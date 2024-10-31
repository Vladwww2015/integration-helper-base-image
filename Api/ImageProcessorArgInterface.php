<?php

namespace IntegrationHelper\BaseImage\Api;

interface ImageProcessorArgInterface
{
    public function setArgs(mixed $args): ImageProcessorArgInterface;

    public function getArgs(): mixed;
}
