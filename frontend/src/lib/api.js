/**
 * Centralized API Client for Laravel MongoDB REST API
 * Handles Sanctum Bearer token authentication, stateful CSRF cookie management,
 * unified error responses, and endpoint mapping.
 */

const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_BASE_URL || "http://localhost:8000/api/v1";

// Base server origin (e.g. "http://localhost:8000")
const SERVER_ROOT = API_BASE_URL.replace(/\/api\/v1\/?$/, "");

const TOKEN_STORAGE_KEY = "ecommerce_admin_token";
const USER_STORAGE_KEY = "ecommerce_admin_user";

export class ApiError extends Error {
  constructor(message, status, data = null, errors = null) {
    super(message);
    this.name = "ApiError";
    this.status = status;
    this.data = data;
    this.errors = errors;
  }
}

/**
 * Storage helpers for Bearer token and User session
 */
export const tokenStorage = {
  get: () => {
    if (typeof window === "undefined") return null;
    return localStorage.getItem(TOKEN_STORAGE_KEY);
  },
  set: (token) => {
    if (typeof window === "undefined") return;
    localStorage.setItem(TOKEN_STORAGE_KEY, token);
  },
  remove: () => {
    if (typeof window === "undefined") return;
    localStorage.removeItem(TOKEN_STORAGE_KEY);
  },
};

export const userStorage = {
  get: () => {
    if (typeof window === "undefined") return null;
    const raw = localStorage.getItem(USER_STORAGE_KEY);
    try {
      return raw ? JSON.parse(raw) : null;
    } catch {
      return null;
    }
  },
  set: (user) => {
    if (typeof window === "undefined") return;
    localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(user));
  },
  remove: () => {
    if (typeof window === "undefined") return;
    localStorage.removeItem(USER_STORAGE_KEY);
  },
};

/**
 * Read cookie by name from document.cookie
 */
export function getCookie(name) {
  if (typeof document === "undefined") return null;
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) {
    const raw = parts.pop().split(";").shift();
    try {
      return decodeURIComponent(raw);
    } catch {
      return raw;
    }
  }
  return null;
}

/**
 * Initialize Sanctum CSRF cookie for stateful domain requests
 */
let csrfPromise = null;
export async function ensureCsrfCookie() {
  if (typeof window === "undefined") return;
  
  if (!csrfPromise) {
    csrfPromise = fetch(`${SERVER_ROOT}/sanctum/csrf-cookie`, {
      method: "GET",
      credentials: "include",
      headers: {
        Accept: "application/json",
      },
    })
      .then((res) => res)
      .catch((err) => {
        console.warn("CSRF cookie initialization warning:", err?.message);
      })
      .finally(() => {
        csrfPromise = null;
      });
  }
  return csrfPromise;
}

/**
 * Core API request method
 */
export async function apiRequest(endpoint, options = {}) {
  const {
    method = "GET",
    body = null,
    headers = {},
    token = null,
    requiresAuth = true,
    _retry = false,
  } = options;

  const url = `${API_BASE_URL}${endpoint.startsWith("/") ? endpoint : `/${endpoint}`}`;

  const requestHeaders = {
    Accept: "application/json",
    ...headers,
  };

  if (body && !(body instanceof FormData)) {
    requestHeaders["Content-Type"] = "application/json";
  }

  // Include Bearer token
  const authToken = token || (requiresAuth ? tokenStorage.get() : null);
  if (authToken) {
    requestHeaders["Authorization"] = `Bearer ${authToken}`;
  }

  // Include X-XSRF-TOKEN header from cookie for Laravel Sanctum
  const xsrfToken = getCookie("XSRF-TOKEN");
  if (xsrfToken) {
    requestHeaders["X-XSRF-TOKEN"] = xsrfToken;
  }

  const fetchOptions = {
    method,
    headers: requestHeaders,
    credentials: "include", // Required for Sanctum session and cookies
  };

  if (body) {
    fetchOptions.body = body instanceof FormData ? body : JSON.stringify(body);
  }

  let response;
  try {
    response = await fetch(url, fetchOptions);
  } catch (networkError) {
    throw new ApiError(
      "Unable to connect to the server. Please ensure the backend is running at " +
        API_BASE_URL,
      0,
      null
    );
  }

  // Handle 419 CSRF mismatch by re-fetching CSRF cookie and retrying once
  if (response.status === 419 && !_retry) {
    await ensureCsrfCookie();
    return apiRequest(endpoint, { ...options, _retry: true });
  }

  let json = null;
  const contentType = response.headers.get("content-type");
  if (contentType && contentType.includes("application/json")) {
    try {
      json = await response.json();
    } catch {
      json = null;
    }
  }

  if (!response.ok) {
    const status = response.status;
    let errorMessage = "An unexpected error occurred.";

    if (json?.message) {
      errorMessage = json.message;
    } else if (status === 401) {
      errorMessage = "Session expired or unauthenticated. Please log in again.";
    } else if (status === 403) {
      errorMessage = "Admin access required. You do not have permission to view this resource.";
    } else if (status === 404) {
      errorMessage = "The requested resource was not found.";
    } else if (status === 419) {
      errorMessage = "Security token mismatch. Please try submitting again.";
    } else if (status === 422) {
      errorMessage = json?.message || "Validation failed. Please check your inputs.";
    } else if (status >= 500) {
      errorMessage = "A server error occurred. Please try again later.";
    }

    throw new ApiError(errorMessage, status, json?.data, json?.errors);
  }

  return json;
}

