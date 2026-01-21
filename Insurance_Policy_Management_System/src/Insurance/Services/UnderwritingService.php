<?php

namespace Insurance\Services;

use Insurance\Policies\AbstractPolicy;
use Insurance\ValueObjects\ValidationResult;

class UnderwritingService
{
    public function evaluate(AbstractPolicy $policy): ValidationResult
    {
        $result = $policy->validateCoverage();

        if (!$result->isValid()) {
            return $result;
        }

        $premium = $policy->calculatePremium();
        if ($premium->getAmount() <= 0) {
            $result->addError('Calculated premium must be positive.');
        }

        return $result;
    }
}

