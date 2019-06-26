<?php


namespace Job\Contracts;


interface Behavior
{
    /**
     * 获取对队列名称
     * @return string|null
     * @author luffyzhao@vip.126.com
     */
    public function getQueue(): ?string;

    /**
     * 获取延迟时间
     * @return int|null
     * @author luffyzhao@vip.126.com
     */
    public function getDelay(): ?int;

    /**
     * 程序运行时长
     * @return int|null
     * @author luffyzhao@vip.126.com
     */
    public function getTimeout(): ?int;

    /**
     * @param null $e
     * @return mixed
     * @author luffyzhao@vip.126.com
     */
    public function fail($e = null);

    /**
     * 最大失败次数
     * @return int
     * @author luffyzhao@vip.126.com
     */
    public function getTries(): int;

    /**
     * 当前运行次数
     * @return int
     * @author luffyzhao@vip.126.com
     */
    public function getRunningCount(): int;

    /**
     * 运行次数加1
     * @return Behavior
     * @author luffyzhao@vip.126.com
     */
    public function addRunningCount(): Behavior;

    /**
     * @return mixed
     * @author luffyzhao@vip.126.com
     */
    public function handle();
}