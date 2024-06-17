<?php

namespace GearDev\Processes\ProcessesManagement;

use GearDev\Core\ContextStorage\ContextStorage;
use GearDev\Coroutines\Co\CoFactory;
use Illuminate\Support\Facades\Log;
use Swow\Sync\WaitGroup;

abstract class AbstractProcess
{
    protected string $name;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function runInitProcessesInCoroutine(): void
    {
        $group = new WaitGroup();
        $group->add();
        CoFactory::createCo($this->getName())
            ->charge(function (WaitGroup $group) {
                while (true) {
                    try {
                        $result = $this->run();
                        if ($result === true) {
                            $group->done();
                            break;
                        }
                    } catch (\Throwable $e) {
                        if (getenv('GEAR_DEV_SERVER')) {
                            Log::critical('CRITICAL, INIT PROCESS ' . $this->getName() . ' failed with message: ' . $e->getMessage().'. Will sleep 5 sec and try to restart', ['throwable' => $e]);
                            sleep(5);
                        } else {
                            Log::info('INIT PROCESS ' . $this->getName() . ' stopped with message: ' . $e->getMessage(), ['throwable' => $e]);
                            ContextStorage::getSystemChannel('exitChannel')->push(1);
                        }
                    }
                }
            })
            ->args($group)
            ->runWithClonedDiContainer();
        $group->wait();
    }

    public function runProcessInCoroutine(): void
    {
        CoFactory::createCo($this->getName())
            ->charge(function () {
                while (true) {
                    try {
                        $result = $this->run();
                        if ($result === true) {
                            return;
                        }
                    } catch (\Throwable $e) {
                        if (getenv('GEAR_DEV_SERVER')) {
                            Log::critical('CRITICAL, Process ' . $this->getName() . ' failed with message: ' . $e->getMessage().'. Will sleep 5 sec and try to restart', ['throwable' => $e]);
                            sleep(5);
                        } else {
                            Log::info('Process ' . $this->getName() . ' stopped with message: ' . $e->getMessage(), ['throwable' => $e]);
                            ContextStorage::getSystemChannel('exitChannel')->push(1);
                        }
                    }
                }
            })
            ->runWithClonedDiContainer();
    }

    abstract protected function run(): bool;
}
