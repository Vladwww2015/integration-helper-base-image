<?php
namespace IntegrationHelper\BaseImage\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Exception\NoSuchEntityException;

use IntegrationHelper\BaseImage\Api\ImageProcessorArgInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorManagerInterface;

class Clear extends AbstractCommand
{
    public const COMMAND = 'integration-helper:image:clear';

    public const PARAM_NAME = 'name';

    public function __construct(
        ImageProcessorArgInterface $imageProcessorArg,
        ImageProcessorManagerInterface $imageProcessorManager,
        protected array $processStrategies = [],
        string $name = null
    ) {
        if(array_key_exists('name', $processStrategies)) {
            throw new \Exception(sprintf('Please use another key for process strategies, because %s is reservated', 'name'));
        }
        parent::__construct($imageProcessorArg, $imageProcessorManager, $name);
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName(self::COMMAND)
            ->addOption(
                self::PARAM_NAME,
                'n',
                InputOption::VALUE_REQUIRED,
                'Cleaner Name'
            )->setDescription('Run Process Clear Images');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $name = $input->getOption(self::PARAM_NAME) ?: '';

            $args = $this->processStrategies;
            $args['name'] = $name;

            $this->imageProcessorManager->runProcessByNameAndGetResult(
                \IntegrationHelper\BaseImage\Api\ConstraintsInterface::PROCESS_CLEANER,
                $this->imageProcessorArg->setArgs($args)
            );
        } catch (NoSuchEntityException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
