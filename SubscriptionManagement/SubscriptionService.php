<?php
namespace Repository;

use Shared\Core\Database;

class SubscriptionService
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getDbObject();
    }

    public function activate($subscriptionId)
    {
        return $this->updateStatus($subscriptionId, 'active');
    }

    public function suspend($subscriptionId, $reason)
    {
        return $this->updateStatus($subscriptionId, 'suspended');
    }

    public function cancel($subscriptionId, $immediate = false)
    {
        $subscription = $this->getSubscription($subscriptionId);
        if (!$subscription) {
            return false;
        }

        $status = 'cancelled';
        $endDate = $immediate ? date('Y-m-d') : $subscription['end_date'];

        $stmt = $this->db->prepare("UPDATE subscriptions SET status = ?, end_date = ? WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ssi', $status, $endDate, $subscriptionId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function renew($subscriptionId)
    {
        $subscription = $this->getSubscription($subscriptionId);
        if (!$subscription) {
            return false;
        }

        $plan = $this->getPlan($subscription['plan_id']);
        if (!$plan) {
            return false;
        }

        $startDate = $subscription['end_date'];
        $endDate = date('Y-m-d', strtotime($startDate . ' +' . (int)$plan['billing_cycle_days'] . ' days'));

        $stmt = $this->db->prepare("UPDATE subscriptions SET status = 'active', start_date = ?, end_date = ? WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ssi', $startDate, $endDate, $subscriptionId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function upgrade($subscriptionId, $newPlanId)
    {
        $subscription = $this->getSubscription($subscriptionId);
        $newPlan = $this->getPlan($newPlanId);

        if (!$subscription || !$newPlan) {
            return false;
        }

        $proration = $this->calculateProration($subscription, $newPlan);
        $stmt = $this->db->prepare("UPDATE subscriptions SET plan_id = ?, status = 'active' WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ii', $newPlanId, $subscriptionId);
        $result = $stmt->execute();
        $stmt->close();

        return $result ? $proration : false;
    }

    public function calculateProration($subscription, $newPlan)
    {
        $plan = $this->getPlan($subscription['plan_id']);
        if (!$plan) {
            return 0;
        }

        $start = strtotime($subscription['start_date']);
        $end = strtotime($subscription['end_date']);
        $today = strtotime(date('Y-m-d'));

        $totalDays = max(1, (int)round(($end - $start) / 86400));
        $remainingDays = max(0, (int)round(($end - $today) / 86400));

        $remainingRatio = $remainingDays / $totalDays;
        $currentCredit = (float)$plan['price'] * $remainingRatio;
        $newCharge = (float)$newPlan['price'] * $remainingRatio;

        return round($newCharge - $currentCredit, 2);
    }

    private function updateStatus($subscriptionId, $status)
    {
        $stmt = $this->db->prepare("UPDATE subscriptions SET status = ? WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('si', $status, $subscriptionId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    private function getSubscription($subscriptionId)
    {
        $stmt = $this->db->prepare("SELECT id, customer_id, plan_id, status, start_date, end_date, auto_renew FROM subscriptions WHERE id = ?");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $subscriptionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $subscription = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $subscription;
    }

    private function getPlan($planId)
    {
        $stmt = $this->db->prepare("SELECT id, name, price, billing_cycle_days FROM subscription_plans WHERE id = ?");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $planId);
        $stmt->execute();
        $result = $stmt->get_result();
        $plan = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $plan;
    }
}
