<?php

declare(strict_types=1);

namespace OrderManagement\Entities;

final class StockAdjustment
{
    public function __construct(
        public readonly int $productId,
        public readonly int $warehouseId,
        public readonly int $delta,
        public readonly string $reason = ''
    ) {
    }
}

