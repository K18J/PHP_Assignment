<?php

namespace Insurance\Services;

use Insurance\Policies\AbstractPolicy;
use RuntimeException;

class PolicyIssuanceService
{
    private UnderwritingService $underwritingService;

    public function __construct(UnderwritingService $underwritingService)
    {
        $this->underwritingService = $underwritingService;
    }

    public function issue(AbstractPolicy $policy): void
    {
        $validation = $this->underwritingService->evaluate($policy);

        if (!$validation->isValid()) {
            throw new RuntimeException('Policy cannot be issued: ' . implode(', ', $validation->getErrors()));
        }

        $policy->moveToPending();
        $policy->activate();
    }
}

