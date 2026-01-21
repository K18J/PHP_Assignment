<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'Insurance\\';
    if (str_starts_with($class, $prefix)) {
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $path = __DIR__ . '/../src/Insurance/' . $relative . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
});

use Insurance\Calculators\HealthPremiumCalculator;
use Insurance\Calculators\LifePremiumCalculator;
use Insurance\Calculators\PropertyPremiumCalculator;
use Insurance\Calculators\VehiclePremiumCalculator;
use Insurance\Policies\DiscountContext;
use Insurance\Policies\Endorsement;
use Insurance\Policies\HealthPolicy;
use Insurance\Policies\LifePolicy;
use Insurance\Policies\PolicyData;
use Insurance\Policies\PropertyPolicy;
use Insurance\Policies\VehiclePolicy;
use Insurance\Services\PolicyIssuanceService;
use Insurance\Services\UnderwritingService;
use Insurance\ValueObjects\CoverageType;
use Insurance\ValueObjects\Money;
use Insurance\ValueObjects\RiskAssessment;

function renderPolicy(string $title, $policy): void
{
    echo PHP_EOL . $title . PHP_EOL;
    echo str_repeat('-', strlen($title)) . PHP_EOL;
    echo "Number: {$policy->getPolicyNumber()}" . PHP_EOL;
    echo "Status: {$policy->getStatus()}" . PHP_EOL;
    echo "Premium: {$policy->getPremium()}" . PHP_EOL;
    echo PHP_EOL;
}

// Common dates
$start = new \DateTimeImmutable('2024-01-01');
$end = new \DateTimeImmutable('2025-01-01');

$underwriting = new UnderwritingService();
$issuance = new PolicyIssuanceService($underwriting);

// Vehicle policy lifecycle and endorsement
$vehicleData = new PolicyData(
    CoverageType::VEHICLE,
    Money::fromFloat(25000),
    $start,
    $end,
    35,
    ['vehicle_value' => 25000, 'safety_score' => 82]
);
$vehiclePolicy = new VehiclePolicy(
    'VEH-001',
    $vehicleData,
    new VehiclePremiumCalculator(),
    new RiskAssessment(12, ['urban' => true], 0.05),
    new DiscountContext(loyaltyYears: 6, noClaimYears: 4, bundleCount: 2)
);

$issuance->issue($vehiclePolicy);
renderPolicy('Vehicle Policy - Issued', $vehiclePolicy);

$endorsement = new Endorsement(
    'Added premium sound system',
    new \DateTimeImmutable('2024-06-01'),
    ['vehicle_value' => 27000],
    Money::fromFloat(120)
);
$vehiclePolicy->addEndorsement($endorsement);
renderPolicy('Vehicle Policy - After Endorsement', $vehiclePolicy);

$cancellation = $vehiclePolicy->cancel(new \DateTimeImmutable('2024-09-15'), 'Sold vehicle mid-term');
echo "Cancellation refund: {$cancellation->getRefund()}" . PHP_EOL;
echo "Final status: {$cancellation->getFinalStatus()}" . PHP_EOL;

// Health policy example
$healthData = new PolicyData(
    CoverageType::HEALTH,
    Money::fromFloat(10000),
    $start,
    $end,
    40,
    ['conditions_count' => 1]
);
$healthPolicy = new HealthPolicy(
    'HEA-101',
    $healthData,
    new HealthPremiumCalculator(),
    new RiskAssessment(8, ['fitness' => 'average']),
    new DiscountContext(loyaltyYears: 3, noClaimYears: 5, bundleCount: 1, vipCustomer: true)
);

$issuance->issue($healthPolicy);
renderPolicy('Health Policy - Issued', $healthPolicy);

// Life policy example
$lifeData = new PolicyData(
    CoverageType::LIFE,
    Money::fromFloat(150000),
    $start,
    $end,
    32,
    ['smoker' => false]
);
$lifePolicy = new LifePolicy(
    'LIF-555',
    $lifeData,
    new LifePremiumCalculator(),
    new RiskAssessment(6, ['occupation' => 'office']),
    new DiscountContext(loyaltyYears: 4, bundleCount: 2)
);

$issuance->issue($lifePolicy);
renderPolicy('Life Policy - Issued', $lifePolicy);

// Property policy example
$propertyData = new PolicyData(
    CoverageType::PROPERTY,
    Money::fromFloat(300000),
    $start,
    $end,
    45,
    ['location_risk' => 0.08, 'construction_factor' => 1.1]
);
$propertyPolicy = new PropertyPolicy(
    'PRO-777',
    $propertyData,
    new PropertyPremiumCalculator(),
    new RiskAssessment(10, ['coastal' => true]),
    new DiscountContext(noClaimYears: 6, bundleCount: 1)
);

$issuance->issue($propertyPolicy);
renderPolicy('Property Policy - Issued', $propertyPolicy);

echo PHP_EOL . "Demo complete." . PHP_EOL;

