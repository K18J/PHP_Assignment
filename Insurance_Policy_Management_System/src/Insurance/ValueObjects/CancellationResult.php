<?php

namespace Insurance\ValueObjects;

use DateTimeImmutable;
use Insurance\ValueObjects\Money;
use Insurance\ValueObjects\PolicyStatus;

class CancellationResult
{
    private DateTimeImmutable $cancellationDate;
    private string $reason;
    private PolicyStatus $finalStatus;
    private Money $refund;

    public function __construct(DateTimeImmutable $cancellationDate, string $reason, PolicyStatus $finalStatus, Money $refund)
    {
        $this->cancellationDate = $cancellationDate;
        $this->reason = $reason;
        $this->finalStatus = $finalStatus;
        $this->refund = $refund;
    }

    public function getRefund(): Money
    {
        return $this->refund;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getCancellationDate(): DateTimeImmutable
    {
        return $this->cancellationDate;
    }

    public function getFinalStatus(): PolicyStatus
    {
        return $this->finalStatus;
    }
}

