"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { ArrowLeft, Loader2, Save } from "lucide-react"
import Link from "next/link"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Checkbox } from "@/components/ui/checkbox"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { useToast } from "@/components/ui/use-toast"
import {
  accessControlService,
  ACLRole,
  ACLModule,
  UpdatePermissionsDto,
} from "@/lib/services/access-control.service"
import { useAuth } from "@/contexts/auth-context"

interface PermissionState {
  [moduleId: string]: {
    canCreate: boolean
    canRead: boolean
    canUpdate: boolean
    canDelete: boolean
    canDetail: boolean
  }
}

export default function RolePermissionsPage() {
  const params = useParams()
  const router = useRouter()
  const { toast } = useToast()
  const { user } = useAuth()
  
  const [role, setRole] = useState<ACLRole | null>(null)
  const [modules, setModules] = useState<ACLModule[]>([])
  const [permissions, setPermissions] = useState<PermissionState>({})
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)

  // Verify Update Permission (needed to edit permissions)
  if (!user?.permissions?.["access_control"]?.canUpdate) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-red-600">Acesso Negado</h1>
          <p className="text-gray-600">Você não tem permissão para editar perfis.</p>
        </div>
      </div>
    )
  }

  useEffect(() => {
    async function loadData() {
      try {
        if (typeof params.id !== "string") return
        
        // Parallel fetch
        const [rolesData, modulesData] = await Promise.all([
          accessControlService.findAllRoles(),
          accessControlService.findAllModules(),
        ])
        
        const currentRole = rolesData.find((r) => r.id === params.id)
        if (!currentRole) {
          toast({
            variant: "destructive",
            title: "Erro",
            description: "Perfil não encontrado.",
          })
          router.push("/settings/acl")
          return
        }
        
        setRole(currentRole)
        setModules(modulesData)
        
        // Initialize permissions state
        const initialPerms: PermissionState = {}
        
        // First set all to false
        modulesData.forEach(m => {
          initialPerms[m.id] = {
            canCreate: false,
            canRead: false,
            canUpdate: false,
            canDelete: false,
            canDetail: false,
          }
        })
        
        // Then override with existing permissions
        if (currentRole.permissions) {
          currentRole.permissions.forEach(p => {
            if (initialPerms[p.moduleId]) {
              initialPerms[p.moduleId] = {
                canCreate: p.canCreate,
                canRead: p.canRead,
                canUpdate: p.canUpdate,
                canDelete: p.canDelete,
                canDetail: p.canDetail,
              }
            }
          })
        }
        
        setPermissions(initialPerms)
      } catch (error) {
        toast({
          variant: "destructive",
          title: "Erro ao carregar",
          description: "Não foi possível carregar os dados.",
        })
      } finally {
        setLoading(false)
      }
    }

    loadData()
  }, [params.id, router, toast])

  const handleCheckChange = (moduleId: string, action: keyof PermissionState[string], checked: boolean) => {
    setPermissions(prev => ({
      ...prev,
      [moduleId]: {
        ...prev[moduleId],
        [action]: checked
      }
    }))
  }

  const handleSave = async () => {
    if (!role) return
    
    try {
      setSaving(true)
      
      const updates: UpdatePermissionsDto[] = Object.entries(permissions).map(([moduleId, actions]) => ({
        moduleId,
        actions
      }))
      
      await accessControlService.updatePermissions(role.id, updates)
      
      toast({
        title: "Permissões salvas",
        description: "As permissões foram atualizadas com sucesso.",
      })
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao salvar",
        description: "Não foi possível salvar as alterações.",
      })
    } finally {
      setSaving(false)
    }
  }

  if (loading) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
      </div>
    )
  }

  if (!role) return null

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon" asChild>
          <Link href="/acl">
            <ArrowLeft className="h-4 w-4" />
          </Link>
        </Button>
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Permissões: {role.name}</h1>
          <p className="text-muted-foreground">
            {role.description}
          </p>
        </div>
        <div className="ml-auto">
          <Button onClick={handleSave} disabled={saving}>
            {saving && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
            {!saving && <Save className="mr-2 h-4 w-4" />}
            Salvar Alterações
          </Button>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Matriz de Permissões</CardTitle>
          <CardDescription>
            Defina o que este perfil pode fazer em cada módulo do sistema.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead className="w-[300px]">Módulo</TableHead>
                <TableHead className="text-center">Ler</TableHead>
                <TableHead className="text-center">Detalhar</TableHead>
                <TableHead className="text-center">Criar</TableHead>
                <TableHead className="text-center">Editar</TableHead>
                <TableHead className="text-center">Excluir</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {modules.map((module) => (
                <TableRow key={module.id}>
                  <TableCell className="font-medium">
                    <div>{module.name}</div>
                    <div className="text-xs text-muted-foreground">{module.description}</div>
                  </TableCell>
                  <TableCell className="text-center">
                    <div className="flex justify-center">
                      <Checkbox 
                        checked={permissions[module.id]?.canRead}
                        onCheckedChange={(c) => handleCheckChange(module.id, 'canRead', c as boolean)}
                      />
                    </div>
                  </TableCell>
                  <TableCell className="text-center">
                    <div className="flex justify-center">
                      <Checkbox 
                        checked={permissions[module.id]?.canDetail}
                        onCheckedChange={(c) => handleCheckChange(module.id, 'canDetail', c as boolean)}
                      />
                    </div>
                  </TableCell>
                  <TableCell className="text-center">
                    <div className="flex justify-center">
                      <Checkbox 
                        checked={permissions[module.id]?.canCreate}
                        onCheckedChange={(c) => handleCheckChange(module.id, 'canCreate', c as boolean)}
                      />
                    </div>
                  </TableCell>
                  <TableCell className="text-center">
                    <div className="flex justify-center">
                      <Checkbox 
                        checked={permissions[module.id]?.canUpdate}
                        onCheckedChange={(c) => handleCheckChange(module.id, 'canUpdate', c as boolean)}
                      />
                    </div>
                  </TableCell>
                  <TableCell className="text-center">
                    <div className="flex justify-center">
                      <Checkbox 
                        checked={permissions[module.id]?.canDelete}
                        onCheckedChange={(c) => handleCheckChange(module.id, 'canDelete', c as boolean)}
                      />
                    </div>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  )
}
