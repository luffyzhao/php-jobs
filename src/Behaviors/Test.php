<?php


namespace Job\Behaviors;


use Job\Bus\Behavior as BehaviorAlias;
use Job\Contracts\Behavior;
use Exception;

class Test implements Behavior
{
    use BehaviorAlias;

    protected $timeSot = [0, 15000, 30000, 30000, 30000, 30000];

    /**
     * @return mixed
     * @throws Exception
     * @author luffyzhao@vip.126.com
     */
    public function handle()
    {
        throw new Exception('测试');
    }

    /**
     * @return int|null
     * @author luffyzhao@vip.126.com
     */
    public function getDelay(): ?int
    {
        return $this->timeSot[$this->getRunningCount()] ?? null;
    }

    /**
     * @return int
     * @author luffyzhao@vip.126.com
     */
    public function getTries(): int
    {
        return count($this->timeSot);
    }

    /**
     * @param null $e
     * @return mixed|void
     * @author luffyzhao@vip.126.com
     */
    public function fail($e = null)
    {

    }
}