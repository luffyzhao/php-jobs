<?php


namespace Job;

use Job\Contracts\Behavior;
use Job\Factory\PheanstalkFactory;

class Dispatch
{
    /**
     * @var PheanstalkFactory
     * @author luffyzhao@vip.126.com
     */
    private $pheanstalkFactory;

    public function __construct(PheanstalkFactory $pheanstalkFactory)
    {
        $this->pheanstalkFactory = $pheanstalkFactory;
    }

    /**
     * @param Behavior $behavior
     * @throws Exceptions\PushException
     * @author luffyzhao@vip.126.com
     */
    public function now(Behavior $behavior)
    {
        $this->pheanstalkFactory->push($behavior);
    }
}