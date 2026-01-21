<?php

namespace Insurance\Policies;

use Insurance\ValueObjects\CoverageType;
use Insurance\ValueObjects\Money;
use Insurance\ValueObjects\ValidationResult;

class LifePolicy extends AbstractPolicy
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

        if ($this->policyData->getCoverageType() !== CoverageType::LIFE) {
            $result->addError('LifePolicy must use life coverage type.');
        }

        if ($this->policyData->getCustomerAge() < 18) {
            $result->addError('Life insurance requires adult customers.');
        }

        if (!array_key_exists('smoker', $attrs)) {
            $result->addError('Smoking status must be provided.');
        }

        return $result;
    }
}

