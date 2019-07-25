<?php


namespace Job;


use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WorkCommand extends Command
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

        $this->listenForEvents();

        $connection = $input->getArgument('connection')
            ?: $this->container['config']['queue.default'];

        $queue = $input->getOption('queue') ?: $this->container['config']->get(
            "queue.connections.{$connection}.queue", 'default'
        );

        $this->container['queue.worker']->{$input->getOption('once') ? 'runNextJob' : 'daemon'}(
            $connection, $queue, $this->gatherWorkerOptions()
        );
    }

    /**
     * Listen for the queue events in order to update the console output.
     *
     * @return void
     */
    protected function listenForEvents()
    {
        $this->container['events']->listen(JobProcessing::class, function ($event) {
            $this->writeOutput($event->job, 'starting');
        });

        $this->container['events']->listen(JobProcessed::class, function ($event) {
            $this->writeOutput($event->job, 'success');
        });

        $this->container['events']->listen(JobFailed::class, function ($event) {
            $this->writeOutput($event->job, 'failed');

            $this->logFailedJob($event);
        });
    }

    /**
     * Store a failed job event.
     *
     * @param JobFailed $event
     * @return void
     */
    protected function logFailedJob(JobFailed $event)
    {
        $this->container['queue.failer']->log(
            $event->connectionName, $event->job->getQueue(),
            $event->job->getRawBody(), $event->exception
        );
    }

    /**
     * Write the status output for the queue worker.
     *
     * @param Job $job
     * @param string $status
     * @return void
     */
    protected function writeOutput(Job $job, $status)
    {
        switch ($status) {
            case 'starting':
                $this->writeStatus($job, 'Processing', 'comment');
            case 'success':
                $this->writeStatus($job, 'Processed', 'info');
            case 'failed':
                $this->writeStatus($job, 'Failed', 'error');
        }
    }

    /**
     * Format the status output for the queue worker.
     *
     * @param Job $job
     * @param string $status
     * @param string $type
     * @return void
     */
    protected function writeStatus(Job $job, $status, $type)
    {
        $this->output->writeln(sprintf(
            "<{$type}>[%s][%s] %s</{$type}> %s",
            Carbon::now()->format('Y-m-d H:i:s'),
            $job->getJobId(),
            str_pad("{$status}:", 11), $job->resolveName()
        ));
    }

    /**
     * @return WorkerOptions
     * @author luffyzhao@vip.126.com
     */
    protected function gatherWorkerOptions()
    {
        return new WorkerOptions(
            $this->input->getOption('delay'), $this->input->getOption('memory'),
            $this->input->getOption('timeout'), $this->input->getOption('sleep'),
            $this->input->getOption('tries'), $this->input->getOption('force'),
            $this->input->getOption('stop-when-empty')
        );
    }

    /**
     * @author luffyzhao@vip.126.com
     */
    protected function configure()
    {
        $this
            ->setName('queue:work')
            ->setDescription('队列消费')
            ->addArgument('connection', InputArgument::OPTIONAL, '选择连接类型', '')
            ->addOption('queue', null, InputOption::VALUE_REQUIRED, '选择队列')
            ->addOption('daemon', null, InputOption::VALUE_NONE, '是否守护进程')
            ->addOption('once', null, InputOption::VALUE_NONE, '是否只执行一次')
            ->addOption('stop-when-empty', null, InputOption::VALUE_NONE, '队列消费完就停止')
            ->addOption('delay', null, InputOption::VALUE_REQUIRED, '延迟失败作业的秒数', 0)
            ->addOption('force', null, InputOption::VALUE_NONE, '强制工人在维护模式下运行')
            ->addOption('memory', null, InputOption::VALUE_REQUIRED, '内存限制（兆字节）', 128)
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, '无作业可用时休眠的秒数', 3)
            ->addOption('timeout', null, InputOption::VALUE_REQUIRED, '子进程可以运行的秒数', 60)
            ->addOption('tries', null, InputOption::VALUE_REQUIRED, '在记录作业失败之前尝试该作业的次数', 0);
    }

}