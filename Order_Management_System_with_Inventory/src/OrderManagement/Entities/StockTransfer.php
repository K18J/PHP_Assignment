<?php

declare(strict_types=1);

namespace OrderManagement\Entities;

final class StockTransfer
{
    public function __construct(
        public readonly int $productId,
        public readonly int $fromWarehouseId,
        public readonly int $toWarehouseId,
        public readonly int $quantity,
        public readonly string $reference = ''
    ) {
    }
}