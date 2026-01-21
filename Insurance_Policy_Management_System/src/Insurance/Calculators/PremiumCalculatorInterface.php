<?php

namespace Insurance\Calculators;

use Insurance\Policies\DiscountContext;
use Insurance\Policies\PolicyData;
use Insurance\ValueObjects\Money;
use Insurance\ValueObjects\RiskAssessment;

interface PremiumCalculatorInterface
{
    public function calculateBasePremium(PolicyData $data): Money;

    public function applyRiskFactors(Money $premium, RiskAssessment $risk): Money;

    public function applyDiscounts(Money $premium, DiscountContext $ctx): Money;
}

