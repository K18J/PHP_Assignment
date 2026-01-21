<?php

namespace Insurance\Policies;

use Insurance\ValueObjects\CoverageType;
use Insurance\ValueObjects\Money;
use Insurance\ValueObjects\ValidationResult;

class PropertyPolicy extends AbstractPolicy
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

        if ($this->policyData->getCoverageType() !== CoverageType::PROPERTY) {
            $result->addError('PropertyPolicy must use property coverage type.');
        }

        if (($attrs['location_risk'] ?? 0) < 0) {
            $result->addError('Location risk must be zero or positive.');
        }

        if (($attrs['construction_factor'] ?? 0) <= 0) {
            $result->addError('Construction factor must be positive.');
        }

        return $result;
    }
}

