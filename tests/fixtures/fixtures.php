<?php

declare(strict_types=1);

namespace Platine\Test\Fixture\Dev;

use stdClass;

abstract class CreateObjectIsNotInstantiable
{
}

class ExpectMethodCallCountBase
{
    public function call(): void
    {
    }
}

class ExpectMethodCallCountDep
{
    public function callBase(ExpectMethodCallCountBase $a)
    {
        $a->call();
    }
}

class CreateObjectNoConstructor
{
}

class CreateObjectAll
{
    public $a;
    public $b;
    public stdClass $c;
    public function __construct($a, stdClass $c, $b = 5)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }
}

class GetPrivateProtectedAttributeTestClass
{
    /**
     *
     * @var string
     */
    private string $name = 'foo';
    protected int $value = 123;

    /**
     *
     * @return string
     */
    private function privateMethod(): string
    {
        return $this->name;
    }

    /**
     * @param int $v
     * @return int
     */
    protected function protectedMethod(int $v): int
    {
        return $this->value * $v;
    }
}

class ClassToMock
{
    public int $a = 1;

    public function a(): int
    {
        return 10;
    }

    public function b(int $param): bool
    {
        return $param > 0;
    }
}
