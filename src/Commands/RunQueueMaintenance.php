<?php

  namespace ActiveCollab\JobQueue\Commands;

  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;
  use Exception;
  use DirectoryIterator;

  /**
   * @package ActiveCollab\JobQueue\Commands
   */
  class RunQueueMaintenance extends Command
  {
    /**
     * Configure command
     */
    protected function configure()
    {
      parent::configure();

      $this->setName('run_queue_maintenance')->setDescription('Execute queue maintenance tasks');
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
      try {
        $queue = $this->getDispatcher($input)->getQueue();

        $queue->checkStuckJobs();
        $queue->cleanUp();

        return $this->success('Queue maintenance done', $input, $output);
      } catch (Exception $e) {
        return $this->abortDueToException($e, $input, $output);
      }
    }
  }