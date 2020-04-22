<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Unit;

use RemotelyLiving\PHPQueryBus\AbstractResult;

class AbstractResultTest extends AbstractTestCase
{
    private AbstractResult $abstractResult;

    protected function setUp(): void
    {
        $this->abstractResult = new class extends AbstractResult {

        };
    }

    public function testDefaults(): void
    {
        $this->assertFalse($this->abstractResult->isNotFound());
        $this->assertFalse($this->abstractResult->hasErrors());
        $this->assertEquals([], $this->abstractResult->getErrors());
    }

    public function testFactoryCreatesNotFoundResult(): void
    {
        $this->assertTrue(AbstractResult::notFound()->isNotFound());
        $this->assertFalse(AbstractResult::notFound()->hasErrors());
        $this->assertEquals([], AbstractResult::notFound()->getErrors());
    }

    public function testFactoryCreatesResultWithEmptyErrors(): void
    {
        $errors = [new \InvalidArgumentException(), new \LogicException()];
        $this->assertFalse(AbstractResult::withErrors(...$errors)->isNotFound());
        $this->assertTrue(AbstractResult::withErrors(...$errors)->hasErrors());
        $this->assertEquals($errors, AbstractResult::withErrors(...$errors)->getErrors());
    }
}
