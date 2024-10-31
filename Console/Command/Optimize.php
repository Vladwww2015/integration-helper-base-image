<?php
namespace IntegrationHelper\BaseImage\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Optimize extends AbstractCommand
{
    public const COMMAND = 'integration-helper:image:optimize';

    public const PARAM_NAME = 'name';

    protected function configure(): void
    {
        $this->setName(self::COMMAND)
            ->setDescription('Optimize Images')
            ->addOption(
                self::PARAM_NAME,
                'n',
                InputOption::VALUE_REQUIRED,
                'Name Optimize Process'
            );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->imageProcessorManager->runProcessByNameAndGetResult(
                \IntegrationHelper\BaseImage\Api\ConstraintsInterface::PROCESS_OPTIMIZER,
                $this->imageProcessorArg->setArgs(['name' => $input->getOption(self::PARAM_NAME)])
            );
        } catch (NoSuchEntityException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

}
