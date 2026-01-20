<?php

namespace App\Exceptions;

use App\Enums\OrderStatus;
use Exception;

class InvalidOrderStatusTransitionException extends Exception
{
    public static function fromTransition(OrderStatus $currentStatus, OrderStatus $newStatus)
    {
        $availStatuses = implode(', ', array_map(fn($status) => $status->value, OrderStatus::getAllowedTransitions()[$currentStatus->value] ?? []));

        return new static(
            empty($availStatuses) ?
            "Status is in terminal stage right now. It cannot be updated anymore."
            :
            "Cannot update order status from '{$currentStatus->value}' to '{$newStatus->value}'. Allowed order transitions are: " . $availStatuses
        );
    }

    public function getStatusCode(): int
    {
        return 422; // Unprocessable Content
    }
}
