<?php

namespace Insurance\Calculators;

use Insurance\Policies\DiscountContext;
use Insurance\Policies\PolicyData;
use Insurance\ValueObjects\Money;
use Insurance\ValueObjects\RiskAssessment;

class VehiclePremiumCalculator implements PremiumCalculatorInterface
{
    public function calculateBasePremium(PolicyData $data): Money
    {
        $attributes = $data->getAttributes();
        $vehicleValue = (float) ($attributes['vehicle_value'] ?? 0);
        $safetyScore = (float) ($attributes['safety_score'] ?? 0);

        $baseRate = max(0.03, 0.05 - ($safetyScore / 1000)); 
        $coveragePortion = $data->getCoverageAmount()->percentage($baseRate * 100);

        $valuePortion = Money::fromFloat($vehicleValue * 0.01); 

        return $coveragePortion->add($valuePortion);
    }

    public function applyRiskFactors(Money $premium, RiskAssessment $risk): Money
    {
        return $premium->multiply($risk->multiplier());
    }

    public function applyDiscounts(Money $premium, DiscountContext $ctx): Money
    {
        $discount = 0.0;

        if ($ctx->getLoyaltyYears() >= 5) {
            $discount += 0.05;
        }

        if ($ctx->getNoClaimYears() >= 3) {
            $discount += 0.04;
        }

        if ($ctx->getBundleCount() > 1) {
            $discount += 0.02;
        }

        if ($ctx->isVip()) {
            $discount += 0.03;
        }

        $discount = min($discount, 0.25);

        return $premium->multiply(1 - $discount);
    }
}

