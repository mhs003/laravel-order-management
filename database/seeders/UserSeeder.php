<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('users')) {
            DB::table('users')->truncate();
        }
        Schema::enableForeignKeyConstraints();

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        User::create([
            'name' => 'Customer1',
            'email' => 'customer1@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        User::create([
            'name' => 'Customer2',
            'email' => 'customer2@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
    }
}
