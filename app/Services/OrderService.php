<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PDO;

class OrderService
{
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'user_id' => $data['user_id'],
                'status' => OrderStatus::PENDING
            ]);

            foreach ($data['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            $order->refresh();

            ilog('Order Created', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'total_amount' => $order->total_amount,
            ]);

            return $order;
        });
    }

    public function updateOrderStatus(Order $order, OrderStatus $status): Order
    {
        return DB::transaction(function () use ($order, $status) {
            // check if transition is allowed
            if (!$order->status->canTransitionTo($status)) {
                throw InvalidOrderStatusTransitionException::fromTransition($order->status, $status);
            }

            $oldStatus = $order->status;
            $order->status = $status;
            $order->save();

            ilog('Order status updated', [
                'order_id' => $order->id,
                'old_status' => $oldStatus->value,
                'new_status' => $status->value,
            ]);

            return $order;
        });
    }

    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            if (isset($data['status'])) {
                $newStatus = OrderStatus::from($data['status']);

                if (!$order->status->canTransitionTo($newStatus)) {
                    throw InvalidOrderStatusTransitionException::fromTransition(
                        $order->status,
                        $newStatus
                    );
                }

                $order->status = $newStatus;
            }

            if (isset($data['items'])) {
                $order->items()->delete();

                foreach ($data['items'] as $itemData) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_name' => $itemData['product_name'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                    ]);
                }
            }

            $order->save();
            $order->refresh();

            ilog('Order updated', [
                'order_id' => $order->id,
            ]);

            return $order;
        });
    }


    public function deleteOrder(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            $deleted = $order->delete();

            if ($deleted) {
                ilog('Order deleted', [
                    'order_id' => $order->id,
                ]);
            }

            return $deleted;
        });
    }

    public function getUserOrders(User $user, array $filters = [])
    {
        $query = Order::forUser($user)->withCommonRelations()->latest();

        if (isset($filters['status'])) {
            $query->status($filters['status']);
        }

        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->get();
    }

    public function getAllOrders(array $filters = [])
    {
        $query = Order::withCommonRelations()->latest();

        if (isset($filters['status'])) {
            $query->status($filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }

        return $query->get();
    }

}
