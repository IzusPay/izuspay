import { apiClient } from "@/lib/api"
import { BankAccount, PixKeyType } from "./bank-accounts.service"

export { PixKeyType }

export enum WithdrawalMethod {
  BANK_ACCOUNT = 'bank_account',
  PIX = 'pix',
}

export enum WithdrawalStatus {
  PENDING = 'pending',
  PROCESSING = 'processing',
  COMPLETED = 'completed',
  REJECTED = 'rejected',
  FAILED = 'failed',
}

export interface Withdrawal {
  id: string
  amount: number
  fee: number
  status: WithdrawalStatus
  method: WithdrawalMethod
  rejectionReason?: string
  companyId: string
  bankAccountId?: string
  bankAccount?: BankAccount
  pixKey?: string
  pixKeyType?: PixKeyType
  createdAt: string
  updatedAt: string
}

export interface RequestWithdrawalDto {
  amount: number
  method: WithdrawalMethod
  bankAccountId?: string
  pixKey?: string
  pixKeyType?: PixKeyType
}

export const withdrawalsService = {
  async findAll() {
    const response = await apiClient("/withdrawals")
    if (!response.ok) throw new Error("Failed to fetch withdrawals")
    return response.json() as Promise<Withdrawal[]>
  },

  async findOne(id: string) {
    const response = await apiClient(`/withdrawals/${id}`)
    if (!response.ok) throw new Error("Failed to fetch withdrawal")
    return response.json() as Promise<Withdrawal>
  },

  async request(data: RequestWithdrawalDto) {
    const response = await apiClient("/withdrawals", {
      method: "POST",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to request withdrawal" }))
        throw new Error(error.message || "Failed to request withdrawal")
    }
    return response.json() as Promise<Withdrawal>
  },
}
