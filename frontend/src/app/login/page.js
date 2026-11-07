"use client";

import React, { useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "@/context/AuthContext";
import LoginForm from "@/components/LoginForm";

export default function LoginPage() {
  const { isAuthenticated, isAdmin, loading } = useAuth();
  const router = useRouter();

  // If already logged in as admin, redirect to dashboard
  useEffect(() => {
    if (!loading && isAuthenticated && isAdmin) {
      router.push("/dashboard");
    }
  }, [loading, isAuthenticated, isAdmin, router]);

  return (
    <main className="min-h-screen bg-slate-950 flex flex-col justify-center items-center px-4 py-12 relative overflow-hidden">
      {/* Background ambient lighting */}
      <div className="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none" />
      <div className="absolute bottom-1/4 right-1/4 w-80 h-80 bg-purple-600/10 rounded-full blur-3xl pointer-events-none" />

      {/* Decorative top badge */}
      <div className="mb-6 inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-xs font-medium text-slate-400">
        <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
        <span>Laravel Sanctum REST API Connected</span>
      </div>

      {/* Centered Login Card */}
      <div className="w-full max-w-md relative z-10">
        <LoginForm />
      </div>

      {/* Footer info */}
      <footer className="mt-8 text-center text-xs text-slate-400 relative z-10">
        <p>Apex Commerce Enterprise Administration</p>
        <p className="text-[11px] text-slate-400 mt-1">
          Powered by Laravel + MongoDB + Next.js App Router
        </p>
      </footer>
    </main>
  );
}
