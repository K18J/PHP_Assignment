<?php

namespace Insurance\Policies;

use DateTimeImmutable;
use Insurance\ValueObjects\Money;

class Endorsement
{
    private string $description;
    private DateTimeImmutable $effectiveDate;
    private array $dataChanges;
    private Money $premiumDelta;

    public function __construct(string $description, DateTimeImmutable $effectiveDate, array $dataChanges = [], ?Money $premiumDelta = null)
    {
        $this->description = $description;
        $this->effectiveDate = $effectiveDate;
        $this->dataChanges = $dataChanges;
        $this->premiumDelta = $premiumDelta ?? new Money(0);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getEffectiveDate(): DateTimeImmutable
    {
        return $this->effectiveDate;
    }

    public function applyToData(PolicyData $data): PolicyData
    {
        return $data->withAttributes($this->dataChanges);
    }

    public function getPremiumDelta(): Money
    {
        return $this->premiumDelta;
    }
}

