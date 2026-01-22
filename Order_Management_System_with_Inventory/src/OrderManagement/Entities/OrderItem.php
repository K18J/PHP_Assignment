<?php

declare(strict_types=1);

namespace OrderManagement\Entities;

final class OrderItem
{
    public function __construct(
        public readonly int $productId,
        public readonly int $warehouseId,
        public int $quantity,
        public float $unitPrice
    ) {
    }

    public function total(): float
    {
        return $this->quantity * $this->unitPrice;
    }
}

