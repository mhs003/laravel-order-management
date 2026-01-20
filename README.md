# order management system

A production-ready Order Management module demonstrating Laravel best practices.

## assessment overview

- Role-based access control (Admin, Manager, Customer)
- Policy-based authorization
- Service layer architecture
- Database transactions
- Custom domain exceptions
- Form request validation
- Eloquent relationships and scopes

## quick start

### prerequisites

- PHP 8.1 or higher
- Composer
- MySQL/PostgreSQL
- Laravel 10.x

### installation

```bash
# Clone the repository
git clone https://github.com/mhs003/laravel-order-management
cd laravel-order-management

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=order_management
DB_USERNAME=root
DB_PASSWORD=

# Run migrations
php artisan migrate

# Seed test data
php artisan db:seed

# Start the server
php artisan serve
```

## test users

After seeding, you can use these credentials:

| Role      | Email                  | Password |
|-----------|------------------------|----------|
| Admin     | admin@example.com      | password |
| Manager   | manager@example.com    | password |
| Customer1 | customer1@example.com  | password |
| Customer2 | customer2@example.com  | password |

## project structure

```
app/
├── Enums/
│   └── OrderStatus.php                 # Order status enum with transition logic
├── Exceptions/
│   └── InvalidOrderStatusTransitionException.php
├── Http/
│   ├── Controllers/
│   │   └── OrderController.php         # Thin controller (delegates to service)
│   │   └── AuthController.php          # Thin controller for user authentication (login)
│   └── Requests/
│       ├── StoreOrderRequest.php       # Validation for creating orders
│       ├── UpdateOrderRequest.php      # Validation for updating orders
│       └── LoginRequest.php            # Validation for login request
├── Models/
│   ├── Order.php                       # Order model with relationships
│   ├── OrderItem.php                   # OrderItem model
│   └── User.php                        # Extended User model
├── Policies/
│   └── OrderPolicy.php                 # Authorization rules
└── Services/
    ├── OrderService.php                # Business logic layer for order management
    └── AuthService.php                 # Business logic layer for authentication (login)

database/
├── migrations/
│   ├── xxxx_create_orders_table.php
│   └── xxxx_create_order_items_table.php
└── seeders/
    ├── UserSeeder.php                  # Database seeder for users
    ├── OrderSeeder.php                 # Database seeder for order
    └── DatabaseSeeder.php              # ...

routes/
└── api.php                             # API routes

part-a.txt                              # Conceptual answers
part-d.txt                              # Engineering judgment answers
```

## authorization matrix

| Action         | Admin | Manager | Customer        |
|----------------|-------|---------|-----------------|
| View All       | ✅    | ✅      | ❌ (own only)   |
| View Single    | ✅    | ✅      | ✅ (own only)   |
| Create         | ✅    | ❌      | ✅              |
| Update Status  | ✅    | ✅      | ❌              |
| Update Items   | ✅    | ❌      | ❌              |
| Delete         | ✅    | ❌      | ❌              |

## api endpoints

### login
```http
POST /api/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "access_token": "...",
  "token_type": "Bearer",
  "user": {
    "id": 12,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin",
    "email_verified_at": null,
    "created_at": "2026-01-20T16:29:56.000000Z",
    "updated_at": "2026-01-20T16:29:56.000000Z"
  }
}
```

### list orders
```http
GET /api/orders
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user": {...},
      "status": "pending",
      "total_amount": "149.97",
      "items_count": 2,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### create order
```http
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "items": [
    {
      "product_name": "Wireless Mouse",
      "product_sku": "MOUSE-001",
      "quantity": 2,
      "unit_price": 29.99
    }
  ]
}
```

### view order
```http
GET /api/orders/{id}
Authorization: Bearer {token}
```

### update order status
```http
PUT /api/orders/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "processing"
}
```

### delete order
```http
DELETE /api/orders/{id}
Authorization: Bearer {token}
```


_... See [`http_files/login.http`](/http_files/login.http) and [`http_files/order.http`](/http_files/order.http)_

## key features

### 1. status transition rules

Valid transitions:
- `pending` -> `processing` or `cancelled`
- `processing` -> `completed` or `cancelled`
- `completed` -> (terminal)
- `cancelled` -> (terminal)

Invalid transitions throw `InvalidOrderStatusTransitionException`.

### 2. automatic total calculation

Order totals are automatically calculated from order items:
```php
$order = Order::create(['user_id' => 1]);

OrderItem::create([
    'order_id' => $order->id,
    'product_name' => 'Item 1',
    'quantity' => 2,
    'unit_price' => 10.00
]);

$order->refresh();
echo $order->total_amount; // 20.00 (automatically calculated)
```

### 3. database transactions

All modifications use transactions for data integrity:
```php
DB::transaction(function () {
    // Create order
    // Create items
    // Calculate total
    // All or nothing
});
```

### 4. query scopes

Reusable query scopes for common filters:
```php
// Get pending orders for a user
Order::forUser($user)->status(OrderStatus::PENDING)->get();

// Get recent orders with relationships
Order::recent()->withCommonRelations()->get();
```

### 5. policy-based authorization

No role checks in controllers:
```php
// Bad
if ($user->role === 'admin') {
    // ...
}

// Good
$this->authorize('update', $order);
```

## testing

### manual testing with cURL

**Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

Save the access token in a variable:
```bash
ADMIN_ACCESS_TOKEN="1|SAbGhtwYmoEEm4fUYrGLxB28pfNkUI7umvokS0sTd6a852e5"
# similar for other roles
MANAGER_ACCESS_TOKEN="8|zBysJQ1wQOxjNEjssLRqBFmhbrsgqSJq87qVuuqTd730ac0b"
CUSTOMER_ACCESS_TOKEN="9|Uz7IVcegeOHnUqkwHxalA7S8mEFiC5DAw5jyIE7t205b03bc"
```

**Admin creates order for customer:**
```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer $ADMIN_ACCESS_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 3,
    "items": [
      {
        "product_name": "Laptop",
        "quantity": 1,
        "unit_price": 999.99
      }
    ]
  }'
```

**Manager updates order status:**
```bash
curl -X PUT http://localhost:8000/api/orders/1 \
  -H "Authorization: Bearer $MANAGER_ACCESS_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"status": "processing"}'
```

**Customer views their orders:**
```bash
curl -X GET http://localhost:8000/api/orders \
  -H "Authorization: Bearer $CUSTOMER_ACCESS_TOKEN"
```

---

**Assessment Parts:**
- Part A: Conceptual answers in [`part-a.txt`](/part-a.txt)
- Part B: Complete implementation (this repository)
- Part C: Debugging analysis in [`part-c.md`](/part-c.md)
- Part D: Engineering judgment in [`part-d.txt`](/part-d.txt)
