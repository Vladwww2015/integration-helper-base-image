<?php

namespace IntegrationHelper\BaseImage\Api;

interface ImageProcessInterface
{
    /**
     * @return ImageSaverInterface
     */
    public function execute(string $image): ImageProcessInterface;

    public function executeAll(): ImageProcessInterface;

    /**
     * @return string
     */
    public function getName(): string;
}
