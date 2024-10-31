<?php

namespace IntegrationHelper\BaseImage\Api;

interface ImageProcessorResultInterface
{
    /**
     * @param mixed $data
     * @return ImageProcessorResultInterface
     */
    public function setData(mixed $data): ImageProcessorResultInterface;

    /**
     * @return mixed
     */
    public function getData(): mixed;
}
