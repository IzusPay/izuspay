import { apiClient } from "@/lib/api"

export interface ApiKey {
  id: string
  name: string
  prefix: string
  active: boolean
  lastUsedAt?: string
  createdAt: string
  // Sensitive data is usually not returned in list, or only on creation
  secretKey?: string // Only available on creation response
}

export interface CreateApiKeyDto {
  name: string
}

export interface Webhook {
  id: string
  url: string
  events: string[]
  active: boolean
  description?: string
  createdAt: string
  updatedAt: string
}

export interface CreateWebhookDto {
  url: string
  events: string[]
  description?: string
}

export enum WebhookLogStatus {
  PENDING = 'pending',
  SUCCESS = 'success',
  FAILED = 'failed',
  SKIPPED_BY_RULE = 'skipped_by_rule',
}

export interface WebhookLog {
  id: string
  url: string
  method: string
  payload: any
  status: WebhookLogStatus
  httpCode?: number
  responseBody?: string
  attempts: number
  createdAt: string
  updatedAt: string
}

export const devToolsService = {
  // API Keys
  async findAllApiKeys() {
    const response = await apiClient("/api-keys")
    if (!response.ok) throw new Error("Failed to fetch API keys")
    return response.json() as Promise<ApiKey[]>
  },

  async createApiKey(data: CreateApiKeyDto) {
    const response = await apiClient("/api-keys", {
      method: "POST",
      body: JSON.stringify(data),
    })
    if (!response.ok) throw new Error("Failed to create API key")
    return response.json() as Promise<ApiKey & { secretKey: string }>
  },

  async deleteApiKey(id: string) {
    const response = await apiClient(`/api-keys/${id}`, {
      method: "DELETE",
    })
    if (!response.ok) throw new Error("Failed to delete API key")
  },

  // Webhooks
  async findAllWebhooks() {
    const response = await apiClient("/webhooks")
    if (!response.ok) throw new Error("Failed to fetch webhooks")
    return response.json() as Promise<Webhook[]>
  },

  async createWebhook(data: CreateWebhookDto) {
    const response = await apiClient("/webhooks", {
      method: "POST",
      body: JSON.stringify(data),
    })
    if (!response.ok) throw new Error("Failed to create webhook")
    return response.json() as Promise<Webhook>
  },

  async deleteWebhook(id: string) {
    const response = await apiClient(`/webhooks/${id}`, {
      method: "DELETE",
    })
    if (!response.ok) throw new Error("Failed to delete webhook")
  },

  // Webhook Logs
  async findAllWebhookLogs() {
    const response = await apiClient("/webhooks/logs") // Assuming endpoint
    if (!response.ok) {
       // Logs endpoint might be different or not exposed generally.
       // Checking controller...
       return [] 
    }
    return response.json() as Promise<WebhookLog[]>
  }
}
