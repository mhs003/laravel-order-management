<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';


    public static function getAllowedTransitions(): array
    {
        return [
            self::PENDING->value => [self::PROCESSING, self::CANCELLED],
            self::PROCESSING->value => [self::COMPLETED, self::CANCELLED],
            self::COMPLETED->value => [], // Terminal state
            self::CANCELLED->value => [], // Terminal state
        ];


    }

    public function canTransitionTo(OrderStatus $newStatus): bool {
        if ($this === $newStatus) {
            return true;
        }

        $allowedTransitoins = self::getAllowedTransitions()[$this->value] ?? [];

        return \in_array($newStatus, $allowedTransitoins, true);
    }

    public function label():string {
        return match($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }


    public static function values():array {
        return array_column(self::cases(), 'value');
    }
}
