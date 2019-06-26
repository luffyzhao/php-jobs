<?php


namespace Job\Contracts;


use Interop\Queue\Consumer;
use Interop\Queue\Message;

interface Job
{
    /**
     * @return mixed
     * @author luffyzhao@vip.126.com
     */
    public function getId();

    /**
     * @return mixed
     * @author luffyzhao@vip.126.com
     */
    public function getData(): Behavior;

    /**
     * @return Message
     * @author luffyzhao@vip.126.com
     */
    public function getMessage(): Message;

    /**
     * @return Consumer
     * @author luffyzhao@vip.126.com
     */
    public function getConsumer(): Consumer;

    /**
     * @param Consumer $consumer
     * @return Job
     * @author luffyzhao@vip.126.com
     */
    public function setConsumer(Consumer $consumer): Job;
}