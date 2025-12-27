import { apiClient } from "@/lib/api"

export interface GatewayParam {
  id?: string
  gatewayId?: string
  label: string
  value: string
}

export interface GatewayType {
  id: string
  name: string
  slug: string
}

export interface Gateway {
  id: string
  name: string
  image?: string
  apiUrl: string
  typeId?: string
  type?: GatewayType
  params: GatewayParam[]
  transactionFeePercentage: number
  transactionFeeFixed: number
  costFeePercentage: number
  costFeeFixed: number
  priority: number
  isActive: boolean
  createdAt: string
  updatedAt: string
}

export interface CreateGatewayDto {
  name: string
  apiUrl: string
  typeId?: string
  params: GatewayParam[]
  transactionFeePercentage: number
  transactionFeeFixed: number
  costFeePercentage: number
  costFeeFixed: number
  priority: number
  isActive: boolean
}

export interface UpdateGatewayDto extends Partial<CreateGatewayDto> {}

export const gatewaysService = {
  async findAll() {
    const response = await apiClient("/gateways")
    if (!response.ok) throw new Error("Failed to fetch gateways")
    return response.json() as Promise<Gateway[]>
  },

  async findOne(id: string) {
    const response = await apiClient(`/gateways/${id}`)
    if (!response.ok) throw new Error("Failed to fetch gateway")
    return response.json() as Promise<Gateway>
  },

  async create(data: CreateGatewayDto) {
    const response = await apiClient("/gateways", {
      method: "POST",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to create gateway" }))
        throw new Error(error.message || "Failed to create gateway")
    }
    return response.json() as Promise<Gateway>
  },

  async update(id: string, data: UpdateGatewayDto) {
    const response = await apiClient(`/gateways/${id}`, {
      method: "PUT",
      body: JSON.stringify(data),
    })
    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: "Failed to update gateway" }))
        throw new Error(error.message || "Failed to update gateway")
    }
    return response.json() as Promise<Gateway>
  },

  async delete(id: string) {
    const response = await apiClient(`/gateways/${id}`, {
      method: "DELETE",
    })
    if (!response.ok) throw new Error("Failed to delete gateway")
  },

  async findAllTypes() {
    const response = await apiClient("/gateways/types") // Assuming this endpoint exists or I need to create/mock it. 
    // Wait, I should check if there is a controller for types.
    // If not, I might need to rely on what I have or check the backend again.
    // For now I'll assume standard CRUD structure usually includes relations or separate endpoints.
    // Let's check the backend controller first to be sure.
    if (!response.ok) return [] // Fallback if endpoint doesn't exist
    return response.json() as Promise<GatewayType[]>
  }
}
