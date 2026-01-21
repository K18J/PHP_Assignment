<?php

namespace Insurance\Policies;

use DateTimeImmutable;
use Insurance\Calculators\PremiumCalculatorInterface;
use Insurance\ValueObjects\CancellationResult;
use Insurance\ValueObjects\Money;
use Insurance\ValueObjects\PolicyStatus;
use Insurance\ValueObjects\RiskAssessment;
use Insurance\ValueObjects\ValidationResult;
use InvalidArgumentException;

abstract class AbstractPolicy
{
    protected string $policyNumber;
    protected PolicyStatus $status;
    protected Money $premium;
    protected PolicyData $policyData;
    protected RiskAssessment $riskAssessment;
    protected DiscountContext $discountContext;
    protected PremiumCalculatorInterface $calculator;
    /** @var Endorsement[] */
    protected array $endorsements = [];

    public function __construct(
        string $policyNumber,
        PolicyData $policyData,
        PremiumCalculatorInterface $calculator,
        RiskAssessment $riskAssessment,
        DiscountContext $discountContext
    ) {
        $this->policyNumber = $policyNumber;
        $this->status = PolicyStatus::draft();
        $this->policyData = $policyData;
        $this->calculator = $calculator;
        $this->riskAssessment = $riskAssessment;
        $this->discountContext = $discountContext;
        $this->premium = new Money(0);
    }

    abstract public function calculatePremium(): Money;

    abstract public function validateCoverage(): ValidationResult;

    public function cancel(DateTimeImmutable $date, string $reason): CancellationResult
    {
        if ($this->status->equals(PolicyStatus::cancelled())) {
            return new CancellationResult($date, $reason, $this->status, new Money(0));
        }

        if (!$this->status->canTransitionTo(PolicyStatus::cancelled())) {
            throw new InvalidArgumentException("Cannot cancel policy in status {$this->status}.");
        }

        $start = $this->policyData->getStartDate();
        $end = $this->policyData->getEndDate();
        $effectiveDate = $date > $end ? $end : $date;

        $totalDays = $this->policyData->getDurationDays();
        $usedDays = (int) $start->diff($effectiveDate)->format('%a');
        $usedDays = max(0, min($usedDays, $totalDays));
        $remainingDays = max(0, $totalDays - $usedDays);

        $refund = $remainingDays > 0
            ? $this->premium->proRata($remainingDays, $totalDays)
            : new Money(0, $this->premium->getCurrency());

        $this->status = $this->status->transitionTo(PolicyStatus::cancelled());

        return new CancellationResult($date, $reason, $this->status, $refund);
    }

    public function addEndorsement(Endorsement $endorsement): void
    {
        if ($endorsement->getEffectiveDate() < $this->policyData->getStartDate()) {
            throw new InvalidArgumentException('Endorsement effective date must be within policy term.');
        }

        $this->policyData = $endorsement->applyToData($this->policyData);
        $this->endorsements[] = $endorsement;

        $newPremium = $this->calculatePremium()->add($endorsement->getPremiumDelta());
        $this->premium = $newPremium;
    }

    public function getStatus(): PolicyStatus
    {
        return $this->status;
    }

    public function getPremium(): Money
    {
        return $this->premium;
    }

    public function getPolicyNumber(): string
    {
        return $this->policyNumber;
    }

    public function getPolicyData(): PolicyData
    {
        return $this->policyData;
    }

    public function getEndorsements(): array
    {
        return $this->endorsements;
    }

    public function moveToPending(): void
    {
        $this->status = $this->status->transitionTo(PolicyStatus::pending());
    }

    public function activate(): void
    {
        $this->status = $this->status->transitionTo(PolicyStatus::active());
    }

    public function expire(): void
    {
        $this->status = $this->status->transitionTo(PolicyStatus::expired());
    }

    protected function runPremiumPipeline(): Money
    {
        $base = $this->calculator->calculateBasePremium($this->policyData);
        $withRisk = $this->calculator->applyRiskFactors($base, $this->riskAssessment);
        return $this->calculator->applyDiscounts($withRisk, $this->discountContext);
    }
}

