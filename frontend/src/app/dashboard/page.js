"use client";

import React, { useState, useEffect, useCallback } from "react";
import ProtectedRoute from "@/components/ProtectedRoute";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import StatCard from "@/components/StatCard";
import { adminApi, ApiError } from "@/lib/api";

export default function DashboardPage() {
  const [activeTab, setActiveTab] = useState("dashboard");
  const [mobileSidebarOpen, setMobileSidebarOpen] = useState(false);

  // Dashboard stats state
  const [dashboardData, setDashboardData] = useState(null);
  const [statsLoading, setStatsLoading] = useState(true);
  const [statsError, setStatsError] = useState(null);
  const [isRefreshing, setIsRefreshing] = useState(false);

  // Tab data states
  const [tabData, setTabData] = useState({
    products: null,
    categories: null,
    orders: null,
    customers: null,
  });
  const [tabLoading, setTabLoading] = useState(false);
  const [tabError, setTabError] = useState(null);

  // Fetch Dashboard Stats from real Laravel API
  const fetchDashboardStats = useCallback(async (isRefresh = false) => {
    if (isRefresh) {
      setIsRefreshing(true);
    } else {
      setStatsLoading(true);
    }
    setStatsError(null);

    try {
      const res = await adminApi.getDashboardStats();
      if (res?.data) {
        setDashboardData(res.data);
      }
    } catch (err) {
      if (err instanceof ApiError) {
        setStatsError(err.message);
      } else {
        setStatsError("Failed to connect to the dashboard statistics API.");
      }
    } finally {
      setStatsLoading(false);
      setIsRefreshing(false);
    }
  }, []);

  // Fetch secondary tab content on tab switch
  const fetchTabData = useCallback(async (tab) => {
    if (tab === "dashboard") return;
    setTabLoading(true);
    setTabError(null);

    try {
      if (tab === "products") {
        const res = await adminApi.getProducts();
        setTabData((prev) => ({ ...prev, products: res?.data?.data || res?.data || [] }));
      } else if (tab === "categories") {
        const res = await adminApi.getCategories();
        setTabData((prev) => ({ ...prev, categories: res?.data?.data || res?.data || [] }));
      } else if (tab === "orders") {
        const res = await adminApi.getOrders();
        setTabData((prev) => ({ ...prev, orders: res?.data?.data || res?.data || [] }));
      } else if (tab === "customers") {
        const res = await adminApi.getUsers({ role: "customer" });
        setTabData((prev) => ({ ...prev, customers: res?.data?.data || res?.data || [] }));
      }
    } catch (err) {
      setTabError(err?.message || `Failed to fetch ${tab} data.`);
    } finally {
      setTabLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchDashboardStats();
  }, [fetchDashboardStats]);

  useEffect(() => {
    fetchTabData(activeTab);
  }, [activeTab, fetchTabData]);

  const overview = dashboardData?.overview || {};
  const ordersByStatus = dashboardData?.orders_by_status || {};
  const recentOrders = dashboardData?.recent_orders || [];
  const recentAuditLogs = dashboardData?.recent_audit_logs || [];

  const tabTitles = {
    dashboard: "Admin Dashboard",
    products: "Product Catalog",
    categories: "Category Management",
    orders: "Customer Orders",
    customers: "Customer Directory",
  };

  const tabSubtitles = {
    dashboard: "Real-time overview of store performance, revenue, and metrics",
    products: "Manage items, prices, SKUs, and inventory stock levels",
    categories: "Organize taxonomy, categories, and catalog hierarchy",
    orders: "Track order fulfillment, payment status, and customer shipments",
    customers: "Registered user accounts and shopper profiles",
  };

  return (
    <ProtectedRoute requireAdmin={true}>
      <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col lg:flex-row">
        {/* Sidebar */}
        <Sidebar
          activeTab={activeTab}
          onSelectTab={(tab) => setActiveTab(tab)}
          isOpen={mobileSidebarOpen}
          onClose={() => setMobileSidebarOpen(false)}
        />

        {/* Main Workspace Area */}
        <div className="flex-1 lg:pl-64 flex flex-col min-w-0">
          {/* Topbar */}
          <Topbar
            title={tabTitles[activeTab] || "Dashboard"}
            subtitle={tabSubtitles[activeTab] || ""}
            onMenuToggle={() => setMobileSidebarOpen(!mobileSidebarOpen)}
            onRefresh={() => {
              if (activeTab === "dashboard") {
                fetchDashboardStats(true);
              } else {
                fetchTabData(activeTab);
              }
            }}
            isRefreshing={isRefreshing || tabLoading}
          />

          {/* Page Content */}
          <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-8 overflow-y-auto">
            {/* TAB: DASHBOARD OVERVIEW */}
            {activeTab === "dashboard" && (
              <>
                {/* Error Banner with Retry */}
                {statsError && (
                  <div className="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-between">
                    <div className="flex items-center space-x-3 text-rose-300 text-sm">
                      <svg className="w-5 h-5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                      </svg>
                      <div>
                        <p className="font-semibold text-rose-200">Unable to load dashboard data</p>
                        <p className="text-xs text-rose-300/80">{statsError}</p>
                      </div>
                    </div>
                    <button
                      onClick={() => fetchDashboardStats(false)}
                      className="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold transition-colors"
                    >
                      Retry Connection
                    </button>
                  </div>
                )}

                {/* KPI Metrics: The 7 Cards Specified in Requirements */}
                <section>
                  <div className="flex items-center justify-between mb-4">
                    <div>
                      <h2 className="text-sm font-bold uppercase tracking-wider text-slate-400">
                        Operational Key Performance Indicators
                      </h2>
                      <p className="text-xs text-slate-500">
                        Live aggregation directly from MongoDB via Laravel REST API
                      </p>
                    </div>
                    <span className="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                      GET /api/v1/admin/dashboard/stats
                    </span>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* 1. Total Revenue */}
                    <StatCard
                      title="Total Revenue"
                      value={overview.total_revenue}
                      isCurrency={true}
                      subtitle="Non-cancelled order totals"
                      loading={statsLoading}
                      color="emerald"
                      icon={
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      }
                    />

                    {/* 2. Total Orders */}
                    <StatCard
                      title="Total Orders"
                      value={overview.total_orders}
                      subtitle="All recorded customer checkouts"
                      loading={statsLoading}
                      color="indigo"
                      icon={
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                      }
                    />

                    {/* 3. Total Customers */}
                    <StatCard
                      title="Total Customers"
                      value={overview.total_customers}
                      subtitle="Registered shopper accounts"
                      loading={statsLoading}
                      color="blue"
                      icon={
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                      }
                    />

                    {/* 4. Total Admins */}
                    <StatCard
                      title="Total Admins"
                      value={overview.total_admins}
                      subtitle="Privileged staff operators"
                      loading={statsLoading}
                      color="purple"
                      icon={
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                      }
                    />

                    {/* 5. Total Products */}
                    <StatCard
                      title="Total Products"
                      value={overview.total_products}
                      subtitle="Active inventory items"
                      loading={statsLoading}
                      color="cyan"
                      icon={
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                      }
                    />

                    {/* 6. Total Categories */}
                    <StatCard
                      title="Total Categories"
                      value={overview.total_categories}
                      subtitle="Taxonomy classifications"
                      loading={statsLoading}
                      color="amber"
                      icon={
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                      }
                    />

                    {/* 7. Low Stock Products */}
                    <StatCard
                      title="Low Stock Products"
                      value={overview.low_stock_products}
                      subtitle="Stock <= 5 units alert"
                      alert={true}
                      loading={statsLoading}
                      color="rose"
                      icon={
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                      }
                    />

                    {/* System Status Summary Card */}
                    <div className="relative overflow-hidden rounded-2xl bg-slate-900/90 border border-slate-800 p-5 flex flex-col justify-between shadow-xl">
                      <div className="flex items-center justify-between">
                        <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">
                          System Status
                        </p>
                        <span className="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping" />
                      </div>
                      <div className="my-2">
                        <p className="text-base font-bold text-emerald-400 flex items-center space-x-1.5">
                          <span className="w-2 h-2 rounded-full bg-emerald-400" />
                          <span>Laravel API Live</span>
                        </p>
                        <p className="text-xs text-slate-400 mt-0.5">
                          MongoDB • Sanctum Bearer
                        </p>
                      </div>
                      <p className="text-[10px] text-slate-400 font-mono">
                        Base: http://localhost:8000
                      </p>
                    </div>
                  </div>
                </section>

                {/* Orders Breakdown by Status */}
                <section>
                  <h2 className="text-sm font-bold uppercase tracking-wider text-slate-400 mb-3">
                    Order Status Pipeline
                  </h2>
                  <div className="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    {[
                      { status: "Pending", key: "pending", color: "text-amber-400", bg: "bg-amber-500/10", border: "border-amber-500/20" },
                      { status: "Processing", key: "processing", color: "text-blue-400", bg: "bg-blue-500/10", border: "border-blue-500/20" },
                      { status: "Shipped", key: "shipped", color: "text-indigo-400", bg: "bg-indigo-500/10", border: "border-indigo-500/20" },
                      { status: "Delivered", key: "delivered", color: "text-emerald-400", bg: "bg-emerald-500/10", border: "border-emerald-500/20" },
                      { status: "Cancelled", key: "cancelled", color: "text-rose-400", bg: "bg-rose-500/10", border: "border-rose-500/20" },
                    ].map((item) => (
                      <div
                        key={item.key}
                        className={`rounded-xl bg-slate-900/80 border ${item.border} p-3.5 flex flex-col justify-between`}
                      >
                        <span className="text-xs font-semibold text-slate-400">{item.status}</span>
                        <div className="mt-2 flex items-baseline justify-between">
                          <span className={`text-xl font-bold ${item.color}`}>
                            {ordersByStatus[item.key] ?? 0}
                          </span>
                          <span className={`text-[10px] font-semibold px-1.5 py-0.5 rounded ${item.bg} ${item.color}`}>
                            orders
                          </span>
                        </div>
                      </div>
                    ))}
                  </div>
                </section>

                {/* 2-Column Activity: Recent Orders & Recent Audit Trail */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  {/* Recent Orders */}
                  <div className="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-xl">
                    <div className="flex items-center justify-between mb-4">
                      <div className="flex items-center space-x-2">
                        <div className="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                          </svg>
                        </div>
                        <h3 className="font-bold text-white text-sm">Recent Orders</h3>
                      </div>
                      <button
                        onClick={() => setActiveTab("orders")}
                        className="text-xs text-indigo-400 hover:text-indigo-300 font-medium"
                      >
                        View all &rarr;
                      </button>
                    </div>

                    {recentOrders.length === 0 ? (
                      <div className="text-center py-8 text-slate-500 text-xs">
                        No orders recorded yet. Place an order to see live data.
                      </div>
                    ) : (
                      <div className="divide-y divide-slate-800">
                        {recentOrders.map((order) => (
                          <div key={order._id || order.id} className="py-3 flex items-center justify-between text-xs">
                            <div>
                              <p className="font-semibold text-white font-mono">{order.order_number}</p>
                              <p className="text-slate-400 text-[11px]">{order.user?.name || "Customer"}</p>
                            </div>
                            <div className="text-right">
                              <p className="font-bold text-emerald-400 font-mono">
                                ${Number(order.total_amount || 0).toFixed(2)}
                              </p>
                              <span className="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700 capitalize">
                                {order.status}
                              </span>
                            </div>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>

                  {/* Recent Audit Logs */}
                  <div className="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-xl">
                    <div className="flex items-center justify-between mb-4">
                      <div className="flex items-center space-x-2">
                        <div className="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center">
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                          </svg>
                        </div>
                        <h3 className="font-bold text-white text-sm">System Audit Trail</h3>
                      </div>
                      <span className="text-[11px] text-slate-500">Live MongoDB records</span>
                    </div>

                    {recentAuditLogs.length === 0 ? (
                      <div className="text-center py-8 text-slate-500 text-xs">
                        No audit logs available.
                      </div>
                    ) : (
                      <div className="divide-y divide-slate-800 max-h-80 overflow-y-auto">
                        {recentAuditLogs.slice(0, 6).map((log) => (
                          <div key={log._id || log.id} className="py-2.5 flex items-start justify-between text-xs">
                            <div className="min-w-0 pr-2">
                              <p className="font-medium text-slate-200 truncate">
                                <span className="text-indigo-400 font-mono font-semibold">{log.action}</span>
                                {log.resource_type && ` on ${log.resource_type}`}
                              </p>
                              <p className="text-[11px] text-slate-500 truncate">
                                By: {log.user?.name || log.user?.email || "System"}
                              </p>
                            </div>
                            <span className="text-[10px] text-slate-500 shrink-0 font-mono">
                              {log.created_at ? new Date(log.created_at).toLocaleTimeString() : ""}
                            </span>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                </div>
              </>
            )}

            {/* TAB: PRODUCTS */}
            {activeTab === "products" && (
              <div className="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-xl">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="font-bold text-white text-base">All Products</h3>
                  <span className="text-xs text-slate-400 font-mono">
                    Endpoint: GET /api/v1/products
                  </span>
                </div>

                {tabLoading ? (
                  <div className="py-12 text-center text-slate-400 text-sm animate-pulse">
                    Loading products from Laravel API...
                  </div>
                ) : tabError ? (
                  <div className="p-4 rounded-xl bg-rose-500/10 text-rose-300 text-xs">
                    {tabError}
                  </div>
                ) : !tabData.products || tabData.products.length === 0 ? (
                  <div className="text-center py-12 text-slate-500 text-sm">
                    No products found.
                  </div>
                ) : (
                  <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                      <thead>
                        <tr className="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                          <th className="pb-3 px-3">Product Name</th>
                          <th className="pb-3 px-3">SKU</th>
                          <th className="pb-3 px-3">Price</th>
                          <th className="pb-3 px-3">Stock</th>
                          <th className="pb-3 px-3">Status</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-800/60">
                        {tabData.products.map((item) => (
                          <tr key={item._id || item.id} className="hover:bg-slate-800/30 transition-colors">
                            <td className="py-3 px-3 font-semibold text-white">{item.name}</td>
                            <td className="py-3 px-3 font-mono text-slate-400">{item.sku || "N/A"}</td>
                            <td className="py-3 px-3 font-bold text-emerald-400 font-mono">
                              ${Number(item.price || 0).toFixed(2)}
                            </td>
                            <td className="py-3 px-3">
                              <span
                                className={`inline-flex items-center px-2 py-0.5 rounded font-mono font-bold ${
                                  Number(item.stock) <= 5
                                    ? "bg-rose-500/10 text-rose-400 border border-rose-500/20"
                                    : "bg-slate-800 text-slate-300"
                                }`}
                              >
                                {item.stock} in stock
                              </span>
                            </td>
                            <td className="py-3 px-3">
                              <span className="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                Active
                              </span>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            )}

            {/* TAB: CATEGORIES */}
            {activeTab === "categories" && (
              <div className="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-xl">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="font-bold text-white text-base">Categories</h3>
                  <span className="text-xs text-slate-400 font-mono">
                    Endpoint: GET /api/v1/categories
                  </span>
                </div>

                {tabLoading ? (
                  <div className="py-12 text-center text-slate-400 text-sm animate-pulse">
                    Loading categories from Laravel API...
                  </div>
                ) : tabError ? (
                  <div className="p-4 rounded-xl bg-rose-500/10 text-rose-300 text-xs">
                    {tabError}
                  </div>
                ) : !tabData.categories || tabData.categories.length === 0 ? (
                  <div className="text-center py-12 text-slate-500 text-sm">
                    No categories found.
                  </div>
                ) : (
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {tabData.categories.map((cat) => (
                      <div
                        key={cat._id || cat.id}
                        className="p-4 rounded-xl bg-slate-800/40 border border-slate-800 hover:border-slate-700 transition-colors"
                      >
                        <div className="flex items-center justify-between mb-2">
                          <h4 className="font-bold text-white text-sm">{cat.name}</h4>
                          <span className="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-800 text-indigo-400 border border-slate-700">
                            {cat.slug || "slug"}
                          </span>
                        </div>
                        <p className="text-xs text-slate-400 line-clamp-2">
                          {cat.description || "Category description"}
                        </p>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            )}

            {/* TAB: ORDERS */}
            {activeTab === "orders" && (
              <div className="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-xl">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="font-bold text-white text-base">All Customer Orders</h3>
                  <span className="text-xs text-slate-400 font-mono">
                    Endpoint: GET /api/v1/orders
                  </span>
                </div>

                {tabLoading ? (
                  <div className="py-12 text-center text-slate-400 text-sm animate-pulse">
                    Loading orders from Laravel API...
                  </div>
                ) : tabError ? (
                  <div className="p-4 rounded-xl bg-rose-500/10 text-rose-300 text-xs">
                    {tabError}
                  </div>
                ) : !tabData.orders || tabData.orders.length === 0 ? (
                  <div className="text-center py-12 text-slate-500 text-sm">
                    No customer orders found in database.
                  </div>
                ) : (
                  <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                      <thead>
                        <tr className="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                          <th className="pb-3 px-3">Order #</th>
                          <th className="pb-3 px-3">Customer</th>
                          <th className="pb-3 px-3">Total Amount</th>
                          <th className="pb-3 px-3">Status</th>
                          <th className="pb-3 px-3">Payment</th>
                          <th className="pb-3 px-3">Created</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-800/60">
                        {tabData.orders.map((ord) => (
                          <tr key={ord._id || ord.id} className="hover:bg-slate-800/30 transition-colors">
                            <td className="py-3 px-3 font-mono font-bold text-white">{ord.order_number}</td>
                            <td className="py-3 px-3 text-slate-300">{ord.user?.name || "Customer"}</td>
                            <td className="py-3 px-3 font-bold text-emerald-400 font-mono">
                              ${Number(ord.total_amount || 0).toFixed(2)}
                            </td>
                            <td className="py-3 px-3">
                              <span className="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700 capitalize">
                                {ord.status}
                              </span>
                            </td>
                            <td className="py-3 px-3 font-mono text-slate-400 uppercase text-[10px]">
                              {ord.payment_method || "credit_card"}
                            </td>
                            <td className="py-3 px-3 text-slate-500 font-mono text-[11px]">
                              {ord.created_at ? new Date(ord.created_at).toLocaleDateString() : "—"}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            )}

            {/* TAB: CUSTOMERS */}
            {activeTab === "customers" && (
              <div className="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-xl">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="font-bold text-white text-base">Registered Shoppers</h3>
                  <span className="text-xs text-slate-400 font-mono">
                    Endpoint: GET /api/v1/users?role=customer
                  </span>
                </div>

                {tabLoading ? (
                  <div className="py-12 text-center text-slate-400 text-sm animate-pulse">
                    Loading customer accounts from Laravel API...
                  </div>
                ) : tabError ? (
                  <div className="p-4 rounded-xl bg-rose-500/10 text-rose-300 text-xs">
                    {tabError}
                  </div>
                ) : !tabData.customers || tabData.customers.length === 0 ? (
                  <div className="text-center py-12 text-slate-500 text-sm">
                    No customers registered.
                  </div>
                ) : (
                  <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                      <thead>
                        <tr className="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                          <th className="pb-3 px-3">Customer Name</th>
                          <th className="pb-3 px-3">Email Address</th>
                          <th className="pb-3 px-3">Phone</th>
                          <th className="pb-3 px-3">Role</th>
                          <th className="pb-3 px-3">Status</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-800/60">
                        {tabData.customers.map((cust) => (
                          <tr key={cust._id || cust.id} className="hover:bg-slate-800/30 transition-colors">
                            <td className="py-3 px-3 font-semibold text-white">{cust.name}</td>
                            <td className="py-3 px-3 text-slate-300 font-mono">{cust.email}</td>
                            <td className="py-3 px-3 text-slate-400">{cust.phone || "—"}</td>
                            <td className="py-3 px-3">
                              <span className="inline-block px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20 uppercase">
                                {cust.role}
                              </span>
                            </td>
                            <td className="py-3 px-3">
                              <span className="inline-flex items-center space-x-1 text-emerald-400 font-medium">
                                <span className="w-1.5 h-1.5 rounded-full bg-emerald-400" />
                                <span>Active</span>
                              </span>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            )}
          </main>
        </div>
      </div>
    </ProtectedRoute>
  );
}
