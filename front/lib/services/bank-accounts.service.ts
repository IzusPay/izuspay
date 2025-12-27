import { apiClient } from "@/lib/api"

export enum PixKeyType {
  CPF = 'cpf',
  CNPJ = 'cnpj',
  EMAIL = 'email',
  PHONE = 'phone',
  RANDOM = 'random',
}

export interface BankAccount {
  id: string
  bankName: string
  agency: string
  accountNumber: string
  accountDigit: string
  pixKey?: string
  pixKeyType?: PixKeyType
  companyId: string
  createdAt: string
  updatedAt: string
}

export interface CreateBankAccountDto {
  bankName: string
  agency: string
  accountNumber: string
  accountDigit: string
  pixKey?: string
  pixKeyType?: PixKeyType
}

export const bankAccountsService = {
  async findAll() {
    const response = await apiClient("/bank-accounts")
    if (!response.ok) throw new Error("Failed to fetch bank accounts")
    return response.json() as Promise<BankAccount[]>
  },

  async findOne(id: string) {
    const response = await apiClient(`/bank-accounts/${id}`)
    if (!response.ok) throw new Error("Failed to fetch bank account")
    return response.json() as Promise<BankAccount>
  },

  async create(data: CreateBankAccountDto) {
    const response = await apiClient("/bank-accounts", {
      method: "POST",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to create bank account" }))
        throw new Error(error.message || "Failed to create bank account")
    }
    return response.json() as Promise<BankAccount>
  },

  async update(id: string, data: Partial<CreateBankAccountDto>) {
    const response = await apiClient(`/bank-accounts/${id}`, {
      method: "PATCH",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to update bank account" }))
        throw new Error(error.message || "Failed to update bank account")
    }
    return response.json() as Promise<BankAccount>
  },

  async delete(id: string) {
    const response = await apiClient(`/bank-accounts/${id}`, {
      method: "DELETE",
    })
    if (!response.ok) throw new Error("Failed to delete bank account")
  },
}
