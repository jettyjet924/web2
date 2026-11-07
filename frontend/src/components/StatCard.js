"use client";

import React from "react";

const COLOR_MAP = {
  emerald: {
    bg: "bg-emerald-500/10",
    text: "text-emerald-400",
    border: "border-emerald-500/20",
    glow: "shadow-emerald-500/5",
    gradient: "from-emerald-500/20 to-transparent",
  },
  indigo: {
    bg: "bg-indigo-500/10",
    text: "text-indigo-400",
    border: "border-indigo-500/20",
    glow: "shadow-indigo-500/5",
    gradient: "from-indigo-500/20 to-transparent",
  },
  blue: {
    bg: "bg-blue-500/10",
    text: "text-blue-400",
    border: "border-blue-500/20",
    glow: "shadow-blue-500/5",
    gradient: "from-blue-500/20 to-transparent",
  },
  purple: {
    bg: "bg-purple-500/10",
    text: "text-purple-400",
    border: "border-purple-500/20",
    glow: "shadow-purple-500/5",
    gradient: "from-purple-500/20 to-transparent",
  },
  amber: {
    bg: "bg-amber-500/10",
    text: "text-amber-400",
    border: "border-amber-500/20",
    glow: "shadow-amber-500/5",
    gradient: "from-amber-500/20 to-transparent",
  },
  rose: {
    bg: "bg-rose-500/10",
    text: "text-rose-400",
    border: "border-rose-500/20",
    glow: "shadow-rose-500/5",
    gradient: "from-rose-500/20 to-transparent",
  },
  cyan: {
    bg: "bg-cyan-500/10",
    text: "text-cyan-400",
    border: "border-cyan-500/20",
    glow: "shadow-cyan-500/5",
    gradient: "from-cyan-500/20 to-transparent",
  },
};

export default function StatCard({
  title,
  value,
  subtitle,
  icon,
  isCurrency = false,
  alert = false,
  loading = false,
  color = "indigo",
}) {
  const styles = COLOR_MAP[color] || COLOR_MAP.indigo;

  const formattedValue = () => {
    if (value === undefined || value === null) return "—";
    if (isCurrency) {
      return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
        minimumFractionDigits: 2,
      }).format(Number(value) || 0);
    }
    return new Intl.NumberFormat("en-US").format(Number(value) || 0);
  };

  return (
    <div
      className={`relative overflow-hidden rounded-2xl bg-slate-900/90 border transition-all duration-200 p-5 ${
        alert && Number(value) > 0
          ? "border-rose-500/40 bg-rose-950/20 shadow-lg shadow-rose-500/5"
          : `${styles.border} hover:border-slate-700 shadow-xl ${styles.glow}`
      }`}
    >
      {/* Subtle top accent gradient */}
      <div
        className={`absolute top-0 left-0 right-0 h-1 bg-gradient-to-r ${styles.gradient}`}
      />

      <div className="flex items-start justify-between">
        <div className="flex-1 min-w-0 pr-3">
          <p className="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">
            {title}
          </p>

          {loading ? (
            <div className="h-8 w-24 bg-slate-800 animate-pulse rounded-lg mt-1" />
          ) : (
            <div className="flex items-baseline space-x-2">
              <h3
                className={`text-2xl font-bold tracking-tight ${
                  alert && Number(value) > 0 ? "text-rose-400" : "text-white"
                }`}
              >
                {formattedValue()}
              </h3>
              {alert && Number(value) > 0 && (
                <span className="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                  Attention
                </span>
              )}
            </div>
          )}

          {subtitle && (
            <p className="text-xs text-slate-500 mt-1.5 font-medium truncate">
              {subtitle}
            </p>
          )}
        </div>

        <div
          className={`w-11 h-11 rounded-xl flex items-center justify-center shrink-0 border ${
            alert && Number(value) > 0
              ? "bg-rose-500/10 text-rose-400 border-rose-500/20"
              : `${styles.bg} ${styles.text} ${styles.border}`
          }`}
        >
          {icon}
        </div>
      </div>
    </div>
  );
}
