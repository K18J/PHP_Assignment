<?php

namespace Insurance\Policies;

use DateInterval;
use DateTimeImmutable;
use Insurance\ValueObjects\CoverageType;
use Insurance\ValueObjects\Money;
use InvalidArgumentException;

class PolicyData
{
    private string $coverageType;
    private Money $coverageAmount;
    private DateTimeImmutable $startDate;
    private DateTimeImmutable $endDate;
    private int $customerAge;
    private array $attributes;

    public function __construct(
        string $coverageType,
        Money $coverageAmount,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        int $customerAge,
        array $attributes = []
    ) {
        if (!CoverageType::isValid($coverageType)) {
            throw new InvalidArgumentException('Unsupported coverage type.');
        }

        if ($endDate <= $startDate) {
            throw new InvalidArgumentException('End date must be after start date.');
        }

        $this->coverageType = $coverageType;
        $this->coverageAmount = $coverageAmount;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->customerAge = $customerAge;
        $this->attributes = $attributes;
    }

    public function getCoverageType(): string
    {
        return $this->coverageType;
    }

    public function getCoverageAmount(): Money
    {
        return $this->coverageAmount;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getCustomerAge(): int
    {
        return $this->customerAge;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function withAttributes(array $attributes): self
    {
        $clone = clone $this;
        $clone->attributes = array_merge($this->attributes, $attributes);
        return $clone;
    }

    public function getDurationDays(): int
    {
        $interval = $this->startDate->diff($this->endDate);
        return (int) $interval->format('%a');
    }
}

