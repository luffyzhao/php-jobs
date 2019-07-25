<?php


namespace Job;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    public $tries = 5;

    public function __construct()
    {

    }

    public function handle()
    {
        throw new \Exception('1');
    }
}