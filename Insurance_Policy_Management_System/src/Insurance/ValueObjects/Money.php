<?php

namespace Insurance\ValueObjects;

use InvalidArgumentException;

class Money
{
    private int $amount;
    private string $currency;

    public function __construct(int $amount, string $currency = 'USD')
    {
        if ($currency === '') {
            throw new InvalidArgumentException('Currency cannot be empty.');
        }

        $this->amount = $amount;
        $this->currency = $currency;
    }

    public static function fromFloat(float $value, string $currency = 'USD'): self
    {
        $minor = (int) round($value * 100);
        return new self($minor, $currency);
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float $factor): self
    {
        $minor = (int) round($this->amount * $factor);
        return new self($minor, $this->currency);
    }

    public function percentage(float $percent): self
    {
        return $this->multiply($percent / 100);
    }

    public function proRata(int $numerator, int $denominator): self
    {
        if ($denominator <= 0) {
            throw new InvalidArgumentException('Denominator must be positive for pro-rata calculation.');
        }

        $factor = $numerator / $denominator;
        return $this->multiply($factor);
    }

    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    public function __toString(): string
    {
        $formatted = number_format($this->amount / 100, 2, '.', ',');
        return "{$this->currency} {$formatted}";
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Currency mismatch.');
        }
    }
}