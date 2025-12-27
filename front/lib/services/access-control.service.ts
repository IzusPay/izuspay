import { apiClient } from "@/lib/api"

export interface ACLRole {
  id: string
  name: string
  description: string
  permissions?: ACLPermission[]
  createdAt: string
  updatedAt: string
}

export interface ACLModule {
  id: string
  name: string
  key: string
  description: string
  icon?: string
  route?: string
}

export interface ACLPermission {
  id: string
  moduleId: string
  module?: ACLModule
  canCreate: boolean
  canRead: boolean
  canUpdate: boolean
  canDelete: boolean
  canDetail: boolean
}

export interface CreateRoleDto {
  name: string
  description: string
}

export interface UpdatePermissionsDto {
  moduleId: string
  actions: {
    canCreate?: boolean
    canRead?: boolean
    canUpdate?: boolean
    canDelete?: boolean
    canDetail?: boolean
  }
}

export const accessControlService = {
  async findAllRoles() {
    const response = await apiClient("/access-control/roles")
    if (!response.ok) throw new Error("Failed to fetch roles")
    return response.json() as Promise<ACLRole[]>
  },

  async createRole(data: CreateRoleDto) {
    const response = await apiClient("/access-control/roles", {
      method: "POST",
      body: JSON.stringify(data),
    })
    if (!response.ok) throw new Error("Failed to create role")
    return response.json() as Promise<ACLRole>
  },

  async findAllModules() {
    const response = await apiClient("/access-control/modules")
    if (!response.ok) throw new Error("Failed to fetch modules")
    return response.json() as Promise<ACLModule[]>
  },

  async updatePermissions(roleId: string, permissions: UpdatePermissionsDto[]) {
    const response = await apiClient(`/access-control/roles/${roleId}/permissions`, {
      method: "PUT",
      body: JSON.stringify({ permissions }),
    })
    if (!response.ok) throw new Error("Failed to update permissions")
    return response.json() as Promise<ACLRole>
  },

  async updateModule(id: string, data: { icon?: string; route?: string }) {
    const response = await apiClient(`/access-control/modules/${id}`, {
      method: "PUT",
      body: JSON.stringify(data),
    })
    if (!response.ok) throw new Error("Failed to update module")
    return response.json() as Promise<ACLModule>
  },
}
