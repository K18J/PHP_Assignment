<?php

declare(strict_types=1);

namespace OrderManagement\Entities;

final class ReservationResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?int $reservationId = null,
        public readonly ?string $message = null
    ) {
    }
}

