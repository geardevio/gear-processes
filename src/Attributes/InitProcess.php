<?php

namespace GearDev\Processes\Attributes;

use GearDev\Collector\Collector\AttributeInterface;
use GearDev\Processes\ProcessesManagement\AbstractProcess;
use Illuminate\Foundation\Application;

#[\Attribute(\Attribute::TARGET_CLASS)]
class InitProcess implements AttributeInterface
{

    public function __construct(
        public string $processName,
        public bool $serverOnly = true
    )
    {

    }

    private function isAllowedToRun() {
        if ($this->serverOnly && defined('IS_GEAR_SERVER') && IS_GEAR_SERVER) return true;
        if (!$this->serverOnly) return true;
        return false;
    }

    public function onClass(Application $app, string $className, AttributeInterface $attribute): void
    {
        if (!$this->isAllowedToRun()) return;
        $instance = $app->make($className);
        if (!is_a($instance, AbstractProcess::class)) {
            throw new \Exception('Class ' . $className . ' must implement ' . AbstractProcess::class);
        }
        /** @var AttributeInterface $attribute */
        if ($attribute instanceof InitProcess) {
            $instance->setName($attribute->processName);
        }
        $instance->runInitProcessesInCoroutine();
        unset($instance);
    }

    public function onMethod(Application $app, string $className, string $methodName, AttributeInterface $attribute): void
    {
        if (!$this->isAllowedToRun()) return;
        // TODO: Implement onMethod() method.
    }

    public function onProperty(Application $app, string $className, string $propertyName, AttributeInterface $attribute): void
    {
        if (!$this->isAllowedToRun()) return;
        // TODO: Implement onProperty() method.
    }
}