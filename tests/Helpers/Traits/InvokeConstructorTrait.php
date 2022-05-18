<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Helpers\Traits;

use ReflectionClass;
use ReflectionException;

trait InvokeConstructorTrait
{
    /**
     * @param string $class
     * @param array $parameters
     * @return object
     * @throws ReflectionException
     */
    public function invokeConstructor(string $class, array $parameters = array()): object
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        $constructor->setAccessible(true);
        $object = $reflection->newInstanceWithoutConstructor();
        $constructor->invokeArgs($object, $parameters);
        return $object;
    }
}
