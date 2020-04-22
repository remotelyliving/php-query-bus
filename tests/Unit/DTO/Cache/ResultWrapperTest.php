<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Unit;

use RemotelyLiving\PHPQueryBus\DTO;
use RemotelyLiving\PHPQueryBus\Exceptions\UnserializationFailure;
use RemotelyLiving\PHPQueryBus\Interfaces;
use RemotelyLiving\PHPQueryBus\Tests\Stubs;

class ResultWrapperTest extends AbstractTestCase
{
    private DTO\Cache\ResultWrapper $resultWrapper;

    private Interfaces\Result $result;

    private float $recomputeSeconds = 10.0;

    private int $ttl = 30;

    protected function setUp(): void
    {
        $this->result = new Stubs\GetUserResult(new \stdClass());
        $this->resultWrapper = DTO\Cache\ResultWrapper::wrap($this->result, $this->recomputeSeconds, $this->ttl);
    }

    public function testSerializesAndUnserializes(): void
    {
        $rehydrated = \unserialize(serialize($this->resultWrapper));
        $this->assertEquals($this->resultWrapper->getResult(), $rehydrated->getResult());
    }

    public function testReturnsWithErrorsResultOnSerializationError(): void
    {
        // phpcs:disable
        $serializedWithBadData = 'C:50:"RemotelyLiving\PHPQueryBus\DTO\Cache\ResultWrapper":130:{a:4:{s:6:"result";a:1:{i:0;s:13:"not an object";}s:16:"recomputeSeconds";d:10;s:3:"ttl";i:30;s:15:"storedTimestamp";i:1587557981;}}';
        // phpcs:enable
        $resultWithErrors = unserialize($serializedWithBadData);

        $this->assertTrue($resultWithErrors->getResult()->hasErrors());
        $this->assertTrue($resultWithErrors->getResult()->getErrors()[0] instanceof UnserializationFailure);
    }

    public function testWillProbablyExpireSoon(): void
    {
        $this->resultWrapper->setTimeStamp(100);
        $this->assertFalse($this->resultWrapper->willProbablyExpireSoon());

        /** @var \RemotelyLiving\PHPQueryBus\DTO\Cache\ResultWrapper $storedWithTimestamp */
        $storedWithTimestamp = \unserialize(\serialize($this->resultWrapper));

        $storedWithTimestamp->setTimeStamp(100 + 1);
        $this->assertThat(
            $storedWithTimestamp->willProbablyExpireSoon(),
            $this->logicalOr($this->isTrue(), $this->isFalse())
        );

        $storedWithTimestamp->setTimeStamp(100 + 5);
        $this->assertThat(
            $storedWithTimestamp->willProbablyExpireSoon(),
            $this->logicalOr($this->isTrue(), $this->isFalse())
        );

        $storedWithTimestamp->setTimeStamp(100 + 15);
        $this->assertThat(
            $storedWithTimestamp->willProbablyExpireSoon(),
            $this->logicalOr($this->isTrue(), $this->isFalse())
        );

        // definitely expiring soon
        $storedWithTimestamp->setTimeStamp(100 + 25);
        $this->assertThat(
            $storedWithTimestamp->willProbablyExpireSoon(),
            $this->logicalOr($this->isTrue(), $this->isFalse())
        );

        $storedWithTimestamp->setTimeStamp(100 + $this->ttl + 1);
        $this->assertTrue($storedWithTimestamp->willProbablyExpireSoon());
    }
}
