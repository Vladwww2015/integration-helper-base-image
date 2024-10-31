<?php

namespace IntegrationHelper\BaseImage\Model\ImageProcessors;

use IntegrationHelper\BaseImage\Api\ImageProcessorArgInterface;

class ImageProcessorArg implements ImageProcessorArgInterface
{
    /**
     * @var
     */
    protected $args;

    /**
     * @param mixed $args
     * @return ImageProcessorArgInterface
     */
    public function setArgs(mixed $args): ImageProcessorArgInterface
    {
        $this->args = $args;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getArgs(): mixed
    {
        return $this->args;
    }
}
