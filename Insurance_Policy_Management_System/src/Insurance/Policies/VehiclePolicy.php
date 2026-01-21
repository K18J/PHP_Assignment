<?php

namespace Insurance\Policies;

use Insurance\ValueObjects\CoverageType;
use Insurance\ValueObjects\Money;
use Insurance\ValueObjects\ValidationResult;

class VehiclePolicy extends AbstractPolicy
{
    public function calculatePremium(): Money
    {
        $this->premium = $this->runPremiumPipeline();
        return $this->premium;
    }

    public function validateCoverage(): ValidationResult
    {
        $result = new ValidationResult();
        $attrs = $this->policyData->getAttributes();

        if ($this->policyData->getCoverageType() !== CoverageType::VEHICLE) {
            $result->addError('VehiclePolicy must use vehicle coverage type.');
        }

        if (!isset($attrs['vehicle_value']) || $attrs['vehicle_value'] <= 0) {
            $result->addError('Vehicle value must be provided.');
        }

        if (($attrs['safety_score'] ?? 0) < 0) {
            $result->addError('Safety score cannot be negative.');
        }

        return $result;
    }
}

