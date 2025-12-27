"use client"

import { useEffect, useState } from "react"
import Link from "next/link"
import { Plus, Settings2, Pencil } from "lucide-react"
import { Button } from "@/components/ui/button"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { useToast } from "@/components/ui/use-toast"
import { useAuth } from "@/contexts/auth-context"
import { accessControlService, ACLRole, ACLModule } from "@/lib/services/access-control.service"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"

export default function ACLPage() {
  const [roles, setRoles] = useState<ACLRole[]>([])
  const [modules, setModules] = useState<ACLModule[]>([])
  const [loading, setLoading] = useState(true)
  const [newRoleName, setNewRoleName] = useState("")
  const [newRoleDesc, setNewRoleDesc] = useState("")
  const [isDialogOpen, setIsDialogOpen] = useState(false)
  
  // Module Editing
  const [editingModule, setEditingModule] = useState<ACLModule | null>(null)
  const [isModuleDialogOpen, setIsModuleDialogOpen] = useState(false)
  const [moduleForm, setModuleForm] = useState({ icon: "", route: "" })

  const { toast } = useToast()
  const { user } = useAuth()

  async function loadRoles() {
    try {
      setLoading(true)
      const data = await accessControlService.findAllRoles()
      setRoles(data)
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao carregar perfis",
        description: "Não foi possível obter a lista de perfis de acesso.",
      })
    } finally {
      setLoading(false)
    }
  }

  async function loadModules() {
    try {
      const data = await accessControlService.findAllModules()
      setModules(data)
    } catch (error) {
      console.error(error)
      toast({
        variant: "destructive",
        title: "Erro ao carregar módulos",
        description: "Não foi possível obter a lista de módulos.",
      })
    }
  }

  useEffect(() => {
    loadRoles()
    loadModules()
  }, [])

  async function handleCreateRole() {
    try {
      if (!newRoleName) return
      
      await accessControlService.createRole({
        name: newRoleName,
        description: newRoleDesc,
      })
      
      toast({
        title: "Perfil criado",
        description: "O novo perfil de acesso foi criado com sucesso.",
      })
      
      setIsDialogOpen(false)
      setNewRoleName("")
      setNewRoleDesc("")
      loadRoles()
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao criar",
        description: "Não foi possível criar o perfil.",
      })
    }
  }

  function handleEditModule(module: ACLModule) {
    setEditingModule(module)
    setModuleForm({
      icon: module.icon || "",
      route: module.route || "",
    })
    setIsModuleDialogOpen(true)
  }

  async function handleUpdateModule() {
    if (!editingModule) return

    try {
      await accessControlService.updateModule(editingModule.id, moduleForm)
      
      toast({
        title: "Módulo atualizado",
        description: "As configurações do módulo foram salvas.",
      })
      
      setIsModuleDialogOpen(false)
      setEditingModule(null)
      loadModules()
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao atualizar",
        description: "Não foi possível atualizar o módulo.",
      })
    }
  }

  // Verify Read Permission
  if (user?.role !== "admin" && !user?.permissions?.["access_control"]?.canRead) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-red-600">Acesso Negado</h1>
          <p className="text-gray-600">Você não tem permissão para acessar este módulo.</p>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Controle de Acesso</h1>
          <p className="text-muted-foreground">
            Gerencie os perfis e módulos do sistema.
          </p>
        </div>
      </div>

      <Tabs defaultValue="roles" className="space-y-4">
        <TabsList>
          <TabsTrigger value="roles">Perfis de Acesso</TabsTrigger>
          <TabsTrigger value="modules">Módulos do Sistema</TabsTrigger>
        </TabsList>

        <TabsContent value="roles" className="space-y-4">
          <div className="flex justify-end">
            {user?.permissions?.["access_control"]?.canCreate && (
              <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
                <DialogTrigger asChild>
                  <Button>
                    <Plus className="mr-2 h-4 w-4" />
                    Nova Função
                  </Button>
                </DialogTrigger>
                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>Criar Novo Perfil</DialogTitle>
                    <DialogDescription>
                      Defina o nome e descrição para o novo perfil de acesso.
                    </DialogDescription>
                  </DialogHeader>
                  <div className="grid gap-4 py-4">
                    <div className="grid grid-cols-4 items-center gap-4">
                      <Label htmlFor="name" className="text-right">
                        Nome
                      </Label>
                      <Input
                        id="name"
                        value={newRoleName}
                        onChange={(e) => setNewRoleName(e.target.value)}
                        className="col-span-3"
                        placeholder="Ex: Gerente Financeiro"
                      />
                    </div>
                    <div className="grid grid-cols-4 items-center gap-4">
                      <Label htmlFor="description" className="text-right">
                        Descrição
                      </Label>
                      <Input
                        id="description"
                        value={newRoleDesc}
                        onChange={(e) => setNewRoleDesc(e.target.value)}
                        className="col-span-3"
                      />
                    </div>
                  </div>
                  <DialogFooter>
                    <Button variant="outline" onClick={() => setIsDialogOpen(false)}>
                      Cancelar
                    </Button>
                    <Button onClick={handleCreateRole}>Criar</Button>
                  </DialogFooter>
                </DialogContent>
              </Dialog>
            )}
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Perfis de Acesso</CardTitle>
              <CardDescription>
                Lista de perfis disponíveis no sistema.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Nome</TableHead>
                    <TableHead>Descrição</TableHead>
                    <TableHead className="text-right">Ações</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {loading ? (
                    <TableRow>
                      <TableCell colSpan={3} className="text-center py-8">
                        Carregando...
                      </TableCell>
                    </TableRow>
                  ) : roles.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={3} className="text-center py-8">
                        Nenhum perfil encontrado.
                      </TableCell>
                    </TableRow>
                  ) : (
                    roles.map((role) => (
                      <TableRow key={role.id}>
                        <TableCell className="font-medium">{role.name}</TableCell>
                        <TableCell>{role.description}</TableCell>
                        <TableCell className="text-right">
                          {user?.permissions?.["access_control"]?.canUpdate && (
                            <Button variant="outline" size="sm" asChild>
                              <Link href={`/acl/${role.id}`}>
                                <Settings2 className="mr-2 h-4 w-4" />
                                Permissões
                              </Link>
                            </Button>
                          )}
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="modules">
          <Card>
            <CardHeader>
              <CardTitle>Módulos do Sistema</CardTitle>
              <CardDescription>
                Configure os ícones e rotas dos módulos para o menu lateral.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Nome</TableHead>
                    <TableHead>Chave (Key)</TableHead>
                    <TableHead>Rota</TableHead>
                    <TableHead>Ícone (Lucide)</TableHead>
                    <TableHead className="text-right">Ações</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {modules.map((module) => (
                    <TableRow key={module.id}>
                      <TableCell className="font-medium">{module.name}</TableCell>
                      <TableCell className="font-mono text-xs">{module.key}</TableCell>
                      <TableCell className="font-mono text-xs">{module.route || "-"}</TableCell>
                      <TableCell>{module.icon || "-"}</TableCell>
                      <TableCell className="text-right">
                        {user?.role === "admin" && (
                          <Button 
                            variant="ghost" 
                            size="sm" 
                            onClick={() => handleEditModule(module)}
                          >
                            <Pencil className="h-4 w-4" />
                          </Button>
                        )}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>

          <Dialog open={isModuleDialogOpen} onOpenChange={setIsModuleDialogOpen}>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Editar Módulo</DialogTitle>
                <DialogDescription>
                  Configure a rota e o ícone para o módulo {editingModule?.name}.
                </DialogDescription>
              </DialogHeader>
              <div className="grid gap-4 py-4">
                <div className="grid grid-cols-4 items-center gap-4">
                  <Label htmlFor="route" className="text-right">
                    Rota
                  </Label>
                  <Input
                    id="route"
                    value={moduleForm.route}
                    onChange={(e) => setModuleForm({ ...moduleForm, route: e.target.value })}
                    className="col-span-3"
                    placeholder="Ex: /sales"
                  />
                </div>
                <div className="grid grid-cols-4 items-center gap-4">
                  <Label htmlFor="icon" className="text-right">
                    Ícone
                  </Label>
                  <Input
                    id="icon"
                    value={moduleForm.icon}
                    onChange={(e) => setModuleForm({ ...moduleForm, icon: e.target.value })}
                    className="col-span-3"
                    placeholder="Nome do ícone Lucide (ex: ShoppingCart)"
                  />
                </div>
              </div>
              <DialogFooter>
                <Button variant="outline" onClick={() => setIsModuleDialogOpen(false)}>
                  Cancelar
                </Button>
                <Button onClick={handleUpdateModule}>Salvar</Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>
        </TabsContent>
      </Tabs>
    </div>
  )
}
