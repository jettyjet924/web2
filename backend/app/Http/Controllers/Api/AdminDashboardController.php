<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/admin/dashboard/stats",
     *     summary="Executive KPI analytics & dashboard metrics (Admin only)",
     *     description="Calculates comprehensive e-commerce operational statistics including revenue, orders by status, user growth, and inventory alerts directly from MongoDB collections.",
     *     tags={"Dashboard & Audit"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics calculated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_revenue", type="number", example=12450.75),
     *                 @OA\Property(property="total_orders", type="integer", example=142),
     *                 @OA\Property(property="total_customers", type="integer", example=89),
     *                 @OA\Property(property="total_products", type="integer", example=35),
     *                 @OA\Property(property="low_stock_products", type="integer", example=4),
     *                 @OA\Property(property="orders_by_status", type="object"),
     *                 @OA\Property(property="recent_orders", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="recent_audit_logs", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden: Admin only")
     * )
     */
    public function stats()
    {
        $totalRevenue = (float) Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalOrders = Order::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $lowStockCount = Product::where('stock', '<=', 5)->count();

        // Orders breakdown by status
        $ordersByStatus = [
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        // Recent activity
        $recentOrders = Order::with('user')->orderBy('created_at', 'desc')->limit(5)->get();
        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
        $recentAuditLogs = AuditLog::with('user')->orderBy('created_at', 'desc')->limit(10)->get();

        return $this->successResponse([
            'overview' => [
                'total_revenue' => round($totalRevenue, 2),
                'total_orders' => $totalOrders,
                'total_customers' => $totalCustomers,
                'total_admins' => $totalAdmins,
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'low_stock_products' => $lowStockCount,
            ],
            'orders_by_status' => $ordersByStatus,
            'recent_orders' => $recentOrders,
            'recent_users' => $recentUsers,
            'recent_audit_logs' => $recentAuditLogs,
        ], 'Dashboard metrics retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/admin/audit-logs",
     *     summary="System audit trail records (Admin only)",
     *     description="Retrieve historical logs of changes, user log-ins, order operations, and data mutations.",
     *     tags={"Dashboard & Audit"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="action",
     *         in="query",
     *         description="Filter by action name (e.g. login, product_created, order_placed)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="resource_type",
     *         in="query",
     *         description="Filter by entity type (User, Product, Order, Category)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Entries per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Audit logs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden: Admin only")
     * )
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('resource_type')) {
            $query->where('resource_type', $request->input('resource_type'));
        }

        $perPage = (int) $request->input('per_page', 20);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->successResponse($logs, 'Audit trail retrieved successfully');
    }
}

