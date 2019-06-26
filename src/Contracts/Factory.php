<?php


namespace Job\Contracts;


use Interop\Queue\Message;

interface Factory
{
    /**
     * @param $command
     * @return mixed
     * @author luffyzhao@vip.126.com
     */
    public function push(Behavior $command);

    /**
     * @param $queue
     * @return Message|null
     * @author luffyzhao@vip.126.com
     */
    public function pop($queue = null):?Job;

    /**
     * 列队处理成功
     * @param Job $job
     * @return mixed
     * @author luffyzhao@vip.126.com
     */
    public function acknowledge(Job $job);

    /**
     * 列队处理不成功
     * @param Job $job
     * @param bool $requeue
     * @return mixed
     * @author luffyzhao@vip.126.com
     */
    public function reject(Job $job, bool $requeue = false);
}