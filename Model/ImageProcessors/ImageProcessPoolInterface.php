<?php

namespace IntegrationHelper\BaseImage\Model\ImageProcessors;

use IntegrationHelper\BaseImage\Api\ImageProcessInterface;

interface ImageProcessPoolInterface
{
    /**
     * @return ImageProcessInterface[]
     */
    public function getProcesses(): array;

    /**
     * @param string $name
     * @return ImageProcessInterface
     */
    public function getProcess(string $name): ImageProcessInterface;
}
