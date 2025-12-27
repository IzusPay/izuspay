import { apiClient } from "@/lib/api"

export interface Product {
  id: string
  name: string
  productName: string
  description?: string
  amount: number
  active: boolean
  paymentMethods: string[]
  imageUrl?: string
  mainColor?: string
  companyId: string
  createdAt: string
  updatedAt: string
}

export interface CreateProductDto {
  name: string
  productName: string
  description?: string
  amount: number
  active?: boolean
  paymentMethods?: string[]
  imageUrl?: string
  mainColor?: string
  companyId: string
}

export interface UpdateProductDto {
  name?: string
  productName?: string
  description?: string
  amount?: number
  active?: boolean
  paymentMethods?: string[]
  imageUrl?: string
  mainColor?: string
  companyId?: string
}

export const productsService = {
  async findAll() {
    const response = await apiClient("/products")
    if (!response.ok) throw new Error("Failed to fetch products")
    return response.json() as Promise<Product[]>
  },

  async findOne(id: string) {
    const response = await apiClient(`/products/${id}`)
    if (!response.ok) throw new Error("Failed to fetch product")
    return response.json() as Promise<Product>
  },

  async create(data: CreateProductDto) {
    const response = await apiClient("/products", {
      method: "POST",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to create product" }))
        throw new Error(error.message || "Failed to create product")
    }
    return response.json() as Promise<Product>
  },

  async update(id: string, data: UpdateProductDto) {
    const response = await apiClient(`/products/${id}`, {
      method: "PATCH",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to update product" }))
        throw new Error(error.message || "Failed to update product")
    }
    return response.json() as Promise<Product>
  },

  async remove(id: string) {
    const response = await apiClient(`/products/${id}`, {
      method: "DELETE",
    })
    if (!response.ok) throw new Error("Failed to delete product")
    return true
  },
}
