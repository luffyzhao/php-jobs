<?php


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TestJob implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    public $tries = 5;

    public function handle()
    {
        throw new InvalidArgumentException('测试');
    }
}