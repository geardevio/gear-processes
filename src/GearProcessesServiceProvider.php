<?php

namespace GearDev\Processes;

use GearDev\Collector\Collector\Collector;
use Illuminate\Support\ServiceProvider;

class GearProcessesServiceProvider extends ServiceProvider
{
    public function boot() {

    }

    public function register() {
        Collector::addPackageToCollector(__DIR__);
    }
}