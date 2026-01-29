<?php

declare(strict_types=1);

namespace OrderManagement\Entities;

use DateTimeImmutable;

final class ReservationRequest
{
    public function __construct(
        public readonly int $productId,
        public readonly int $warehouseId,
        public readonly int $quantity,
        public readonly ?int $orderId = null,
        public readonly ?DateTimeImmutable $expiresAt = null
    ) {
    }
}