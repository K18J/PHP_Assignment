<?php

namespace Insurance\ValueObjects;

class RiskAssessment
{
    private float $riskScore;
    private array $factors;
    private float $baseRateAdjustment;

    public function __construct(float $riskScore, array $factors = [], float $baseRateAdjustment = 0.0)
    {
        $this->riskScore = $riskScore;
        $this->factors = $factors;
        $this->baseRateAdjustment = $baseRateAdjustment;
    }

    public function getRiskScore(): float
    {
        return $this->riskScore;
    }

    public function getFactors(): array
    {
        return $this->factors;
    }

    public function getBaseRateAdjustment(): float
    {
        return $this->baseRateAdjustment;
    }

    public function multiplier(): float
    {
        // Risk score expressed as percentage above base rate.
        return 1 + $this->baseRateAdjustment + ($this->riskScore / 100);
    }
}

