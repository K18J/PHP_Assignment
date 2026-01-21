<?php

namespace Insurance\Policies;

class DiscountContext
{
    public function __construct(
        private int $loyaltyYears = 0,
        private int $noClaimYears = 0,
        private int $bundleCount = 0,
        private bool $vipCustomer = false
    ) {
    }

    public function getLoyaltyYears(): int
    {
        return $this->loyaltyYears;
    }

    public function getNoClaimYears(): int
    {
        return $this->noClaimYears;
    }

    public function getBundleCount(): int
    {
        return $this->bundleCount;
    }

    public function isVip(): bool
    {
        return $this->vipCustomer;
    }
}

