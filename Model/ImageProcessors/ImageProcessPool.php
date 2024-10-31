<?php

namespace IntegrationHelper\BaseImage\Model\ImageProcessors;

use IntegrationHelper\BaseImage\Api\ImageProcessInterface;

class ImageProcessPool implements ImageProcessPoolInterface
{
    /**
     * @var array
     */
    protected $_processes = [];

    /**
     * @param array $imageSavers
     */
    public function __construct(
        array $processes = []
    ) {
        $this->_processes = array_filter($processes, fn($process) => $process instanceof ImageProcessInterface);
    }

    /**
     * @return ImageProcessInterface[]
     */
    public function getProcesses(): array
    {
        return $this->_processes;
    }

    /**
     * @param string $name
     * @return ImageProcessInterface
     * @throws \Exception
     */
    public function getProcess(string $name): ImageProcessInterface
    {
        foreach ($this->getProcesses() as $process) {
            if($process->getName() === $name) {
                return $process;
            }
        }

        throw new \Exception(sprintf('Image Process with name %s not found', $name));
    }
}
