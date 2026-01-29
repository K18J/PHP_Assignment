<?php

declare(strict_types=1);

namespace OrderManagement\Entities;

final class Order
{
    private array $items = [];

    public function __construct(
        public ?int $id,
        public string $orderNumber,
        public string $customerName,
        public string $status = 'draft'
    ) {
    }

    public function addItem(OrderItem $item): void
    {
        $this->items[] = $item;
    }

    public function items(): array
    {
        return $this->items;
    }

    public function total(): float
    {
        return array_reduce($this->items, fn (float $carry, OrderItem $item) => $carry + $item->total(), 0.0);
    }
}