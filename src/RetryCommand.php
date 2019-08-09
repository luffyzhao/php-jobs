<?php


namespace Job;


use Illuminate\Support\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RetryCommand extends Command
{
    /**
     * @var Container
     * @author luffyzhao@vip.126.com
     */
    private $container;

    /**
     * @var OutputInterface
     * @author luffyzhao@vip.126.com
     */
    protected $output = null;

    /**
     * @var InputInterface
     * @author luffyzhao@vip.126.com
     */
    protected $input = null;

    public function __construct(Container $container, $name = null)
    {
        parent::__construct($name);

        $this->container = $container;
    }

    /**
     * 执行
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @author luffyzhao@vip.126.com
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        foreach ($this->getJobIds() as $id) {
            $job = $this->container['queue.failer']->find($id);

            if (is_null($job)) {
                $output->writeln("Unable to find failed job with ID [{$id}].");
            } else {
                $this->retryJob($job);

                $output->writeln("The failed job [{$id}] has been pushed back onto the queue!");

                $this->container['queue.failer']->forget($id);
            }
        }
    }

    /**
     * Get the job IDs to be retried.
     *
     * @return array
     */
    protected function getJobIds()
    {
        $ids = (array) $this->input->getArgument('id');

        if (count($ids) === 1 && $ids[0] === 'all') {
            $ids = Arr::pluck($this->container['queue.failer']->all(), 'id');
        }

        return $ids;
    }

    /**
     * Retry the queue job.
     *
     * @param  \stdClass  $job
     * @return void
     */
    protected function retryJob($job)
    {
        $this->container['queue']->connection($job->connection)->pushRaw(
            $this->resetAttempts($job->payload), $job->queue
        );
    }

    /**
     * Reset the payload attempts.
     *
     * Applicable to Redis jobs which store attempts in their payload.
     *
     * @param  string  $payload
     * @return string
     */
    protected function resetAttempts($payload)
    {
        $payload = json_decode($payload, true);

        if (isset($payload['attempts'])) {
            $payload['attempts'] = 0;
        }

        return json_encode($payload);
    }
    /**
     * @author luffyzhao@vip.126.com
     */
    protected function configure()
    {
        $this->setName('queue:retry')
            ->setDescription('重发失败队列')
            ->addArgument('id', InputArgument::IS_ARRAY, '重发ID', ['all']);
    }
}