<?php

namespace App\Exceptions;

use App\Enums\OrderStatus;
use Exception;

class InvalidOrderStatusTransitionException extends Exception
{
    public static function fromTransition(OrderStatus $currentStatus, OrderStatus $newStatus)
    {
        return new static(
            "Cannot update order status from '{$currentStatus->value}' to {$newStatus->value}'",
            "Allowed order transitions are: " . implode(', ', array_map(fn($status) => $status->value, OrderStatus::getAllowedTransitions()[$currentStatus->value] ?? []))
        );
    }

    public function getStatusCode(): int
    {
        return 422; // Unprocessable Content
    }
}
