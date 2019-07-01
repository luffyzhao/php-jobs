<?php


namespace Job;

use Job\Contracts\Behavior;
use Job\Contracts\Factory;
use Job\Factory\PheanstalkFactory;

class Dispatch
{
    /**
     * @var Factory
     * @author luffyzhao@vip.126.com
     */
    private $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param Behavior $behavior
     * @author luffyzhao@vip.126.com
     */
    public function now(Behavior $behavior)
    {
        $this->factory->push($behavior);
    }
}