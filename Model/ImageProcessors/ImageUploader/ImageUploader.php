<?php
namespace IntegrationHelper\BaseImage\Model\ImageProcessors\ImageUploader;


use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as FileDriver;

use IntegrationHelper\BaseImage\Model\AbstractImage;
use IntegrationHelper\BaseImage\Api\ImageConfigInterface;
use IntegrationHelper\BaseImage\Api\ImageUploaderInterface;

/**
 *
 */
class ImageUploader extends AbstractImage implements ImageUploaderInterface
{
    protected array $sources = [];

    /**
     * @var array
     */
    protected array $uploadResultProcess = [];

    /**
     * @param Filesystem $filesystem
     * @param FileDriver $fileDriver
     * @param StoreManagerInterface $storeManager
     * @param ImageConfigInterface $imageConfig
     * @param string $loggerType
     * @param AdapterFactory $imageFactory
     * @param File $file
     * @param array $sources
     */
    public function __construct(
        Filesystem $filesystem,
        FileDriver $fileDriver,
        StoreManagerInterface $storeManager,
        ImageConfigInterface $imageConfig,
        string $loggerType,
        AdapterFactory $imageFactory,
        protected File $file,
        array $sources
    ) {
        parent::__construct($filesystem, $fileDriver, $storeManager, $imageConfig, $imageFactory, $loggerType);
        $this->sources = array_filter($sources, fn($source) => $source instanceof SourceInterface);
    }

    /**
     * @return ImageUploaderInterface
     */
    public function executeAll(): ImageUploaderInterface
    {
        [$widthData, $heightData, $imagePath, $imageDestinationPath] = $this->getImageData();

        if(!$this->fileDriver->isDirectory($imageDestinationPath)) {
            $message = sprintf('Process Upload All Images fails. Destination Dir not isset: %s', $imageDestinationPath);
            $this->log($message);

            throw new \Exception($message);
        }

        if(!$this->fileDriver->isReadable($imageDestinationPath)) {
            $message = sprintf('Process Upload All Images fails. Destination Dir is not readable: %s', $imageDestinationPath);
            $this->log($message);

            throw new \Exception($message);
        }

        /**
         * @var $source SourceInterface
         */
        foreach ($this->sources as $source) {
            foreach ($source->getImages() as $imageData) {
                try {
                    $this->execute($imageData['url']);
                    $source->callbackAfterUpload([...$imageData, ...['result' => $this->getUploadResultProcess()]]);
                } catch (\Throwable $e) {
                    $this->log($e->getMessage());
                }
            }
        }

        return $this;
    }

    /**
     * @param string $imageUrl
     * @param string|null $width
     * @param string|null $height
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(string $imageUrl): ImageUploaderInterface
    {
        [$widthData, $heightData, $imagePath, $imageDestinationPath] = $this->getImageData();

        $this->uploadResultProcess = $this->processUpload($imageUrl, $imageDestinationPath);

        return $this;
    }

    public function getUploadResultProcess(): array
    {
        return $this->uploadResultProcess;
    }

    /**
     * @param string $imageUrl
     * @param string $imageDestinationPath
     * @return array
     */
    protected function processUpload(string $imageUrl, string $imageDestinationPath): array
    {
        try {
            $path = parse_url($imageUrl, PHP_URL_PATH);
            $baseName = basename($path);
            $destinationFile = $imageDestinationPath . '/' . $baseName;

            $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $destinationPath = $mediaDir->getAbsolutePath($imageDestinationPath . '/' . $baseName);
            $fileInfo = pathinfo($destinationPath);
            $fileName = $fileInfo['filename'];
            $extension = $fileInfo['extension'];

            $counter = 1;
            while ($mediaDir->isExist($destinationFile)) {
                $newFileName = $fileName . '_' . $counter;
                $destinationFile = $imageDestinationPath . '/' . $newFileName . '.' . $extension;
                $destinationPath = $mediaDir->getAbsolutePath($destinationFile);
                $counter++;
            }

            $imageData = file_get_contents($imageUrl);
            if ($imageData === false) {
                throw new \Exception('Failed to download the image.');
            }

            $this->file->checkAndCreateFolder($mediaDir->getAbsolutePath($imageDestinationPath));
            $tempFilePath = $mediaDir->getAbsolutePath(rtrim($imageDestinationPath, DIRECTORY_SEPARATOR) . '/temp_' . ltrim($baseName, DIRECTORY_SEPARATOR));
            $this->file->write($tempFilePath, $imageData);

            $imageSave = $this->imageFactory->create();
            $imageSave->open($tempFilePath);
            $imageSave->constrainOnly($this->imageConfig->getConstraintOnly());
            $imageSave->keepTransparency($this->imageConfig->getKeepTransparency());
            $imageSave->quality($this->imageConfig->getQuality());
            $imageSave->keepFrame($this->imageConfig->getKeepFrame());
            $imageSave->keepAspectRatio($this->imageConfig->getKeepAspectRatio());
            $imageSave->save($destinationPath);

            $checkIterations = 0;
            while (true) {
                if ($mediaDir->isExist($destinationPath) && $mediaDir->isFile($destinationPath)) {
                    break;
                }
                if($checkIterations++ > 15) break;
                sleep(0.1);
            }

            $this->file->rm($tempFilePath);

        } catch (\Throwable $e) {
            $this->log($e->getMessage());

            return false;
        }

        return [
            'image_old_url' => $imageUrl,
            'image_full_path' => $destinationPath,
            'image_path' => $imageDestinationPath,
            'image_name' => basename($destinationPath)
        ];
    }

    public function getName(): string
    {
        return $this->imageConfig->getUniqueTypeId();
    }
}
