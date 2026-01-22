<?php

declare(strict_types=1);

namespace OrderManagement\Entities;

final class Warehouse
{
    public function __construct(
        public readonly int $id,
        public string $code,
        public string $name
    ) {
    }
}

