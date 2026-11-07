"use client";

import React from "react";
import { useAuth } from "@/context/AuthContext";

export default function Topbar({
  title = "Dashboard",
  subtitle = "Real-time overview of store performance & metrics",
  onMenuToggle,
  onRefresh,
  isRefreshing = false,
}) {
  const { user, logout } = useAuth();

  return (
    <header className="h-16 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 sticky top-0 z-30 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
      <div className="flex items-center space-x-3">
        {/* Mobile menu hamburger button */}
        <button
          onClick={onMenuToggle}
          className="lg:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
          aria-label="Open sidebar navigation"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <div>
          <h1 className="text-lg sm:text-xl font-bold text-white tracking-tight leading-tight">
            {title}
          </h1>
          {subtitle && (
            <p className="hidden sm:block text-xs text-slate-400 font-medium">
              {subtitle}
            </p>
          )}
        </div>
      </div>

      {/* Right controls: Refresh, User Profile, Role Badge, Logout */}
      <div className="flex items-center space-x-3">
        {onRefresh && (
          <button
            onClick={onRefresh}
            disabled={isRefreshing}
            className="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all border border-transparent hover:border-slate-700 disabled:opacity-50"
            title="Refresh dashboard data from API"
            aria-label="Refresh dashboard data"
          >
            <svg
              className={`w-4 h-4 ${isRefreshing ? "animate-spin text-indigo-400" : ""}`}
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </button>
        )}

        {/* User Info & Role Badge */}
        <div className="hidden sm:flex items-center space-x-3 pl-3 border-l border-slate-800">
          <div className="text-right">
            <p className="text-xs font-semibold text-white leading-tight">
              {user?.name || "Administrator"}
            </p>
            <div className="flex items-center justify-end space-x-1.5 mt-0.5">
              <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
              <span className="text-[10px] font-mono font-bold uppercase tracking-wider text-indigo-300">
                {user?.role || "ADMIN"}
              </span>
            </div>
          </div>

          <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-300 font-bold text-xs">
            {user?.name ? user.name.slice(0, 2).toUpperCase() : "AD"}
          </div>
        </div>

        {/* Logout button in topbar */}
        <button
          onClick={() => logout()}
          className="flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-rose-500/10 hover:text-rose-300 text-slate-300 border border-slate-700 hover:border-rose-500/30 transition-all duration-150"
          title="Sign out of admin session"
        >
          <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span className="hidden md:inline">Logout</span>
        </button>
      </div>
    </header>
  );
}
