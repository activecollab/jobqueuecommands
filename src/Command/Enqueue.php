<?php

namespace ActiveCollab\JobQueue\Command;

use ActiveCollab\JobsQueue\Jobs\JobInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;
use InvalidArgumentException;
use ReflectionClass;

/**
 * @package ActiveCollab\JobQueue\Command
 */
class Enqueue extends Command
{
    /**
     * Configure command
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('enqueue')
            ->addArgument('type', InputArgument::REQUIRED, 'Full job type class name (including namespace)')
            ->addOption('data', 'd', InputOption::VALUE_REQUIRED, 'JSON encoded job data', '{}')
            ->setDescription('Add a new job to the queue');
    }

    /**
     * @param  InputInterface $input
     * @param  OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $type = $this->getType($input);
            $data = $this->getData($input);

            $job_id = $this->dispatcher->dispatch(new $type($data));

            return $this->success("Job #{$job_id} enqueued", $input, $output);
        } catch (Exception $e) {
            return $this->abortDueToException($e, $input, $output);
        }
    }

    /**
     * @param  InputInterface $input
     * @return string
     */
    private function getType(InputInterface $input)
    {
        $type = $input->getArgument('type');

        if (class_exists($type)) {
            $reflection_class = new ReflectionClass($type);

            if ($reflection_class->implementsInterface(JobInterface::class) && !$reflection_class->isAbstract()) {
                return $type;
            } else {
                throw new InvalidArgumentException('Valid job class expected');
            }
        } else {
            throw new InvalidArgumentException("Class '$type' does not exist");
        }
    }

    /**
     * @param  InputInterface $input
     * @return array
     */
    private function getData(InputInterface $input)
    {
        $data = $input->getOption('data');

        if ($data === '') {
            return [];
        } else {
            $result = json_decode($data, true);

            if (json_last_error()) {
                $error_message = 'Failed to parse JSON';

                if (function_exists('json_last_error_msg')) {
                    $error_message .= '. Reason: ' . json_last_error_msg();
                }

                throw new InvalidArgumentException($error_message);
            }

            return $result;
        }
    }
}
