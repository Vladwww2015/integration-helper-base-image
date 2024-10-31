<?php

namespace IntegrationHelper\BaseImage\Model\ImageProcessors;

use IntegrationHelper\BaseImage\Api\ImageProcessorArgInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorResultInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorStrategyInterface;

class ImageProcessorCleaner extends ImageProcessor
{
    /**
     * @param ImageProcessorArgInterface $arg
     * @return ImageProcessorResultInterface
     * @throws \Exception
     */
    public function processAndGetResult(ImageProcessorArgInterface $arg): ImageProcessorResultInterface
    {
        $args = $arg->getArgs();
        $name = $args['name'];

        $imageCleaner = $this->imageProcessPool->getProcess($name);

        if($imageCleaner instanceof ImageProcessorStrategyInterface) {
            return $this->imageProcessorResult->setData($imageCleaner->processByStrategy($arg));
        }

        return $this->imageProcessorResult->setData(false);
    }
}
