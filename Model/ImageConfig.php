<?php

namespace IntegrationHelper\BaseImage\Model;

use IntegrationHelper\BaseImage\Api\ImageConfigInterface;

class ImageConfig implements ImageConfigInterface
{

    public function __construct(
        protected string $imageDestinationPath,
        protected string $uniqueTypeId,
        protected string $imagePath = '',
        protected string $width = '',
        protected string $height = '',
        protected string $quality = '90',
        protected bool $constraintOnly = true,
        protected bool $keepTransparency = true,
        protected bool $keepFrame = false,
        protected bool $keepAspectRatio = true
    ) {}

    /**
     * @return string
     */
    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    /**
     * @return string
     */
    public function getImageDestinationPath(): string
    {
        return $this->imageDestinationPath;
    }

    /**
     * @return int
     */
    public function getQuality(): int
    {
        return (int) $this->quality;
    }

    /**
     * @return bool
     */
    public function getConstraintOnly(): bool
    {
        return $this->constraintOnly;
    }

    /**
     * @return bool
     */
    public function getKeepTransparency(): bool
    {
        return $this->keepTransparency;
    }

    /**
     * @return bool
     */
    public function getKeepFrame(): bool
    {
        return $this->keepFrame;
    }

    /**
     * @return bool
     */
    public function getKeepAspectRatio(): bool
    {
        return $this->keepAspectRatio;
    }

    /**
     * @return string
     */
    public function getUniqueTypeId(): string
    {
        return $this->uniqueTypeId;
    }

    /**
     * @return string
     */
    public function getWidth(): string
    {
        return $this->width;
    }

    /**
     * @return string
     */
    public function getHeight(): string
    {
        return $this->height;
    }
}
