import { apiClient } from "@/lib/api"

export interface Address {
  id?: string
  street: string
  number: string
  complement?: string
  neighborhood: string
  city: string
  state: string
  zipCode: string
}

export interface Company {
  id: string
  name: string
  document: string
  type: 'individual' | 'company'
  phone: string
  status: 'pending' | 'pending_documents' | 'active' | 'suspended'
  balance: number
  withdrawalFee: number
  transactionFeePercentage: number
  transactionFeeFixed: number
  webhookSkipInterval?: number
  address?: Address
  createdAt: string
  updatedAt: string
}

export interface CreateCompanyDto {
  name: string
  document: string
  type: 'individual' | 'company'
  phone: string
  address: Omit<Address, "id">
  withdrawalFee?: number
  transactionFeePercentage?: number
  transactionFeeFixed?: number
  webhookSkipInterval?: number
}

export interface UpdateCompanyDto {
  name?: string
  document?: string
  type?: 'individual' | 'company'
  phone?: string
  status?: 'pending' | 'pending_documents' | 'active' | 'suspended'
  address?: Omit<Address, "id">
  withdrawalFee?: number
  transactionFeePercentage?: number
  transactionFeeFixed?: number
  webhookSkipInterval?: number
}

export const companiesService = {
  async findAll() {
    const response = await apiClient("/companies")
    if (!response.ok) throw new Error("Failed to fetch companies")
    return response.json() as Promise<Company[]>
  },

  async findOne(id: string) {
    const response = await apiClient(`/companies/${id}`)
    if (!response.ok) throw new Error("Failed to fetch company")
    return response.json() as Promise<Company>
  },

  async create(data: CreateCompanyDto) {
    const response = await apiClient("/companies", {
      method: "POST",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to create company" }))
        throw new Error(error.message || "Failed to create company")
    }
    return response.json() as Promise<Company>
  },

  async update(id: string, data: UpdateCompanyDto) {
    const response = await apiClient(`/companies/${id}`, {
      method: "PATCH",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to update company" }))
        throw new Error(error.message || "Failed to update company")
    }
    return response.json() as Promise<Company>
  },

  async remove(id: string) {
    const response = await apiClient(`/companies/${id}`, {
      method: "DELETE",
    })
    if (!response.ok) throw new Error("Failed to delete company")
    return true
  },
}
