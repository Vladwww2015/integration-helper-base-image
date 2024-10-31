<?php
namespace IntegrationHelper\BaseImage\Model;

use IntegrationHelper\BaseImage\Api\ImageConfigInterface;
use IntegrationHelper\BaseLogger\Logger\Logger;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractImage
{
    public function __construct(
        protected Filesystem $filesystem,
        protected FileDriver $fileDriver,
        protected StoreManagerInterface $storeManager,
        protected ImageConfigInterface $imageConfig,
        protected AdapterFactory $imageFactory,
        protected string $loggerType,
    ){}

    public function getMediaPath()
    {
        return $this->imageConfig->getImagePath();
    }

    public function getLoggerType(): string
    {
        return $this->loggerType;
    }

    public function log(string $message)
    {
        Logger::log($message, $this->loggerType);
    }

    /*public function deleteImage(string $imageName)
    {
        if($imageName) {
            $pathToMediaDir = $this->filesystem->getDirectoryReadByPath(DirectoryList::MEDIA)->getAbsolutePath();
            $imagePath = $pathToMediaDir . $this->getMediaPath() . DIRECTORY_SEPARATOR . $imageName;

            try {
                if($this->fileDriver->isExists($imagePath)) {
                    $this->fileDriver->deleteFile($imagePath);
                }
            } catch (\Exception $e) {
                $this->log($e->getMessage());
            }
        }

        return $this;
    }*/

    /**
     * @param string $width
     * @param string $height
     * @return array
     */
    protected function getImageData(): array
    {
        $widthData = $this->imageConfig->getWidth();
        $heightData = $this->imageConfig->getHeight();
        $imagePath = $this->imageConfig->getImagePath();
        $imageDestinationPath = $this->imageConfig->getImageDestinationPath() ?? false;
        if($imageDestinationPath) {
            $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            $imageDestinationPath =  $mediaPath . ltrim($imageDestinationPath, DIRECTORY_SEPARATOR);
        }

        return [$widthData, $heightData, $imagePath, $imageDestinationPath];
    }
}
