<?php


namespace Job;


use Interop\Queue\Consumer;
use Interop\Queue\Message;
use Job\Contracts\Behavior;
use Job\Contracts\Job as JobAlias;

class Response implements JobAlias
{
    /**
     * @var Message
     * @author luffyzhao@vip.126.com
     */
    private $message;

    /**
     * @var Consumer
     * @author luffyzhao@vip.126.com
     */
    private $consumer;

    /**
     * Job constructor.
     * @param Message $message
     * @author luffyzhao@vip.126.com
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     * @author luffyzhao@vip.126.com
     */
    public function getId()
    {
        return $this->message->getMessageId();
    }

    /**
     * @return mixed
     * @author luffyzhao@vip.126.com
     */
    public function getData(): Behavior
    {
        return unserialize($this->message->getBody());
    }

    /**
     * @return Message
     * @author luffyzhao@vip.126.com
     */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * @return Consumer
     * @author luffyzhao@vip.126.com
     */
    public function getConsumer(): Consumer
    {
        return $this->consumer;
    }

    /**
     * @param Consumer $consumer
     * @return JobAlias
     * @author luffyzhao@vip.126.com
     */
    public function setConsumer(Consumer $consumer): JobAlias
    {
        $this->consumer = $consumer;
        return $this;
    }
}