<?php

namespace GearDev\Processes\ProcessesManagement;

use GearDev\Core\ContextStorage\ContextStorage;
use GearDev\Coroutines\Co\CoFactory;

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
        try {
            $result = $this->run();
            if ($result === true) {
                return;
            } else {
                throw new \Exception('Init process '.get_called_class().' did not return true');
            }
        } catch (\Throwable $e) {
            echo json_encode(['msg'=>'INIT PROCESS ' . $this->getName() . ' stopped with message: ' . $e->getMessage(), 'throwable' => $e]).PHP_EOL;
            ContextStorage::getSystemChannel('exitChannel')->push(1);
        }
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
                            echo json_encode(['msg'=>'CRITICAL, Process ' . $this->getName() . ' failed with message: ' . $e->getMessage().'. Will sleep 5 sec and try to restart', 'throwable' => $e]).PHP_EOL;
                            sleep(5);
                        } else {
                            echo json_encode(['msg'=>'Process ' . $this->getName() . ' stopped with message: ' . $e->getMessage(), 'throwable' => $e]).PHP_EOL;
                            ContextStorage::getSystemChannel('exitChannel')->push(1);
                        }
                    }
                }
            })
            ->args()
            ->run();
    }

    abstract protected function run(): bool;
}
