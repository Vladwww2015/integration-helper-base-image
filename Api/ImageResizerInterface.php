<?php
namespace IntegrationHelper\BaseImage\Api;

interface ImageResizerInterface extends ImageProcessInterface
{
    /**
     * @param string $image
     * @return string
     */
    public function getResizedImage(string $image): string;

    /**
     * @param string $image
     * @return string
     */
    public function getResizedUrlImage(string $image): string;

    /**
     * @return string
     */
    public function getName(): string;
}
