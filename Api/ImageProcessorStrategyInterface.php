<?php

namespace IntegrationHelper\BaseImage\Api;

interface ImageProcessorStrategyInterface
{
    /**
     * @param ImageProcessorArgInterface $arg
     * @return ImageProcessorStrategyInterface
     */
    public function processByStrategy(ImageProcessorArgInterface $arg): ImageProcessorStrategyInterface;
}
