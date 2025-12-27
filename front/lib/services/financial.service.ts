import { apiClient } from "@/lib/api"

export enum ProfitSource {
  WITHDRAWAL_FEE = 'withdrawal_fee',
  TRANSACTION_FEE = 'transaction_fee',
}

export interface SystemProfit {
  id: string
  amount: number
  source: ProfitSource
  description?: string
  relatedEntityId?: string
  createdAt: string
}

export interface TotalProfit {
  total: number
}

export const financialService = {
  async findAll() {
    const response = await apiClient("/financial/profits")
    if (!response.ok) throw new Error("Failed to fetch profits")
    return response.json() as Promise<SystemProfit[]>
  },

  async getTotal() {
    const response = await apiClient("/financial/profits/total")
    if (!response.ok) throw new Error("Failed to fetch total profit")
    return response.json() as Promise<TotalProfit>
  },
}
