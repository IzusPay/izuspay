const API_BASE_URL = "http://localhost:3001"

// Get JWT token from localStorage
export function getAuthToken(): string | null {
  if (typeof window !== "undefined") {
    return localStorage.getItem("jwt_token")
  }
  return null
}

// Set JWT token in localStorage
export function setAuthToken(token: string): void {
  if (typeof window !== "undefined") {
    localStorage.setItem("jwt_token", token)
  }
}

// Remove JWT token from localStorage
export function removeAuthToken(): void {
  if (typeof window !== "undefined") {
    localStorage.removeItem("jwt_token")
  }
}

// API client with automatic JWT token injection
export async function apiClient(endpoint: string, options: RequestInit = {}): Promise<Response> {
  const jwtToken = getAuthToken()

  const headers: HeadersInit = {
    ...(options.body instanceof FormData ? {} : { "Content-Type": "application/json" }),
    ...(options.headers || {}),
  }

  // Add JWT token if available
  if (jwtToken) {
    (headers as any)["Authorization"] = `Bearer ${jwtToken}`
  }

  const response = await fetch(`${API_BASE_URL}${endpoint}`, {
    ...options,
    headers,
  })

  // If unauthorized, redirect to login
  if (response.status === 401 && typeof window !== "undefined") {
    removeAuthToken()
    window.location.href = "/login"
  }

  return response
}

// Auth API functions
export const authApi = {
  async login(email: string, password: string) {
    const response = await fetch(`${API_BASE_URL}/auth/login`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ email, password }),
    })

    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: "Login failed" }))
      throw new Error(error.message || "Login failed")
    }

    return response.json()
  },

  async me() {
    const response = await apiClient("/auth/profile", {
      method: "GET",
    })

    if (!response.ok) {
      throw new Error("Failed to get user data")
    }

    return response.json()
  },

  async refresh() {
    // Not implemented in backend yet
    return { access_token: getAuthToken() }
  },

  async logout() {
    // Client-side logout only
    removeAuthToken()
    if (typeof window !== "undefined") {
      window.location.href = "/login"
    }
  }
}
