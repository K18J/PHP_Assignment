<?php

namespace Insurance\Calculators;

use Insurance\Policies\DiscountContext;
use Insurance\Policies\PolicyData;
use Insurance\ValueObjects\Money;
use Insurance\ValueObjects\RiskAssessment;

class PropertyPremiumCalculator implements PremiumCalculatorInterface
{
    public function calculateBasePremium(PolicyData $data): Money
    {
        $attributes = $data->getAttributes();
        $locationRisk = (float) ($attributes['location_risk'] ?? 0.05);
        $constructionTypeFactor = (float) ($attributes['construction_factor'] ?? 1.0);

        $base = $data->getCoverageAmount()->percentage(1.2 * $constructionTypeFactor);
        $locationLoading = $base->multiply(1 + $locationRisk);

        return $locationLoading;
    }

    public function applyRiskFactors(Money $premium, RiskAssessment $risk): Money
    {
        return $premium->multiply($risk->multiplier());
    }

    public function applyDiscounts(Money $premium, DiscountContext $ctx): Money
    {
        $discount = 0.0;

        if ($ctx->getNoClaimYears() >= 5) {
            $discount += 0.05;
        }

        if ($ctx->getBundleCount() > 0) {
            $discount += 0.02;
        }

        return $premium->multiply(1 - min($discount, 0.15));
    }
}

