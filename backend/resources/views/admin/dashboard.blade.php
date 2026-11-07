@extends('layouts.app')

@section('content')
<div class="space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-900/60 backdrop-blur-md p-6 rounded-2xl border border-slate-800">
        <div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1.5"></span> MongoDB Connected
                </span>
                <span class="text-xs text-slate-500">&bull;</span>
                <span class="text-xs text-slate-400 font-mono">DB: {{ config('database.connections.mongodb.database') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1.5">E-Commerce Management Console</h1>
            <p class="text-xs sm:text-sm text-slate-400">Real-time store metrics, inventory management, and audit telemetry</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="/api/documentation" target="_blank"
                class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition shadow-lg shadow-indigo-600/30">
                <i class="fa-solid fa-code"></i>
                <span>OpenAPI / Swagger Explorer</span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Revenue Card -->
        <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 shadow-xl relative overflow-hidden group hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Net Revenue</p>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-dollar-sign text-lg"></i>
                </div>
            </div>
            <div class="mt-4 flex items-baseline space-x-2">
                <span class="text-3xl font-black text-white tracking-tight">${{ number_format($totalRevenue, 2) }}</span>
                <span class="text-xs font-semibold text-emerald-400"><i class="fa-solid fa-arrow-trend-up mr-1"></i>Live</span>
            </div>
            <p class="text-xs text-slate-500 mt-2">Calculated from completed MongoDB orders</p>
        </div>

        <!-- Orders Card -->
        <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 shadow-xl relative overflow-hidden group hover:border-indigo-500/40 transition">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Orders Processed</p>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
                    <i class="fa-solid fa-cart-shopping text-lg"></i>
                </div>
            </div>
            <div class="mt-4 flex items-baseline space-x-2">
                <span class="text-3xl font-black text-white tracking-tight">{{ number_format($totalOrders) }}</span>
                <span class="text-xs font-semibold text-indigo-400">Transactions</span>
            </div>
            <div class="flex items-center space-x-2 mt-2 text-xs text-slate-400">
                <span class="text-amber-400">{{ $ordersByStatus['pending'] ?? 0 }} pending</span>
                <span>&bull;</span>
                <span class="text-emerald-400">{{ $ordersByStatus['delivered'] ?? 0 }} delivered</span>
            </div>
        </div>

        <!-- Catalog Products Card -->
        <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 shadow-xl relative overflow-hidden group hover:border-sky-500/40 transition">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Catalog Products</p>
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center">
                    <i class="fa-solid fa-box-open text-lg"></i>
                </div>
            </div>
            <div class="mt-4 flex items-baseline space-x-2">
                <span class="text-3xl font-black text-white tracking-tight">{{ number_format($totalProducts) }}</span>
                <span class="text-xs font-semibold text-sky-400">SKUs Active</span>
            </div>
            <p class="text-xs text-slate-500 mt-2">{{ count($lowStockProducts) }} items low stock (&le; 5 units)</p>
        </div>

        <!-- Registered Customers Card -->
        <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 shadow-xl relative overflow-hidden group hover:border-purple-500/40 transition">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Registered Users</p>
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
            </div>
            <div class="mt-4 flex items-baseline space-x-2">
                <span class="text-3xl font-black text-white tracking-tight">{{ number_format($totalCustomers) }}</span>
                <span class="text-xs font-semibold text-purple-400">Customers</span>
            </div>
            <p class="text-xs text-slate-500 mt-2">Role: customer in Mongo collection</p>
        </div>

    </div>

    <!-- Order Status Pipeline Breakdown -->
    <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800">
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-300 mb-4 flex items-center space-x-2">
            <i class="fa-solid fa-bars-progress text-indigo-400"></i>
            <span>Order Fulfillment Pipeline</span>
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                <p class="text-xs text-amber-400 font-semibold uppercase">Pending</p>
                <p class="text-2xl font-black text-white mt-1">{{ $ordersByStatus['pending'] ?? 0 }}</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                <p class="text-xs text-blue-400 font-semibold uppercase">Processing</p>
                <p class="text-2xl font-black text-white mt-1">{{ $ordersByStatus['processing'] ?? 0 }}</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                <p class="text-xs text-indigo-400 font-semibold uppercase">Shipped</p>
                <p class="text-2xl font-black text-white mt-1">{{ $ordersByStatus['shipped'] ?? 0 }}</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                <p class="text-xs text-emerald-400 font-semibold uppercase">Delivered</p>
                <p class="text-2xl font-black text-white mt-1">{{ $ordersByStatus['delivered'] ?? 0 }}</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                <p class="text-xs text-rose-400 font-semibold uppercase">Cancelled</p>
                <p class="text-2xl font-black text-white mt-1">{{ $ordersByStatus['cancelled'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Two Columns: Recent Orders and Low Stock Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Recent Orders (2 Columns) -->
        <div class="lg:col-span-2 p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-white tracking-tight">Recent Orders</h2>
                    <p class="text-xs text-slate-400">Latest customer purchases stored in MongoDB</p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 font-mono">Collection: orders</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/80 text-slate-400 uppercase font-semibold border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Order #</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 font-mono font-medium text-indigo-400">{{ $order->order_number }}</td>
                                <td class="py-3 px-4 text-slate-200">{{ $order->user->name ?? 'Guest/Anonymous' }}</td>
                                <td class="py-3 px-4 font-bold text-emerald-400">${{ number_format($order->total_amount, 2) }}</td>
                                <td class="py-3 px-4">
                                    @if($order->status === 'delivered')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-800/60">DELIVERED</span>
                                    @elseif($order->status === 'shipped')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-950 text-indigo-400 border border-indigo-800/60">SHIPPED</span>
                                    @elseif($order->status === 'processing')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-950 text-blue-400 border border-blue-800/60">PROCESSING</span>
                                    @elseif($order->status === 'cancelled')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-950 text-rose-400 border border-rose-800/60">CANCELLED</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-950 text-amber-400 border border-amber-800/60">PENDING</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-500 font-mono">{{ $order->created_at?->format('M d, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">No orders recorded yet. Run database seeders to populate demo records.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inventory Stock Alerts (1 Column) -->
        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-white tracking-tight">Stock Alerts</h2>
                    <p class="text-xs text-slate-400">Inventory items with low quantity (&le; 5 units)</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                </div>
            </div>

            <div class="space-y-2.5">
                @forelse($lowStockProducts as $product)
                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                        <div class="min-w-0 pr-2">
                            <p class="text-xs font-semibold text-white truncate">{{ $product->name }}</p>
                            <p class="text-[10px] text-slate-500 font-mono">SKU: {{ $product->sku }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-black {{ $product->stock === 0 ? 'bg-rose-950 text-rose-400 border border-rose-800' : 'bg-amber-950 text-amber-400 border border-amber-800' }}">
                            {{ $product->stock }} left
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-500">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-lg mb-2 block"></i>
                        All products have healthy inventory levels!
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- System Audit Trail -->
    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-white tracking-tight">Live System Audit Trail</h2>
                <p class="text-xs text-slate-400">Immutable chronological event log stored in MongoDB <code class="text-indigo-400">audit_logs</code> collection</p>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 font-mono">GET /api/v1/admin/audit-logs</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-2.5 px-4">Timestamp</th>
                        <th class="py-2.5 px-4">Initiator</th>
                        <th class="py-2.5 px-4">Action</th>
                        <th class="py-2.5 px-4">Entity</th>
                        <th class="py-2.5 px-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($recentAuditLogs as $log)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-2.5 px-4 text-slate-500">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="py-2.5 px-4 text-white">{{ $log->user->name ?? 'System/Guest' }}</td>
                            <td class="py-2.5 px-4 font-bold text-indigo-400">{{ $log->action }}</td>
                            <td class="py-2.5 px-4 text-slate-400">{{ $log->resource_type }} #{{ substr((string)$log->resource_id, -6) }}</td>
                            <td class="py-2.5 px-4 text-slate-500">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500 font-sans">No audit events recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

