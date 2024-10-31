<?php
namespace IntegrationHelper\BaseImage\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Resize extends AbstractCommand
{
    public const COMMAND = 'integration-helper:image:resize';

    public const PARAM_NAME = 'name';

    protected function configure(): void
    {
        $this->setName(self::COMMAND)
            ->setDescription('Resize Images');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->imageProcessorManager->runProcessByName(\IntegrationHelper\BaseImage\Api\ConstraintsInterface::PROCESS_RESIZER);
        } catch (NoSuchEntityException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

}
