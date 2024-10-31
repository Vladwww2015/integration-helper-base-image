<?php

namespace IntegrationHelper\BaseImage\Api;

interface ImageProcessorInterface
{
    /**
     * @return mixed
     */
    public function process(): ImageProcessorInterface;

    /**
     * @param string $processName
     * @return ImageProcessorInterface
     */
    public function getProcess(string $processName): ImageProcessInterface;

    /**
     * @param ImageProcessorArgInterface $arg
     * @return ImageProcessorResultInterface
     */
    public function processAndGetResult(ImageProcessorArgInterface $arg): ImageProcessorResultInterface;

    /**
     * @return string
     */
    public function getProcessName(): string;

    /**
     * @return int
     */
    public function getSortOrder(): int;
}
