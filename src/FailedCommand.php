<?php


namespace Job;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FailedCommand extends Command
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

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        if (count($jobs = $this->getFailedJobs()) === 0) {
            return $output->writeln('No failed jobs!');
        }

        $this->displayFailedJobs($jobs);
    }

    /**
     * Compile the failed jobs into a displayable format.
     *
     * @return array
     */
    protected function getFailedJobs()
    {
        $failed = $this->container['queue.failer']->all();

        return collect($failed)->map(function ($failed) {
            return $this->parseFailedJob((array) $failed);
        })->filter()->all();
    }

    /**
     * Parse the failed job row.
     *
     * @param  array  $failed
     * @return array
     */
    protected function parseFailedJob(array $failed)
    {
        $row = array_values(Arr::except($failed, ['payload', 'exception']));

        array_splice($row, 3, 0, $this->extractJobName($failed['payload']));

        return $row;
    }

    /**
     * Extract the failed job name from payload.
     *
     * @param  string  $payload
     * @return string|null
     */
    private function extractJobName($payload)
    {
        $payload = json_decode($payload, true);

        if ($payload && (! isset($payload['data']['command']))) {
            return $payload['job'] ?? null;
        } elseif ($payload && isset($payload['data']['command'])) {
            return $this->matchJobName($payload);
        }
    }

    /**
     * Match the job name from the payload.
     *
     * @param  array  $payload
     * @return string
     */
    protected function matchJobName($payload)
    {
        preg_match('/"([^"]+)"/', $payload['data']['command'], $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        return $payload['job'] ?? null;
    }
    /**
     * @author luffyzhao@vip.126.com
     */
    protected function configure()
    {
        $this->setName('queue:failed')
            ->setDescription('查看失败队列');
    }

    /**
     * Display the failed jobs in the console.
     *
     * @param array $rows
     * @return void
     */
    protected function displayFailedJobs(array $rows)
    {
        $table = new Table($this->output);
        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }
        $table->setHeaders(['ID', 'Connection', 'Queue', 'Class', 'Failed At'])->setRows($rows)->setStyle('default');

        $table->render();
    }
}