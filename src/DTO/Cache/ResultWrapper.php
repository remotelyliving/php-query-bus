<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\DTO\Cache;

use RemotelyLiving\PHPQueryBus\AbstractResult;
use RemotelyLiving\PHPQueryBus\Exceptions\UnserializationFailure;
use RemotelyLiving\PHPQueryBus\Interfaces;
use RemotelyLiving\PHPQueryBus\Traits;

final class ResultWrapper implements \Serializable
{
    use Traits\TimeBoundary;

    private Interfaces\Result $result;

    private float $recomputeSeconds;

    private int $ttl;

    private float $storedTimestamp;

    private function __construct()
    {
    }

    public static function wrap(Interfaces\Result $result, float $recomputeSeconds, int $ttl): self
    {
        $self = new self();
        $self->result = $result;
        $self->recomputeSeconds = $recomputeSeconds;
        $self->ttl = $ttl;

        return $self;
    }

    public function serialize(): string
    {
        return \serialize([
            'result' => $this->result,
            'recomputeSeconds' => $this->recomputeSeconds,
            'ttl' => $this->ttl,
            'storedTimestamp' => $this->getTimeStamp(),
         ]);
    }

    /**
     * @psalm-suppress PropertyTypeCoercion
     * @phpstan-ignore-next-line
     */
    public function unserialize($serialized, array $options = null): void
    {
        $unserialized = (!$options) ? \unserialize($serialized) : \unserialize($serialized, $options);
        $this->result = (is_object($unserialized['result']) && $unserialized['result'] instanceof Interfaces\Result)
            ? $unserialized['result']
            : AbstractResult::withErrors(new UnserializationFailure('Result object failed to unserialize properly'));
        $this->recomputeSeconds = $unserialized['recomputeSeconds'];
        $this->ttl = $unserialized['ttl'];
        $this->storedTimestamp = $unserialized['storedTimestamp'];
    }

    public function getResult(): Interfaces\Result
    {
        return $this->result;
    }

    public function willProbablyExpireSoon(): bool
    {
        return ($this->getTimeStamp() - $this->recomputeSeconds * 1 * log(mt_rand() / mt_getrandmax())
          > $this->expiresAfterTimestamp());
    }

    private function expiresAfterTimestamp(): int
    {
        return (int) (($this->storedTimestamp ?? $this->getTimeStamp()) + $this->ttl);
    }
}
