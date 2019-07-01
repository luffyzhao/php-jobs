<?php


namespace Job\Factory;


use Enqueue\Redis\RedisConnectionFactory;
use Interop\Queue\Context;
use Interop\Queue\Exception;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\InvalidMessageException;
use Interop\Queue\Message;
use Interop\Queue\Queue;
use Job\Contracts\Behavior;
use Job\Contracts\Factory;
use Job\Contracts\Job;
use Job\Credentials;
use Job\Exceptions\ConnectionException;
use Job\Exceptions\PushException;
use Job\Response;
use LogicException;

class RedisFactory implements Factory
{

    /**
     * @var Context
     * @author luffyzhao@vip.126.com
     */
    private $client;
    /**
     * @var Queue
     * @author luffyzhao@vip.126.com
     */
    private $queue;
    /**
     * @var Credentials
     * @author luffyzhao@vip.126.com
     */
    private $credentials;

    /**
     * RedisFactory constructor.
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
            $factory = new RedisConnectionFactory([
                'host' => $credentials->getHost(),
                'port' => $credentials->getPort(),
                'scheme_extensions' => ['phpredis'],
            ]);

            $this->client = $factory->createContext();
            $this->queue = $this->client->createQueue($credentials->getQueue());
        } catch (LogicException $exception) {
            throw new ConnectionException($exception->getMessage());
        }
    }

    /**
     * @param $command
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
                $producer->setDeliveryDelay($command->getDelay());
            }

            if($command->getTimeout()){
                $producer->setTimeToLive($command->getTimeout());
            }

            $topic = $this->queue;
            if ($command->getQueue()) {
                $topic = $this->client->createQueue($command->getQueue());
            }

            $producer->send($topic, $message);
        } catch (Exception | InvalidDestinationException | InvalidMessageException $exception) {
            throw new PushException($exception->getMessage());
        }
    }

    /**
     * @param $queue
     * @return Message|null
     * @author luffyzhao@vip.126.com
     */
    public function pop($queue = null): ?Job
    {
        $consumer = $this->client->createConsumer(
            $queue === null ? $this->queue : $this->client->createQueue($queue)
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
     * 列队处理成功
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