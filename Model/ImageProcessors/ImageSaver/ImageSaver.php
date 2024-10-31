<?php
namespace IntegrationHelper\BaseImage\Model\ImageProcessors\ImageSaver;

use IntegrationHelper\BaseImage\Model\AbstractImage;
use IntegrationHelper\BaseImage\Api\ImageSaverInterface;
use IntegrationHelper\BaseImage\Api\ImageConfigInterface;

use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as FileDriver;

/**
 *
 */
class ImageSaver extends AbstractImage implements ImageSaverInterface
{
    /**
     * @var array
     */
    protected $_saveImages = [];

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
     * @return ImageSaverInterface
     */
    public function executeAll(): ImageSaverInterface
    {
        [$imagePath, $imageSavedPath] = $this->getImageData();

        if(!$this->fileDriver->isDirectory($imagePath)) {
            $message = sprintf('Process Save All Images fails. Dir not isset: %s', $imagePath);
            $this->log($message);

            throw new \Exception($message);
        }

        if(!$this->fileDriver->isReadable($imagePath)) {
            $message = sprintf('Process Save All Images fails. Dir is not readable: %s', $imagePath);
            $this->log($message);

            throw new \Exception($message);
        }

        if(!$this->fileDriver->isWritable($imageSavedPath)) {
            $message = sprintf('Process Save All Images fails. Destination Dir is not writable: %s', $imageSavedPath);
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

    protected function getImageData(): array
    {
        $imagePath = $this->imageConfig->getImagePath();
        $imageResizedPath = $this->imageConfig->getImageDestinationPath() ?? false;

        return [$imagePath, $imageResizedPath];
    }

    /**
     * @param string $image
     * @param string|null $width
     * @param string|null $height
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(string $image): ImageSaverInterface
    {
        if($this->isSkip($image)) {
            return $this;
        }

        $typeId = $this->imageConfig->getUniqueTypeId();

        [$imagePath, $imageSavedPath] = $this->getImageData();

        $imageHash = $this->getHashImage($typeId, $image);
        $savePath = trim($imageSavedPath, DIRECTORY_SEPARATOR);
        $imageSaved = $this->getFile($image, $savePath);

        try {
            if($this->fileDriver->isExists($imageSaved) && $this->fileDriver->isFile($imageSaved)) {
                $saveUrl = $this->getUrlImage(sprintf('%s%s', $savePath, $image));
                $this->_saveImages[$imageHash] = $saveUrl;

                return $this;
            }
        } catch (\Exception $e) {
            $this->log($e->getMessage());
        }

        $imagePath = $this->getFile($image, trim($imagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

        if(!$this->fileDriver->isExists($imagePath) || !$this->fileDriver->isFile($imagePath)) {
            $this->log(sprintf('Image with type id: %s and path %s wasn\'t found', $typeId, $imagePath));
        }

        $result = $this->processSave($imagePath, $imageSaved);

        if($result) $this->_saveImages[$imageHash] = $this->getUrlImage(sprintf('%s/%s', $savePath, $image));

        return $this;
    }

    /**
     * @param string $image
     * @return bool
     */
    protected function isSkip(string $image)
    {
        foreach ($this->skipImageTypes as $type) {
            $type = sprintf('.%s', trim($type, '.'));
            if(substr($image, -strlen($type), strlen($type))) {
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
    protected function getUrlImage(string $image)
    {
        $path = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return sprintf('%s%s', $path, $image);
    }

    /**
     * @param string $imagePath
     * @param string $imageSavedPath
     * @param string $width
     * @param string $height
     * @return $this|false
     */
    protected function processSave(string $imagePath, string $imageSavedPath)
    {
        try {
            $imageSave = $this->imageFactory->create();
            $imageSave->open($imagePath);
            $imageSave->constrainOnly($this->imageConfig->getConstraintOnly());
            $imageSave->keepTransparency($this->imageConfig->getKeepTransparency());
            $imageSave->quality($this->imageConfig->getQuality());
            $imageSave->keepFrame($this->imageConfig->getKeepFrame());
            $imageSave->keepAspectRatio($this->imageConfig->getKeepAspectRatio());
            $imageSave->save($imageSavedPath);
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
}
