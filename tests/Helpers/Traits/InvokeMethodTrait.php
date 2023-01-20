<?php

namespace IzzyPay\Tests\Helpers\Traits;

use ReflectionClass;
use ReflectionException;

trait InvokeMethodTrait
{
    /**
     * @param $object
     * @param $methodName
     * @param array $parameters
     * @return mixed
     * @throws ReflectionException
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array()): mixed
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
