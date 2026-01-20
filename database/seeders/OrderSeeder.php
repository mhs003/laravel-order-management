<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('order_items')) {
            DB::table('order_items')->truncate();
        }

        if (Schema::hasTable('orders')) {
            DB::table('orders')->truncate();
        }
        Schema::enableForeignKeyConstraints();


        $customer1 = User::where('email', 'customer1@example.com')->first();
        $customer2 = User::where('email', 'customer2@example.com')->first();

        $order1 = Order::create([
            'user_id' => $customer1->id,
            'status' => OrderStatus::PENDING,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_name' => 'Wireless Mouse',
            'quantity' => 2,
            'unit_price' => 29.99,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_name' => 'Mechanical Keyboard',
            'quantity' => 1,
            'unit_price' => 89.99,
        ]);

        $order2 = Order::create([
            'user_id' => $customer1->id,
            'status' => OrderStatus::PROCESSING,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_name' => 'USB-C Hub',
            'quantity' => 1,
            'unit_price' => 49.99,
        ]);

        $order3 = Order::create([
            'user_id' => $customer1->id,
            'status' => OrderStatus::COMPLETED,
        ]);

        OrderItem::create([
            'order_id' => $order3->id,
            'product_name' => 'Monitor Stand',
            'quantity' => 1,
            'unit_price' => 39.99,
        ]);

        $order4 = Order::create([
            'user_id' => $customer2->id,
            'status' => OrderStatus::PENDING,
        ]);

        OrderItem::create([
            'order_id' => $order4->id,
            'product_name' => 'Laptop Stand',
            'quantity' => 1,
            'unit_price' => 59.99,
        ]);

        OrderItem::create([
            'order_id' => $order4->id,
            'product_name' => 'Webcam',
            'quantity' => 1,
            'unit_price' => 79.99,
        ]);

        Order::all()->each->recalculateTotal();
    }
}
