import { apiClient } from "@/lib/api"

export interface User {
  id: string
  name: string
  email: string
  role: string
  accessRoleId?: string
  companyId?: string | null
  status: 'active' | 'inactive' | 'suspended'
  isTwoFactorEnabled: boolean
  createdAt: string
  updatedAt: string
}

export interface CreateUserDto {
  name: string
  email: string
  password: string
  role?: string
  accessRoleId?: string
  companyId?: string
  status?: 'active' | 'inactive' | 'suspended'
}

export interface UpdateUserDto {
  name?: string
  email?: string
  password?: string
  role?: string
  accessRoleId?: string
  companyId?: string
  status?: 'active' | 'inactive' | 'suspended'
}

export const usersService = {
  async findAll() {
    const response = await apiClient("/users")
    if (!response.ok) throw new Error("Failed to fetch users")
    return response.json() as Promise<User[]>
  },

  async findOne(id: string) {
    const response = await apiClient(`/users/${id}`)
    if (!response.ok) throw new Error("Failed to fetch user")
    return response.json() as Promise<User>
  },

  async create(data: CreateUserDto) {
    const response = await apiClient("/users", {
      method: "POST",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to create user" }))
        throw new Error(error.message || "Failed to create user")
    }
    return response.json() as Promise<User>
  },

  async update(id: string, data: UpdateUserDto) {
    const response = await apiClient(`/users/${id}`, {
      method: "PATCH",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to update user" }))
        throw new Error(error.message || "Failed to update user")
    }
    return response.json() as Promise<User>
  },

  async remove(id: string) {
    const response = await apiClient(`/users/${id}`, {
      method: "DELETE",
    })
    if (!response.ok) throw new Error("Failed to delete user")
    return true
  },
}
