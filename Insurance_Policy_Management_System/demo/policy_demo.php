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

header('Content-Type: text/html; charset=utf-8');
echo '<!doctype html><html><head><meta charset="utf-8"><title>Policy Demo</title>';
echo '<style>
body{font-family:Arial, sans-serif; padding:16px; max-width:960px; margin:auto; line-height:1.4;}
form{padding:12px; border:1px solid #ddd; margin-bottom:18px; border-radius:8px; background:#fafafa;}
label{display:block; margin-top:8px; font-weight:bold;}
input, select{padding:6px; width:100%; max-width:320px; margin-top:4px;}
.row{display:flex; gap:16px; flex-wrap:wrap;}
.row .field{flex:1 1 200px;}
.block{margin-bottom:14px; padding:10px; border:1px solid #e5e5e5; border-radius:6px; background:#fff;}
.title{font-weight:bold; margin-bottom:4px;}
.footer{margin-top:14px; font-style:italic;}
.note{font-size:12px; color:#666;}
</style>';
echo '</head><body>';

$h = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

function input(string $key, $default = null)
{
    $value = $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $value;
}

function renderPolicy(string $title, $policy): string
{
    $lines = [
        '<div class="block">',
        '<div class="title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div>',
        'Number: ' . htmlspecialchars($policy->getPolicyNumber(), ENT_QUOTES, 'UTF-8'),
        'Status: ' . htmlspecialchars((string) $policy->getStatus(), ENT_QUOTES, 'UTF-8'),
        'Premium: ' . htmlspecialchars((string) $policy->getPremium(), ENT_QUOTES, 'UTF-8'),
        '</div>',
    ];

    return implode('<br>', $lines);
}

$policyType = strtolower((string) input('policy_type', CoverageType::VEHICLE));
$coverageAmount = (float) input('coverage_amount', 25000);
$customerAge = (int) input('customer_age', 35);
$startDate = input('start_date', '2024-01-01');
$endDate = input('end_date', '2025-01-01');
$riskScore = (float) input('risk_score', 10);
$baseAdjustment = (float) input('base_adjustment', 0.05);
$loyaltyYears = (int) input('loyalty_years', 3);
$noClaimYears = (int) input('no_claim_years', 2);
$bundleCount = (int) input('bundle_count', 1);
$vip = (bool) input('vip', false);
$endorse = (bool) input('endorse', true);
$endorseDelta = (float) input('endorse_delta', 120);
$cancelDate = input('cancel_date', '2024-09-15');
$cancelReason = input('cancel_reason', 'Sold asset mid-term');

echo '<form method="get">';
echo '<div class="row">';
echo '<div class="field"><label>Policy type</label><select name="policy_type">';
foreach (CoverageType::all() as $type) {
    $sel = $policyType === $type ? 'selected' : '';
    echo '<option value="' . $h($type) . "\" $sel>" . ucfirst($type) . '</option>';
}
echo '</select></div>';
echo '<div class="field"><label>Coverage amount (USD)</label><input type="number" step="0.01" name="coverage_amount" value="' . $h((string) $coverageAmount) . '"></div>';
echo '<div class="field"><label>Customer age</label><input type="number" name="customer_age" value="' . $h((string) $customerAge) . '"></div>';
echo '<div class="field"><label>Start date</label><input type="date" name="start_date" value="' . $h($startDate) . '"></div>';
echo '<div class="field"><label>End date</label><input type="date" name="end_date" value="' . $h($endDate) . '"></div>';
echo '</div>';

echo '<div class="row">';
echo '<div class="field"><label>Risk score (%)</label><input type="number" step="0.1" name="risk_score" value="' . $h((string) $riskScore) . '"></div>';
echo '<div class="field"><label>Base adjustment (multiplier offset)</label><input type="number" step="0.01" name="base_adjustment" value="' . $h((string) $baseAdjustment) . '"></div>';
echo '<div class="field"><label>Loyalty years</label><input type="number" name="loyalty_years" value="' . $h((string) $loyaltyYears) . '"></div>';
echo '<div class="field"><label>No-claim years</label><input type="number" name="no_claim_years" value="' . $h((string) $noClaimYears) . '"></div>';
echo '<div class="field"><label>Bundle count</label><input type="number" name="bundle_count" value="' . $h((string) $bundleCount) . '"></div>';
echo '<div class="field"><label>VIP</label><select name="vip"><option value="0">No</option><option value="1"' . ($vip ? ' selected' : '') . '>Yes</option></select></div>';
echo '</div>';

// Type-specific inputs
echo '<div class="row">';
echo '<div class="field" data-type="vehicle"><label>Vehicle value (for Vehicle)</label><input type="number" step="0.01" name="vehicle_value" value="' . $h((string) input('vehicle_value', 25000)) . '"></div>';
echo '<div class="field" data-type="vehicle"><label>Vehicle safety score (0-100)</label><input type="number" step="1" name="safety_score" value="' . $h((string) input('safety_score', 80)) . '"></div>';
echo '<div class="field" data-type="health"><label>Health: pre-existing conditions count</label><input type="number" name="conditions_count" value="' . $h((string) input('conditions_count', 1)) . '"></div>';
echo '<div class="field" data-type="life"><label>Life: smoker (1=yes,0=no)</label><select name="smoker"><option value="0">No</option><option value="1"' . (input('smoker', '0') === '1' ? ' selected' : '') . '>Yes</option></select></div>';
echo '<div class="field" data-type="property"><label>Property: location risk (0.00-1.0)</label><input type="number" step="0.01" name="location_risk" value="' . $h((string) input('location_risk', 0.08)) . '"></div>';
echo '<div class="field" data-type="property"><label>Property: construction factor</label><input type="number" step="0.01" name="construction_factor" value="' . $h((string) input('construction_factor', 1.1)) . '"></div>';
echo '</div>';

echo '<div class="row">';
echo '<div class="field"><label>Add endorsement?</label><select name="endorse"><option value="1"' . ($endorse ? ' selected' : '') . '>Yes</option><option value="0"' . (!$endorse ? ' selected' : '') . '>No</option></select></div>';
echo '<div class="field"><label>Endorsement premium delta (USD)</label><input type="number" step="0.01" name="endorse_delta" value="' . $h((string) $endorseDelta) . '"></div>';
echo '<div class="field"><label>Cancellation date</label><input type="date" name="cancel_date" value="' . $h($cancelDate) . '"></div>';
echo '<div class="field"><label>Cancellation reason</label><input type="text" name="cancel_reason" value="' . $h($cancelReason) . '"></div>';
echo '</div>';

echo '<button type="submit" style="margin-top:12px;padding:8px 12px;">Run Demo</button>';
echo '<div class="note">Change fields then click Run Demo to see updated premiums, endorsements, and pro-rata cancellation. Type-specific fields show only for the chosen policy.</div>';
echo '</form>';

$start = new \DateTimeImmutable($startDate);
$end = new \DateTimeImmutable($endDate);
$attributes = [];

switch ($policyType) {
    case CoverageType::VEHICLE:
        $attributes = [
            'vehicle_value' => (float) input('vehicle_value', $coverageAmount),
            'safety_score' => (float) input('safety_score', 80),
        ];
        $calculator = new VehiclePremiumCalculator();
        $policyClass = VehiclePolicy::class;
        $policyNumber = 'VEH-001';
        break;
    case CoverageType::HEALTH:
        $attributes = [
            'conditions_count' => (int) input('conditions_count', 1),
        ];
        $calculator = new HealthPremiumCalculator();
        $policyClass = HealthPolicy::class;
        $policyNumber = 'HEA-101';
        break;
    case CoverageType::LIFE:
        $attributes = [
            'smoker' => input('smoker', '0') === '1',
        ];
        $calculator = new LifePremiumCalculator();
        $policyClass = LifePolicy::class;
        $policyNumber = 'LIF-555';
        break;
    default:
        $policyType = CoverageType::PROPERTY;
        $attributes = [
            'location_risk' => (float) input('location_risk', 0.08),
            'construction_factor' => (float) input('construction_factor', 1.1),
        ];
        $calculator = new PropertyPremiumCalculator();
        $policyClass = PropertyPolicy::class;
        $policyNumber = 'PRO-777';
        break;
}

$policyData = new PolicyData(
    $policyType,
    Money::fromFloat($coverageAmount),
    $start,
    $end,
    $customerAge,
    $attributes
);

$riskAssessment = new RiskAssessment($riskScore, [], $baseAdjustment);
$discountContext = new DiscountContext($loyaltyYears, $noClaimYears, $bundleCount, $vip);
$underwriting = new UnderwritingService();
$issuance = new PolicyIssuanceService($underwriting);

/** @var \Insurance\Policies\AbstractPolicy $policy */
$policy = new $policyClass(
    $policyNumber,
    $policyData,
    $calculator,
    $riskAssessment,
    $discountContext
);

$issuance->issue($policy);
$output = [];
$output[] = renderPolicy(ucfirst($policyType) . ' Policy - Issued', $policy);

if ($endorse) {
    $endorsement = new Endorsement(
        'User endorsement',
        $start->modify('+5 months'),
        $attributes,
        Money::fromFloat($endorseDelta)
    );
    $policy->addEndorsement($endorsement);
    $output[] = renderPolicy(ucfirst($policyType) . ' Policy - After Endorsement', $policy);
}

$cancellation = $policy->cancel(new \DateTimeImmutable($cancelDate), $cancelReason);
$output[] = '<div class="block">Cancellation refund: ' . $h((string) $cancellation->getRefund()) . '<br>Final status: ' . $h((string) $cancellation->getFinalStatus()) . '</div>';

echo implode(PHP_EOL, $output);
echo '<div class="footer">Demo complete.</div>';
?>
<script>
(() => {
  const typeSelect = document.querySelector('select[name="policy_type"]');
  const toggleFields = () => {
    const type = typeSelect.value;
    document.querySelectorAll('[data-type]').forEach((el) => {
      const show = el.dataset.type === type;
      el.style.display = show ? 'block' : 'none';
    });
  };
  typeSelect.addEventListener('change', toggleFields);
  toggleFields();
})();
</script>
</body></html>