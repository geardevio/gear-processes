<?php

namespace GearDev\Processes\Clutch;

use GearDev\Collector\Collector\Collector;
use GearDev\Core\Attributes\Clutch;
use GearDev\Core\Warmers\ClutchInterface;
use GearDev\Processes\Attributes\InitProcess;
use GearDev\Processes\Attributes\Process;

#[Clutch]
class RunProcesses implements ClutchInterface
{

    public function clutch(): void
    {
        $collector = Collector::getInstance();
        $collector->runAttributeInstructions(InitProcess::class);

        $collector->runAttributeInstructions(Process::class);
    }
}