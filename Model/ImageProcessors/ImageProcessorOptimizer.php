<?php

namespace IntegrationHelper\BaseImage\Model\ImageProcessors;

use IntegrationHelper\BaseImage\Api\ImageProcessorArgInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorResultInterface;

class ImageProcessorOptimizer extends ImageProcessor
{
    public function process(): ImageProcessorInterface
    {
        // Use ideas from Amasty Image Optimizer
        // TODO: Implement process() method.

        return $this;
    }

    public function processAndGetResult(ImageProcessorArgInterface $arg): ImageProcessorResultInterface
    {
        $name = $arg['name'] ?? false;
        //TODO optimize by criteria
        return $this->imageProcessorResult;
    }
}
