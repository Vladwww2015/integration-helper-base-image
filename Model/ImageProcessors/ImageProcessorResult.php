<?php

namespace IntegrationHelper\BaseImage\Model\ImageProcessors;

use IntegrationHelper\BaseImage\Api\ImageProcessorResultInterface;

class ImageProcessorResult implements ImageProcessorResultInterface
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @param mixed $data
     * @return ImageProcessorResultInterface
     */
    public function setData(mixed $data): ImageProcessorResultInterface
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }
}
