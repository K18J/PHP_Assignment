<?php

namespace Insurance\Policies;

use Insurance\ValueObjects\CoverageType;
use Insurance\ValueObjects\Money;
use Insurance\ValueObjects\ValidationResult;

class HealthPolicy extends AbstractPolicy
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

        if ($this->policyData->getCoverageType() !== CoverageType::HEALTH) {
            $result->addError('HealthPolicy must use health coverage type.');
        }

        if ($this->policyData->getCustomerAge() <= 0) {
            $result->addError('Customer age must be greater than zero.');
        }

        if (($attrs['conditions_count'] ?? 0) < 0) {
            $result->addError('Pre-existing conditions count cannot be negative.');
        }

        return $result;
    }
}

