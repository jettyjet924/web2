<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class WebAdminController extends Controller
{
    /**
     * Show the main admin dashboard Blade view.
     */
    public function dashboard(Request $request)
    {
        $totalRevenue = (float) Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalOrders = Order::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalProducts = Product::count();
        $lowStockProducts = Product::where('stock', '<=', 5)->get();

        $recentOrders = Order::with('user')->orderBy('created_at', 'desc')->limit(8)->get();
        $recentUsers = User::orderBy('created_at', 'desc')->limit(6)->get();
        $recentAuditLogs = AuditLog::with('user')->orderBy('created_at', 'desc')->limit(8)->get();

        $ordersByStatus = [
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'totalCustomers',
            'totalProducts',
            'lowStockProducts',
            'recentOrders',
            'recentUsers',
            'recentAuditLogs',
            'ordersByStatus'
        ));
    }
}