/**
 * Authentication Endpoints
 */
export const authApi = {
  login: async (email, password) => {
    // Ensure CSRF cookie is initialized before posting credentials
    await ensureCsrfCookie();
    return apiRequest("/auth/login", {
      method: "POST",
      body: { email, password },
      requiresAuth: false,
    });
  },

  logout: async () => {
    try {
      return await apiRequest("/auth/logout", {
        method: "POST",
        requiresAuth: true,
      });
    } catch (error) {
      console.warn("Logout API call warning:", error.message);
      return { success: true };
    }
  },

  me: async () => {
    return apiRequest("/auth/me", {
      method: "GET",
      requiresAuth: true,
    });
  },

  updateProfile: async (data) => {
    return apiRequest("/auth/profile", {
      method: "PUT",
      body: data,
      requiresAuth: true,
    });
  },
};

/**
 * Admin Dashboard & Metrics Endpoints
 */
export const adminApi = {
  getDashboardStats: async () => {
    try {
      // Primary registered route in backend/routes/api.php
      return await apiRequest("/admin/dashboard/stats", {
        method: "GET",
        requiresAuth: true,
      });
    } catch (err) {
      // Graceful fallback to /admin/dashboard if route alias exists
      if (err.status === 404) {
        return await apiRequest("/admin/dashboard", {
          method: "GET",
          requiresAuth: true,
        });
      }
      throw err;
    }
  },

  getAuditLogs: async (params = {}) => {
    const query = new URLSearchParams(params).toString();
    const endpoint = `/admin/audit-logs${query ? `?${query}` : ""}`;
    return apiRequest(endpoint, {
      method: "GET",
      requiresAuth: true,
    });
  },

  getProducts: async (params = {}) => {
    const query = new URLSearchParams(params).toString();
    const endpoint = `/products${query ? `?${query}` : ""}`;
    return apiRequest(endpoint, {
      method: "GET",
      requiresAuth: false,
    });
  },

  getCategories: async (params = {}) => {
    const query = new URLSearchParams(params).toString();
    const endpoint = `/categories${query ? `?${query}` : ""}`;
    return apiRequest(endpoint, {
      method: "GET",
      requiresAuth: false,
    });
  },

  getOrders: async (params = {}) => {
    const query = new URLSearchParams(params).toString();
    const endpoint = `/orders${query ? `?${query}` : ""}`;
    return apiRequest(endpoint, {
      method: "GET",
      requiresAuth: true,
    });
  },

  getUsers: async (params = {}) => {
    const query = new URLSearchParams(params).toString();
    const endpoint = `/users${query ? `?${query}` : ""}`;
    return apiRequest(endpoint, {
      method: "GET",
      requiresAuth: true,
    });
  },
};

export default {
  apiRequest,
  ensureCsrfCookie,
  auth: authApi,
  admin: adminApi,
  tokenStorage,
  userStorage,
  API_BASE_URL,
};
