<?php

namespace Insurance\ValueObjects;

use InvalidArgumentException;

class PolicyStatus
{
    public const DRAFT = 'draft';
    public const PENDING = 'pending';
    public const ACTIVE = 'active';
    public const EXPIRED = 'expired';
    public const CANCELLED = 'cancelled';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function expired(): self
    {
        return new self(self::EXPIRED);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public function transitionTo(self $target): self
    {
        if (!$this->canTransitionTo($target)) {
            throw new InvalidArgumentException("Cannot transition from {$this->value} to {$target->value}");
        }

        return $target;
    }

    public function canTransitionTo(self $target): bool
    {
        return match ($this->value) {
            self::DRAFT => in_array($target->value, [self::PENDING, self::CANCELLED], true),
            self::PENDING => in_array($target->value, [self::ACTIVE, self::CANCELLED], true),
            self::ACTIVE => in_array($target->value, [self::EXPIRED, self::CANCELLED], true),
            default => false,
        };
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

