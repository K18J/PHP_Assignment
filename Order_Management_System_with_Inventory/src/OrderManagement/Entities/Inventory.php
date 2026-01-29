<?php

declare(strict_types=1);

namespace OrderManagement\Entities;

final class Inventory
{
    public function __construct(
        public readonly int $id,
        public readonly int $productId,
        public readonly int $warehouseId,
        public int $quantityOnHand,
        public int $quantityReserved,
        public int $version
    ) {
    }

    public function available(): int
    {
        return $this->quantityOnHand - $this->quantityReserved;
    }
}