<?php
namespace IntegrationHelper\BaseImage\Model\ImageProcessors\ImageCleaner;

use IntegrationHelper\BaseImage\Model\AbstractImage;
use IntegrationHelper\BaseImage\Api\ImageCleanerInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorArgInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorStrategyInterface;


class ImageCleaner extends AbstractImage implements ImageCleanerInterface, ImageProcessorStrategyInterface
{
    /**
     * @return ImageCleanerInterface
     */
    public function executeAll(): ImageProcessInterface
    {
        [$width, $height, $imagePath, $imageResizedPath] = $this->getImageData();

        if(!$this->fileDriver->isWritable($imageResizedPath)) {
            $message = sprintf('Process Resize All Images fails. Destination Dir is not writable: %s', $imageResizedPath);
            $this->log($message);

            throw new \Exception($message);
        }

        foreach ($this->fileDriver->readDirectory($imagePath) as $image) {
            try {
                $this->fileDriver->deleteFile($image);
            } catch (\Throwable $e) {
                $this->log($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->imageConfig->getUniqueTypeId();
    }

    /**
     * @param ImageProcessorArgInterface $arg
     * @return ImageProcessorStrategyInterface
     */
    public function processByStrategy(ImageProcessorArgInterface $arg): ImageProcessorStrategyInterface
    {
        $args = $arg->getArgs();

        foreach ($args as $strategy) {
            if(is_object($strategy) && method_exists($strategy, 'execute')) {
                $strategy->execute($this, $this->imageConfig);
            }
        }

        return $this;
    }

    /**
     * @param string $image
     * @return ImageProcessInterface
     */
    public function execute(string $image): ImageProcessInterface
    {
        return $this;
    }
}
