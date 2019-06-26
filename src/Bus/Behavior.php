<?php


namespace Job\Bus;


use Job\Contracts\Behavior as BehaviorAlias;

trait Behavior
{
    /**
     * @var string
     * @author luffyzhao@vip.126.com
     */
    public $queue;

    /**
     * @var int
     * @author luffyzhao@vip.126.com
     */
    public $delay;

    /**
     * @var int
     * @author luffyzhao@vip.126.com
     */
    public $timeout = 30;

    /**
     * 最大失败次数
     * @var int
     * @author luffyzhao@vip.126.com
     */
    public $tries = 1;

    /**
     * 当前运行次数
     * @var int
     * @author luffyzhao@vip.126.com
     */
    public $running_count = 0;

    /**
     * 设置对队列名称
     * @return string
     * @author luffyzhao@vip.126.com
     */
    public function getQueue(): ?string
    {
        return $this->queue;
    }

    /**
     * 设置延迟时间
     * @return int
     * @author luffyzhao@vip.126.com
     */
    public function getDelay(): ?int
    {
        return $this->delay;
    }

    /**
     * 程序运行时长
     * @return int
     * @author luffyzhao@vip.126.com
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    /**
     * 最大失败次数
     * @return int
     * @author luffyzhao@vip.126.com
     */
    public function getTries(): int
    {
        return $this->tries;
    }

    /**
     * 当前运行次数
     * @return int
     * @author luffyzhao@vip.126.com
     */
    public function getRunningCount(): int
    {
        return $this->running_count;
    }

    /**
     * @return BehaviorAlias
     * @author luffyzhao@vip.126.com
     */
    public function addRunningCount(): BehaviorAlias
    {
        $this->running_count++;
        return $this;
    }

    /**
     * @param null $e
     * @return mixed
     * @author luffyzhao@vip.126.com
     */
    public function fail($e = null)
    {

    }
}