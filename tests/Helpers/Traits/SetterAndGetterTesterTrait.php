<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Helpers\Traits;

trait SetterAndGetterTesterTrait
{
    /**
     * @var array $fields
     */
    protected array $fields;

    public function _testSettersAndGetters($entity): void
    {
        foreach ($this->fields as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            $entity->$methodName($value);
        }

        foreach ($this->fields as $key => $value) {
            $methodName = 'get' . ucfirst($key);
            $this->assertEquals($value, $entity->$methodName());
        }
    }
}
