import { apiClient } from "@/lib/api"
import { Product } from "./products.service"
import { Customer } from "./customers.service"

export enum SaleStatus {
  PENDING = "pending",
  PAID = "paid",
  FAILED = "failed",
  REFUNDED = "refunded",
  EXPIRED = "expired",
}

export interface Sale {
  id: string
  companyId: string
  productId?: string
  product?: Product
  customerId?: string
  customer?: Customer
  status: SaleStatus
  amount: number
  fee: number
  netAmount: number
  paymentMethod: string
  payerName: string
  payerEmail: string
  payerDocument: string
  payerPhone?: string
  gatewayId?: string
  transactionId?: string
  pixCode?: string
  pixQrCode?: string
  createdAt: string
  updatedAt: string
}

export interface CreateSaleDto {
  productId: string
  amount: number
  paymentMethod?: string
  payerName: string
  payerEmail: string
  payerDocument: string
  payerPhone?: string
}

export const salesService = {
  async findAll() {
    const response = await apiClient("/sales")
    if (!response.ok) throw new Error("Failed to fetch sales")
    return response.json() as Promise<Sale[]>
  },

  async findOne(id: string) {
    const response = await apiClient(`/sales/${id}`)
    if (!response.ok) throw new Error("Failed to fetch sale")
    return response.json() as Promise<Sale>
  },

  async create(data: CreateSaleDto) {
    const response = await apiClient("/sales", {
      method: "POST",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to create sale" }))
        throw new Error(error.message || "Failed to create sale")
    }
    return response.json() as Promise<Sale>
  },
}
