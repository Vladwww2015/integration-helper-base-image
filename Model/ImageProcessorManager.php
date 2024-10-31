<?php

namespace IntegrationHelper\BaseImage\Model;

use IntegrationHelper\BaseImage\Api\ImageProcessInterface;
use Magento\Framework\Exception\LocalizedException;

use IntegrationHelper\BaseImage\Api\ImageProcessorArgInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorManagerInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorResultInterface;

class ImageProcessorManager implements ImageProcessorManagerInterface
{
    /**
     * @param ImageProcessorPool $imageProcessorPool
     */
    public function __construct(
        protected ImageProcessorPool $imageProcessorPool
    ){}

    /**
     * @param string $processName
     * @return ImageProcessorManagerInterface
     * @throws LocalizedException
     */
    public function runProcessByName(string $processName): ImageProcessorManagerInterface
    {
        $this->getProcessorByName($processName)->process();

        return $this;
    }

    /**
     * @param string $processName
     * @param ImageProcessorArgInterface $imageProcessorArg
     * @return ImageProcessorResultInterface
     * @throws LocalizedException
     */
    public function runProcessByNameAndGetResult(string $processName, ImageProcessorArgInterface $imageProcessorArg): ImageProcessorResultInterface
    {
        return $this->getProcessorByName($processName)->processAndGetResult($imageProcessorArg);
    }

    /**
     * @param string $processorName
     * @param string $processName
     * @return ImageProcessInterface
     * @throws LocalizedException
     */
    public function getProcess(string $processorName, string $processName): ImageProcessInterface
    {
        $processor = $this->getProcessorByName($processorName);

        return $processor->getProcess($processName);
    }

    /**
     * @param string $processName
     * @return ImageProcessorInterface
     * @throws LocalizedException
     */
    public function getProcessorByName(string $processName): ImageProcessorInterface
    {
        foreach ($this->imageProcessorPool->getProcessors() as $processor) {
            if($processor->getProcessName() === $processName) {
                return $processor;
            }
        }

        throw new LocalizedException(__('Image Processor with name %1 doesn\'t exists', $processName));
    }
}
