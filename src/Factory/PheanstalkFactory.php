<?php


namespace Job\Factory;


use Enqueue\Pheanstalk\PheanstalkConnectionFactory;
use Interop\Queue\Context;
use Interop\Queue\Exception;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\InvalidMessageException;
use Interop\Queue\Message;
use Interop\Queue\Topic;
use Job\Contracts\Behavior;
use Job\Contracts\Factory;
use Job\Contracts\Job;
use Job\Credentials;
use Job\Exceptions\ConnectionException;
use Job\Exceptions\PushException;
use Job\Response;
use LogicException;

class PheanstalkFactory implements Factory
{
    /**
     * @var Context
     * @author luffyzhao@vip.126.com
     */
    private $client;
    /**
     * @var Topic
     * @author luffyzhao@vip.126.com
     */
    private $topic;
    /**
     * @var Credentials
     * @author luffyzhao@vip.126.com
     */
    private $credentials;

    /**
     * PheanstalkFactory constructor.
     * @param Credentials $credentials
     * @throws ConnectionException
     * @author luffyzhao@vip.126.com
     */
    public function __construct(Credentials $credentials)
    {
        $this->onConnection($credentials);
        $this->credentials = $credentials;
    }

    /**
     * @param Credentials $credentials
     * @throws ConnectionException
     * @author luffyzhao@vip.126.com
     */
    private function onConnection(Credentials $credentials)
    {
        try {
            $factory = new PheanstalkConnectionFactory([
                'host' => $credentials->getHost(),
                'port' => $credentials->getPort(),
                'timeout' => $credentials->getConnectionTimeout(),
            ]);

            $this->client = $factory->createContext();
            $this->topic = $this->client->createTopic($credentials->getQueue());

        } catch (LogicException $exception) {
            throw new ConnectionException($exception->getMessage());
        }
    }

    /**
     * @param Behavior $command
     * @return mixed
     * @throws PushException
     * @author luffyzhao@vip.126.com
     */
    public function push(Behavior $command)
    {
        try {
            $message = $this->client->createMessage(serialize($command));
            $producer = $this->client->createProducer();

            if ($command->getDelay() && $command->getDelay() > 0) {
                $message->setDelay($command->getDelay());
            }

            if($command->getTimeout()){
                $message->setTimeToRun($command->getTimeout());
            }

            $topic = $this->topic;
            if ($command->getQueue()) {
                $topic = $this->client->createTopic($command->getQueue());
            }

            $producer->send($topic, $message);
        } catch (Exception | InvalidDestinationException | InvalidMessageException $exception) {
            throw new PushException($exception->getMessage());
        }
    }

    /**
     * @param $queue
     * @return Job|null
     * @author luffyzhao@vip.126.com
     */
    public function pop($queue = null): ?Job
    {
        $consumer = $this->client->createConsumer(
            $this->client->createQueue(
                $queue ? $queue : $this->credentials->getQueue()
            )
        );
        $message = $consumer->receive($this->credentials->getResponseTimeout());

        $response = null;
        if ($message instanceof Message) {
            $response = new Response($message);
            $response->setConsumer($consumer);
        }

        return $response;
    }

    /**
     * @param Job $job
     * @return mixed
     * @author luffyzhao@vip.126.com
     */
    public function acknowledge(Job $job)
    {
        $job->getConsumer()->acknowledge($job->getMessage());
    }

    /**
     * 列队处理不成功
     * @param Job $job
     * @param bool $requeue
     * @return mixed
     * @author luffyzhao@vip.126.com
     */
    public function reject(Job $job, bool $requeue = false)
    {
        $job->getConsumer()->reject($job->getMessage(), $requeue);
    }
}