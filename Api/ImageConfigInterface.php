<?php
namespace IntegrationHelper\BaseImage\Api;

interface ImageConfigInterface
{
    /**
     * @return string
     */
    public function getImagePath(): string;

    /**
     * @return string
     */
    public function getUniqueTypeId(): string;

    /**
     * @return string
     */
    public function getImageDestinationPath(): string;

    /**
     * @return string
     */
    public function getWidth(): string;

    /**
     * @return string
     */
    public function getHeight(): string;

    /**
     * @return int
     */
    public function getQuality(): int;

    /**
     * @return bool
     */
    public function getConstraintOnly(): bool;

    /**
     * @return bool
     */
    public function getKeepTransparency(): bool;

    /**
     * @return bool
     */
    public function getKeepFrame(): bool;

    /**
     * @return bool
     */
    public function getKeepAspectRatio(): bool;
}
