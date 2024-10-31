<?php

namespace IntegrationHelper\BaseImage\Console\Command;

use IntegrationHelper\BaseImage\Api\ImageProcessorArgInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorManagerInterface;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @param ImageProcessorArgInterface $imageProcessorArg
     * @param ImageProcessorManagerInterface $imageProcessorManager
     * @param string|null $name
     */
    public function __construct(
        protected ImageProcessorArgInterface $imageProcessorArg,
        protected ImageProcessorManagerInterface $imageProcessorManager,
        string $name = null
    ){
        parent::__construct($name);
    }
}
