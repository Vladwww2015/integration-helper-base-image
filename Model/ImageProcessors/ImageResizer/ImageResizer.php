<?php
namespace IntegrationHelper\BaseImage\Model\ImageProcessors\ImageResizer;

use IntegrationHelper\BaseImage\Api\ImageConfigInterface;
use IntegrationHelper\BaseImage\Api\ImageResizerInterface;
use IntegrationHelper\BaseImage\Model\AbstractImage;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;


class ImageResizer extends AbstractImage implements ImageResizerInterface
{
    /**
     * @var array
     */
    protected $_resizedImages = [];

    /**
     * @param Filesystem $filesystem
     * @param FileDriver $fileDriver
     * @param StoreManagerInterface $storeManager
     * @param ImageConfigInterface $imageConfig
     * @param AdapterFactory $imageFactory
     * @param string $loggerType
     * @param array $skipImageTypes
     */
    public function __construct(
        Filesystem $filesystem,
        FileDriver $fileDriver,
        StoreManagerInterface $storeManager,
        ImageConfigInterface $imageConfig,
        string $loggerType,
        AdapterFactory $imageFactory,
        protected array $skipImageTypes
    ) {
        parent::__construct($filesystem, $fileDriver, $storeManager, $imageConfig, $imageFactory, $loggerType);
        $this->skipImageTypes = array_unique($this->skipImageTypes);
    }

    /**
     * @return ImageResizerInterface
     */
    public function executeAll(): ImageResizerInterface
    {
        [$width, $height, $imagePath, $imageResizedPath] = $this->getImageData();

        if(!$this->fileDriver->isDirectory($imagePath)) {
            $message = sprintf('Process Resize All Images fails. Dir not isset: %s', $imagePath);
            $this->log($message);

            throw new \Exception($message);
        }

        if(!$this->fileDriver->isReadable($imagePath)) {
            $message = sprintf('Process Resize All Images fails. Dir is not readable: %s', $imagePath);
            $this->log($message);

            throw new \Exception($message);
        }

        if(!$this->fileDriver->isWritable($imageResizedPath)) {
            $message = sprintf('Process Resize All Images fails. Destination Dir is not writable: %s', $imageResizedPath);
            $this->log($message);

            throw new \Exception($message);
        }

        foreach ($this->fileDriver->readDirectory($imagePath) as $image) {
            try {
                $this->execute($image);
            } catch (\Throwable $e) {
                $this->log($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * @param string $image
     * @param string|null $width
     * @param string|null $height
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(string $image): ImageResizerInterface
    {
        $DS = DIRECTORY_SEPARATOR;
        $typeId = $this->imageConfig->getUniqueTypeId();

        [$width, $height, $imagePath, $imageResizedPath] = $this->getImageData();

        $imageHash = $this->getHashImage($typeId, $image);
        $resizedPath = sprintf('%s%s%s%s%s%s', trim($imageResizedPath, $DS), $DS, $width, $DS, $height, $DS);
        $imageResized = $this->getFile($image, $resizedPath);

        if($this->isSkip($image)) {
            $this->_resizedImages[$imageHash] = $this->getImagePath($imagePath, $image);

            return $this;
        }

        try {
            if($this->fileDriver->isExists($imageResized) && $this->fileDriver->isFile($imageResized)) {
                $resizedUrl = $this->getImagePath($resizedPath, $image);
                $this->_resizedImages[$imageHash] = $resizedUrl;

                return $this;
            }
        } catch (\Exception $e) {
            $this->log($e->getMessage());
        }

        $imagePath = $this->getFile($image, trim($imagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

        if(!$this->fileDriver->isExists($imagePath) || !$this->fileDriver->isFile($imagePath)) {
            $this->log(sprintf('Image with type id: %s and path %s wasn\'t found', $typeId, $imagePath));
        }

        $result = $this->processResize($imagePath, $imageResized, $width);

        if($result) $this->_resizedImages[$imageHash] = $this
            ->getImagePath($resizedPath, $image);

        return $this;
    }

    /**
     * @return array
     */
    protected function getImageData(): array
    {
        $widthData = $this->imageConfig->getWidth();
        $heightData = $this->imageConfig->getHeight();
        $imagePath = $this->imageConfig->getImagePath();
        $imageDestinationPath = $this->imageConfig->getImageDestinationPath() ?? false;

        return [$widthData, $heightData, $imagePath, $imageDestinationPath];
    }

    /**
     * @param string $image
     * @return bool
     */
    protected function isSkip(string $image)
    {
        foreach ($this->skipImageTypes as $type) {
            $type = sprintf('.%s', trim($type, '.'));
            if(substr($image, -strlen($type), strlen($type)) === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $typeId
     * @param string $image
     * @return string
     */
    protected function getHashImage(string $typeId, string $image)
    {
        return md5(sprintf('%s%s', $typeId, $image));
    }

    /**
     * @param string $image
     * @param string $path
     * @return string
     */
    protected function getFile(string $image, string $path)
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath($path);

        return sprintf('%s/%s', rtrim($path, DIRECTORY_SEPARATOR), $image);
    }

    /**
     * @param string $image
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlImage(string $image): string
    {
        $path = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return sprintf('%s/%s', rtrim($path, '/'), ltrim($image, '/'));
    }

    /**
     * @param string $path
     * @param string $image
     * @return string
     */
    protected function getImagePath(string $path, string $image): string
    {
        return sprintf('%s/%s', rtrim($path, '/'), ltrim($image, '/'));
    }

    /**
     * @param string $imagePath
     * @param string $imageResizedPath
     * @param string $width
     * @return $this|false
     */
    protected function processResize(string $imagePath, string $imageResizedPath, string $width)
    {
        try {
            $imageResize = $this->imageFactory->create();
            $imageResize->open($imagePath);

            $origWidth = $imageResize->getOriginalWidth();
            $origHeight = $imageResize->getOriginalHeight();

            $scale = $width / $origWidth;
            $height = (int) round($origHeight * $scale);

            $imageResize->constrainOnly($this->imageConfig->getConstraintOnly());
            $imageResize->keepTransparency($this->imageConfig->getKeepTransparency());
            $imageResize->quality($this->imageConfig->getQuality());
            $imageResize->keepFrame($this->imageConfig->getKeepFrame());
            $imageResize->keepAspectRatio($this->imageConfig->getKeepAspectRatio());
            $imageResize->resize($width, $height);
            $imageResize->save($imageResizedPath);
        } catch (\Throwable $e) {
            $this->log($e->getMessage());

            return false;
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->imageConfig->getUniqueTypeId();
    }

    /**
     * @param string $image
     * @return string
     */
    public function getResizedImage(string $image): string
    {
        $typeId = $this->imageConfig->getUniqueTypeId();
        $imageHash = $this->getHashImage($typeId, $image);

        return $this->_resizedImages[$imageHash] ?? '';
    }

    /**
     * @param string $image
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getResizedUrlImage(string $image): string
    {
        $typeId = $this->imageConfig->getUniqueTypeId();
        $imageHash = $this->getHashImage($typeId, $image);
        $imageResult = $this->_resizedImages[$imageHash] ?? '';
        if(!$imageResult) $imageResult = $image;

        return $this->getUrlImage($imageResult);
    }
}
