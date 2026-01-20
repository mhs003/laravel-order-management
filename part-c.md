# Part-C : Debugging & Refactoring

### 1. Identify at least four issues (security, architecture, logic).

**Given code:**

```php
class OrderController extends Controller {
    public function update(Request $request, $id) {
        $order = Order::find($id);
        if ($request->status) {
            $order->status = $request->status;
        }
        $order->save();
        return response()->json($order);
    }
}
```

**Indentified Issues:**

1. **No Authorization** - Any authenticated user can update any order (unless authorization is checked in any middleware)

2. **Mass Assignment Vulnerability** - Directly assingning `$request->status` without validation. Attackers could send additional fields to modify protected attributes.

3. **No Form Request Validation** - Validation logivc missing entirely.

4. **No null check** - `Order::find($id)` might return null if no order exists against the id. It will throw exception in such case.

5. **No status transaction validation** - Doesn't check if status transaction is allowed. Attacker might send **pending** even when static is already **completed**, and it will just update the status.

6. **Business logic in controller** - Controllers should be thin, and any business logics should be in a service layer. (good practice)

7. **No database transaction** - If `save()` fails, data could be inconsistent. No rollback mechanism applied.

8. **No error handling** - Client gets ugly `500` on error. No meaningful error messages.

---

### 2. Refactoring

**Refactoring strategy:**

1. Implement authorization
    - Use Laravel Policies
    - Check user permissions before any update

2. Add form request validation
    - Use a validation class for the request
    - Ensure only allowed fields are updated
    - Prevent mass assignment vulnerability.

3. Use Route model binding
    - Replace `find($id)` with automatic injection
    - Automatic `404` error

4. Extract business logic to service
    - Instead of writing business logic inside controller, move them to a service class _(eg: `OrderService`)_
    - Implement database transaction handling
    - Validate business rules (status transitions)

5. Add proper error handing
    - Catch specific exceptions
    - Add proper error messages and http response codes on demand

6. Implement API resource
    - Transform response data
    - Control what API should expose.

**Improved code:**

```php
class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function update(UpdateOrderRequest $request, Order $order)
    {
        try {
            $newStatus = OrderStatus::from($request->validated('status'));
            $order = $this->orderService->updateOrderStatus($order, $newStatus);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => new OrderResource($order),
            ], 200);
        } catch(InvalidOrderStatusTransitionException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order. Please try again.',
            ], 500);
        }
    }
}
```


**Supporting Code - API Resource**

```php
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'total_amount' => number_format($this->total_amount, 2),
            'items_count' => $this->items->count(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
```
