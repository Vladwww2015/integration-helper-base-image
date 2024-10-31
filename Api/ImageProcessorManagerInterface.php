<?php

namespace IntegrationHelper\BaseImage\Api;

interface ImageProcessorManagerInterface
{
    public function runProcessByName(string $processName): ImageProcessorManagerInterface;

    public function runProcessByNameAndGetResult(string $processName, ImageProcessorArgInterface $imageProcessorArg): ImageProcessorResultInterface;

    public function getProcessorByName(string $processName): ImageProcessorInterface;
}
