<?php

namespace Insurance\Calculators;

use Insurance\Policies\DiscountContext;
use Insurance\Policies\PolicyData;
use Insurance\ValueObjects\Money;
use Insurance\ValueObjects\RiskAssessment;

class LifePremiumCalculator implements PremiumCalculatorInterface
{
    public function calculateBasePremium(PolicyData $data): Money
    {
        $age = $data->getCustomerAge();
        $ageLoading = max(1.1, $age / 50);
        $smoker = (bool) ($data->getAttributes()['smoker'] ?? false);

        $base = $data->getCoverageAmount()->percentage(1.0 * $ageLoading);
        if ($smoker) {
            $base = $base->multiply(1.25);
        }

        return $base;
    }

    public function applyRiskFactors(Money $premium, RiskAssessment $risk): Money
    {
        return $premium->multiply($risk->multiplier());
    }

    public function applyDiscounts(Money $premium, DiscountContext $ctx): Money
    {
        $discount = 0.0;

        if ($ctx->getLoyaltyYears() >= 3) {
            $discount += 0.03;
        }

        if ($ctx->getBundleCount() > 2) {
            $discount += 0.02;
        }

        return $premium->multiply(1 - min($discount, 0.15));
    }
}

