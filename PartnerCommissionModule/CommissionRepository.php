<?php
namespace Repository;

use Shared\Core\Database;

class CommissionRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getDbObject();
    }

    public function calculatePartnerCommission($orderId, $partnerId)
    {
        $order = $this->getOrder($orderId);
        if (empty($order['items'])) {
            return 0;
        }

        $rules = $this->getCommissionRules($partnerId);
        if (empty($rules)) {
            return 0;
        }

        $commission = 0;
        foreach ($order['items'] as $item) {
            $rule = $this->findApplicableRule($rules, $item);
            if ($rule) {
                $commission += $item['amount'] * ($rule['rate'] / 100);
            }
        }

        return round($commission, 2);
    }

    private function getOrder($orderId)
    {
        $query = "SELECT oi.id, oi.amount, pg.typeid AS product_category_id, pt.name AS product_category_name
            FROM tblorderitems AS oi
            LEFT JOIN tblproductgroup_new AS pgn ON pgn.id = oi.planid
            LEFT JOIN tblproductgroup AS pg ON pg.id = pgn.groupid
            LEFT JOIN tblproducttype AS pt ON pt.id = pg.typeid
            WHERE oi.orderid = ?";

        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            throw new \Exception("getOrder Query preparation failed.");
        }

        $stmt->bind_param("i", $orderId);

        if (!$stmt->execute()) {
            throw new \Exception("getOrder Query execution failed.");
        }

        $result = $stmt->get_result();
        if (!$result) {
            throw new \Exception("getOrder Result fetching failed.");
        }

        $items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($items as $index => $item) {
            $items[$index]['amount'] = $this->normalizeAmount($item['amount']);
        }

        return [
            'id' => $orderId,
            'items' => $items
        ];
    }

    private function getCommissionRules($partnerId)
    {
        $query = "SELECT id, partner_id, product_category, rate, min_amount, max_amount
            FROM commission_rules
            WHERE partner_id = ?
            ORDER BY min_amount ASC";

        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            throw new \Exception("getCommissionRules Query preparation failed.");
        }

        $stmt->bind_param("i", $partnerId);

        if (!$stmt->execute()) {
            throw new \Exception("getCommissionRules Query execution failed.");
        }

        $result = $stmt->get_result();
        if (!$result) {
            throw new \Exception("getCommissionRules Result fetching failed.");
        }

        $rules = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rules;
    }

    private function findApplicableRule($rules, $item)
    {
        $amount = $item['amount'] ?? 0;
        $categoryId = (string)($item['product_category_id'] ?? '');
        $categoryName = (string)($item['product_category_name'] ?? '');

        foreach ($rules as $rule) {
            $ruleCategory = (string)($rule['product_category'] ?? '');

            if ($ruleCategory !== '' && $ruleCategory !== $categoryId && strcasecmp($ruleCategory, $categoryName) !== 0) {
                continue;
            }

            $min = (float)($rule['min_amount'] ?? 0);
            $max = (float)($rule['max_amount'] ?? 0);

            if ($amount < $min || $amount > $max) {
                continue;
            }

            return $rule;
        }

        return null;
    }

    private function normalizeAmount($amount)
    {
        if ($amount === null) {
            return 0;
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', (string)$amount);
        return (float)$normalized;
    }
}
