<?php

declare(strict_types=1);

namespace Platine\Test\Fixture\Dev;

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
