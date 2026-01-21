<?php

namespace Insurance\Calculators;

use Insurance\Policies\DiscountContext;
use Insurance\Policies\PolicyData;
use Insurance\ValueObjects\Money;
use Insurance\ValueObjects\RiskAssessment;

class HealthPremiumCalculator implements PremiumCalculatorInterface
{
    public function calculateBasePremium(PolicyData $data): Money
    {
        $attributes = $data->getAttributes();
        $ageFactor = max(1.0, $data->getCustomerAge() / 45);
        $preExistingConditions = (int) ($attributes['conditions_count'] ?? 0);

        $base = $data->getCoverageAmount()->percentage(2.0 * $ageFactor);
        $conditionLoading = $data->getCoverageAmount()->percentage($preExistingConditions * 0.5);

        return $base->add($conditionLoading);
    }

    public function applyRiskFactors(Money $premium, RiskAssessment $risk): Money
    {
        return $premium->multiply($risk->multiplier());
    }

    public function applyDiscounts(Money $premium, DiscountContext $ctx): Money
    {
        $discount = 0.0;

        if ($ctx->getNoClaimYears() >= 2) {
            $discount += 0.03;
        }

        if ($ctx->getLoyaltyYears() >= 4) {
            $discount += 0.02;
        }

        if ($ctx->isVip()) {
            $discount += 0.05;
        }

        return $premium->multiply(1 - min($discount, 0.2));
    }
}

