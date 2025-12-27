import { apiClient } from "@/lib/api"

export interface SystemSetting {
  key: string
  value: string
  description?: string
  updatedAt: string
}

export enum BannerType {
  FAVICON = 'favicon',
  LOGO = 'logo',
  CAROUSEL_BANNER = 'carousel_banner',
}

export interface Banner {
  id: string
  type: BannerType
  url: string
  isActive: boolean
  createdAt: string
}

export const settingsService = {
  // System Settings
  async getSettings() {
    const response = await apiClient("/system-settings")
    if (!response.ok) throw new Error("Failed to fetch system settings")
    return response.json() as Promise<SystemSetting[]>
  },

  async updateSetting(key: string, value: string, description?: string) {
    const response = await apiClient("/system-settings", {
      method: "POST",
      body: JSON.stringify({ key, value, description }),
    })
    if (!response.ok) throw new Error("Failed to update setting")
    return response.json() as Promise<SystemSetting>
  },

  // Banners
  async getBanners() {
    const response = await apiClient("/banners")
    if (!response.ok) throw new Error("Failed to fetch banners")
    return response.json() as Promise<Banner[]>
  },

  async uploadBanner(type: BannerType, file: File) {
    const formData = new FormData()
    formData.append("type", type)
    formData.append("file", file)

    const response = await apiClient("/banners", {
      method: "POST",
      body: formData,
      // Note: Do not set Content-Type header manually when using FormData
      // apiClient helper might need adjustment if it forces Content-Type: application/json
    })

    if (!response.ok) throw new Error("Failed to upload banner")
    return response.json() as Promise<Banner>
  },

  async deleteBanner(id: string) {
    const response = await apiClient(`/banners/${id}`, {
      method: "DELETE",
    })
    if (!response.ok) throw new Error("Failed to delete banner")
  },
}
