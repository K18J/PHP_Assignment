<?php

declare(strict_types=1);

namespace OrderManagement\Repositories;

use OrderManagement\Entities\Inventory;
use OrderManagement\Entities\ReservationRequest;
use OrderManagement\Entities\ReservationResult;
use OrderManagement\Entities\StockAdjustment;
use OrderManagement\Entities\StockTransfer;

interface InventoryRepositoryInterface
{
    public function findByProductAndWarehouse(int $productId, int $warehouseId): ?Inventory;

    public function reserveStock(ReservationRequest $request): ReservationResult;

    public function releaseReservation(int $reservationId): void;

    public function transferStock(StockTransfer $transfer): void;

    public function adjustStock(StockAdjustment $adjustment): void;
}