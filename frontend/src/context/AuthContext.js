"use client";

import React, { createContext, useContext, useState, useEffect, useCallback } from "react";
import { useRouter } from "next/navigation";
import { authApi, tokenStorage, userStorage, ensureCsrfCookie, ApiError } from "@/lib/api";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  // Load session from storage and initialize CSRF on mount
  useEffect(() => {
    // Preload CSRF cookie in background
    ensureCsrfCookie();

    const storedToken = tokenStorage.get();
    const storedUser = userStorage.get();

    if (storedToken) {
      setToken(storedToken);
      if (storedUser) {
        setUser(storedUser);
      }

      // Verify token validity with backend in background
      authApi
        .me()
        .then((res) => {
          if (res?.data) {
            setUser(res.data);
            userStorage.set(res.data);
          }
        })
        .catch((err) => {
          // If token is invalid (401), clean up
          if (err?.status === 401) {
            tokenStorage.remove();
            userStorage.remove();
            setToken(null);
            setUser(null);
          }
        })
        .finally(() => {
          setLoading(false);
        });
    } else {
      setLoading(false);
    }
  }, []);

  const login = useCallback(
    async (email, password) => {
      const res = await authApi.login(email, password);
      if (res?.data?.token && res?.data?.user) {
        const receivedToken = res.data.token;
        const receivedUser = res.data.user;

        tokenStorage.set(receivedToken);
        userStorage.set(receivedUser);

        setToken(receivedToken);
        setUser(receivedUser);

        return receivedUser;
      }
      throw new ApiError("Invalid response format from authentication server.", 500);
    },
    []
  );

  const logout = useCallback(async () => {
    try {
      await authApi.logout();
    } catch {
      // Ignore network errors on logout
    } finally {
      tokenStorage.remove();
      userStorage.remove();
      setToken(null);
      setUser(null);
      router.push("/login");
    }
  }, [router]);

  const refreshUser = useCallback(async () => {
    try {
      const res = await authApi.me();
      if (res?.data) {
        setUser(res.data);
        userStorage.set(res.data);
        return res.data;
      }
    } catch (err) {
      if (err?.status === 401) {
        await logout();
      }
      throw err;
    }
  }, [logout]);

  const isAuthenticated = Boolean(token && user);
  const isAdmin = user?.role === "admin";

  const value = {
    user,
    token,
    loading,
    isAuthenticated,
    isAdmin,
    login,
    logout,
    refreshUser,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return context;
}
