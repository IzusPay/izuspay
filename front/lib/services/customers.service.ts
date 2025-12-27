import { apiClient } from "@/lib/api"

export interface Customer {
  id: string
  name: string
  email: string
  document: string
  phone?: string
  companyId: string
  createdAt: string
  updatedAt: string
}

export interface CreateCustomerDto {
  name: string
  email: string
  document: string
  phone?: string
  companyId: string
}

export interface UpdateCustomerDto {
  name?: string
  email?: string
  document?: string
  phone?: string
  companyId?: string
}

export const customersService = {
  async findAll() {
    const response = await apiClient("/customers")
    if (!response.ok) throw new Error("Failed to fetch customers")
    return response.json() as Promise<Customer[]>
  },

  async findOne(id: string) {
    const response = await apiClient(`/customers/${id}`)
    if (!response.ok) throw new Error("Failed to fetch customer")
    return response.json() as Promise<Customer>
  },

  async create(data: CreateCustomerDto) {
    const response = await apiClient("/customers", {
      method: "POST",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to create customer" }))
        throw new Error(error.message || "Failed to create customer")
    }
    return response.json() as Promise<Customer>
  },

  async update(id: string, data: UpdateCustomerDto) {
    const response = await apiClient(`/customers/${id}`, {
      method: "PATCH",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to update customer" }))
        throw new Error(error.message || "Failed to update customer")
    }
    return response.json() as Promise<Customer>
  },

  async remove(id: string) {
    const response = await apiClient(`/customers/${id}`, {
      method: "DELETE",
    })
    if (!response.ok) throw new Error("Failed to delete customer")
    return true
  },
}
