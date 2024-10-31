<?php

namespace IntegrationHelper\BaseImage\Model\ImageProcessors;

use IntegrationHelper\BaseImage\Api\ImageProcessInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorArgInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorResultInterface;
use Magento\Framework\Exception\LocalizedException;

class ImageProcessor implements ImageProcessorInterface
{
    /**
     * @param string $processName
     * @param string $sortOrder
     */
    public function __construct(
        protected ImageProcessorResultInterface $imageProcessorResult,
        protected ImageProcessPoolInterface $imageProcessPool,
        protected string $processName,
        protected string $sortOrder = '10'
    ) {}


    public function process(): ImageProcessorInterface
    {
        foreach ($this->imageProcessPool->getProcesses() as $imageResizer) {
            $imageResizer->executeAll();
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return (int) $this->sortOrder;
    }

    /**
     * @return string
     */
    public function getProcessName(): string
    {
        return $this->processName;
    }

    public function getProcess(string $processName): ImageProcessInterface
    {
        return $this->imageProcessPool->getProcess($processName);
    }

    /**
     * @param ImageProcessorArgInterface $arg
     * @return ImageProcessorResultInterface
     * @throws LocalizedException
     */
    public function processAndGetResult(ImageProcessorArgInterface $arg): ImageProcessorResultInterface
    {
        $this->validate($arg);
        $args = $arg->getArgs();
        $name = $args['name'];
        $image = $args['image'];

        return $this->imageProcessorResult->setData($this->imageProcessPool->getProcess($name)->execute($image));
    }

    protected function validate(ImageProcessorArgInterface $arg)
    {
        $args = $arg->getArgs();
        if (!is_array($args)) {
            throw new LocalizedException(__('Args can\'t be empty for Image Processor Saver in the method processAndGetResult'));
        }

        $name = $args['name'] ?? false;
        if (!$name) {
            throw new LocalizedException(__('Args `name` can\'t be empty for Image Processor Saver in the method processAndGetResult'));
        }

        $image = $args['image'] ?? false;
        if (!$image) {
            throw new LocalizedException(__('Args `image` can\'t be empty for Image Processor Saver in the method processAndGetResult'));
        }
    }
}

