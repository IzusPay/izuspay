"use client"

import { createContext, useContext, useState, useEffect, type ReactNode } from "react"
import { authApi, setAuthToken, removeAuthToken, getAuthToken } from "@/lib/api"
import { useRouter } from "next/navigation"

interface User {
  id: string
  name: string
  email: string
  role?: string
  companyId?: string
  permissions?: Record<
    string,
    {
      canCreate: boolean
      canRead: boolean
      canUpdate: boolean
      canDelete: boolean
      canDetail: boolean
    }
  >
}

interface AuthContextType {
  user: User | null
  loading: boolean
  login: (email: string, password: string) => Promise<void>
  logout: () => Promise<void>
  refreshToken: () => Promise<void>
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [loading, setLoading] = useState(true)
  const router = useRouter()

  useEffect(() => {
    // Check if user is already logged in
    const token = getAuthToken()
    if (token) {
      loadUser()
    } else {
      setLoading(false)
    }
  }, [])

  async function loadUser() {
    try {
      const userData = await authApi.me()
      setUser(userData)
    } catch (error) {
      console.error("[v0] Failed to load user:", error)
      removeAuthToken()
    } finally {
      setLoading(false)
    }
  }

  async function login(email: string, password: string) {
    try {
      const response = await authApi.login(email, password)
      setAuthToken(response.access_token)
      await loadUser()
      router.push("/dashboard")
    } catch (error) {
      console.error("[v0] Login error:", error)
      throw error
    }
  }

  async function logout() {
    try {
      await authApi.logout()
    } catch (error) {
      console.error("[v0] Logout error:", error)
    } finally {
      setUser(null)
      removeAuthToken()
      router.push("/login")
    }
  }

  async function refreshToken() {
    try {
      const response = await authApi.refresh()
      setAuthToken(response.access_token)
    } catch (error) {
      console.error("[v0] Token refresh error:", error)
      throw error
    }
  }

  return <AuthContext.Provider value={{ user, loading, login, logout, refreshToken }}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (context === undefined) {
    throw new Error("useAuth must be used within an AuthProvider")
  }
  return context
}
