<?php


namespace Job;


class Container extends \Illuminate\Container\Container
{
    public function isDownForMaintenance(){
        return false;
    }
}