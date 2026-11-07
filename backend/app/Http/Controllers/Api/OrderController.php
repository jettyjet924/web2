<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/orders",
     *     summary="List orders",
     *     description="Customers see their own orders; Administrators see all orders with status filtering.",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by order status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::with('user');

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->successResponse($orders, 'Orders retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/orders",
     *     summary="Place a new order",
     *     description="Creates an order in MongoDB with embedded item snapshots and decrements product inventory.",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items","shipping_address","payment_method"},
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 type="object",
     *                 required={"product_id","quantity"},
     *                 @OA\Property(property="product_id", type="string", example="65e8a9d123456789abcdef01"),
     *                 @OA\Property(property="quantity", type="integer", example=2)
     *             )),
     *             @OA\Property(property="shipping_address", type="object",
     *                 required={"street","city","state","postal_code","country"},
     *                 @OA\Property(property="street", type="string", example="742 Evergreen Terrace"),
     *                 @OA\Property(property="city", type="string", example="Springfield"),
     *                 @OA\Property(property="state", type="string", example="OR"),
     *                 @OA\Property(property="postal_code", type="string", example="97477"),
     *                 @OA\Property(property="country", type="string", example="USA")
     *             ),
     *             @OA\Property(property="payment_method", type="string", enum={"credit_card","paypal","bank_transfer","cod"}, example="credit_card"),
     *             @OA\Property(property="notes", type="string", example="Please leave package at the front porch.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order placed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation failed or insufficient stock")
     * )
     */
    public function store(OrderRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        $orderItems = [];
        $subtotal = 0;

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                return $this->errorResponse("Product ID {$item['product_id']} does not exist.", 422);
            }

            if ($product->stock < $item['quantity']) {
                return $this->errorResponse("Insufficient stock for '{$product->name}'. Available: {$product->stock}.", 422);
            }

            $lineTotal = round($product->price * $item['quantity'], 2);
            $subtotal += $lineTotal;

            // Embedded line item snapshot
            $orderItems[] = [
                'product_id' => (string) $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->price,
                'quantity' => (int) $item['quantity'],
                'subtotal' => $lineTotal,
            ];

            // Decrement inventory
            $product->decrement('stock', $item['quantity']);
        }

        $taxAmount = round($subtotal * 0.08, 2); // 8% sales tax
        $shippingFee = $subtotal > 100 ? 0.00 : 15.00;
        $totalAmount = round($subtotal + $taxAmount + $shippingFee, 2);

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'user_id' => $user->id,
            'items' => $orderItems,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_fee' => $shippingFee,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'shipping_address' => $validated['shipping_address'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => 'unpaid',
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::record($user->id, 'order_placed', 'Order', $order->id, [
            'order_number' => $order->order_number,
            'total_amount' => $order->total_amount,
        ]);

        return $this->successResponse($order, 'Order placed successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/orders/{id}",
     *     summary="Get single order details",
     *     description="Retrieve detailed order including items and shipping info.",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized to view this order"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function show(Request $request, $id)
    {
        $order = Order::with('user')->find($id);

        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        $user = $request->user();
        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            return $this->errorResponse('Forbidden: You can only view your own orders.', 403);
        }

        return $this->successResponse($order, 'Order retrieved successfully');
    }

    /**
     * @OA\Patch(
     *     path="/orders/{id}/status",
     *     summary="Update order status (Admin only)",
     *     description="Transition order status between pending, processing, shipped, delivered, or cancelled.",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"}),
     *             @OA\Property(property="payment_status", type="string", enum={"unpaid", "paid", "refunded"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'nullable|in:unpaid,paid,refunded',
        ]);

        $order->update($validated);

        AuditLog::record($request->user()->id, 'order_status_updated', 'Order', $order->id, $validated);

        return $this->successResponse($order, 'Order status updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/orders/{id}",
     *     summary="Cancel or delete order",
     *     description="Customer can cancel a pending order; Administrator can cancel or delete any order.",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order cancelled successfully")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Cannot cancel non-pending order")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        $user = $request->user();

        if (!$user->isAdmin()) {
            if ($order->user_id !== $user->id) {
                return $this->errorResponse('Forbidden: You do not own this order.', 403);
            }

            if ($order->status !== 'pending') {
                return $this->errorResponse('Cannot cancel an order that is already ' . $order->status, 400);
            }
        }

        // Restore product inventory when order is cancelled
        if (in_array($order->status, ['pending', 'processing']) && is_array($order->items)) {
            foreach ($order->items as $item) {
                if (isset($item['product_id'], $item['quantity'])) {
                    $product = Product::find($item['product_id']);
                    $product?->increment('stock', $item['quantity']);
                }
            }
        }

        $order->update(['status' => 'cancelled']);

        AuditLog::record($user->id, 'order_cancelled', 'Order', $id);

        return $this->successResponse(null, 'Order cancelled successfully');
    }
}

