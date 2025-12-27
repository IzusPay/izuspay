"use client"

import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"
import { Switch } from "@/components/ui/switch"
import { Plus, Trash2, Search, Edit2 } from "lucide-react"
import { useState, useEffect } from "react"
import { apiClient } from "@/lib/api"
import { useAuth } from "@/contexts/auth-context"

export default function WebhooksPage() {
  const { user } = useAuth()
  const [webhooks, setWebhooks] = useState<any[]>([])
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [isDialogOpen, setIsDialogOpen] = useState(false)
  const [editingWebhook, setEditingWebhook] = useState<any>(null)
  const [webhookForm, setWebhookForm] = useState({
    url: "",
    description: "",
    isActive: true,
  })

  // Verify Read Permission
  if (user?.role !== "admin" && !user?.permissions?.["webhooks"]?.canRead) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-red-600">Acesso Negado</h1>
          <p className="text-gray-600">Você não tem permissão para acessar este módulo.</p>
        </div>
      </div>
    )
  }

  useEffect(() => {
    if (user?.role === "admin" || user?.permissions?.["webhooks"]?.canRead) {
      fetchWebhooks()
    }
  }, [user])

  const fetchWebhooks = async () => {
    try {
      const res = await apiClient("/api/webhooks")
      const data = await res.json()
      if (data.data) {
        setWebhooks(data.data)
      }
    } catch (error) {
      console.error("[v0] Error fetching webhooks:", error)
    }
  }

  const handleSaveWebhook = async () => {
    setIsSubmitting(true)
    try {
      const endpoint = editingWebhook
        ? `/api/webhooks/${editingWebhook.id}`
        : "/api/webhooks"

      const method = editingWebhook ? "PUT" : "POST"

      const res = await apiClient(endpoint, {
        method,
        body: JSON.stringify({
          url: webhookForm.url,
          description: webhookForm.description,
          is_active: webhookForm.isActive,
        }),
      })
      const data = await res.json()

      if (data.status) {
        alert(editingWebhook ? "Webhook atualizado com sucesso!" : "Webhook criado com sucesso!")
        setIsDialogOpen(false)
        setEditingWebhook(null)
        setWebhookForm({ url: "", description: "", isActive: true })
        fetchWebhooks()
      } else {
        alert(data.message || "Erro ao salvar webhook")
      }
    } catch (error) {
      console.error("[v0] Error saving webhook:", error)
      alert("Erro ao salvar webhook")
    } finally {
      setIsSubmitting(false)
    }
  }

  const handleDeleteWebhook = async (id: string) => {
    if (!confirm("Tem certeza que deseja excluir este webhook?")) return

    try {
      const res = await apiClient(`/api/webhooks/${id}`, {
        method: "DELETE",
      })
      const data = await res.json()

      if (data.status) {
        alert("Webhook excluído com sucesso!")
        fetchWebhooks()
      } else {
        alert(data.message || "Erro ao excluir webhook")
      }
    } catch (error) {
      console.error("[v0] Error deleting webhook:", error)
      alert("Erro ao excluir webhook")
    }
  }

  const handleEditWebhook = (webhook: any) => {
    setEditingWebhook(webhook)
    setWebhookForm({
      url: webhook.url,
      description: webhook.description,
      isActive: webhook.is_active,
    })
    setIsDialogOpen(true)
  }

  const activeCount = webhooks.filter((w) => w.is_active).length
  const inactiveCount = webhooks.filter((w) => !w.is_active).length

  return (
    <>
      <div className="space-y-6">
        <h1 className="text-2xl font-semibold">Webhooks</h1>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Card className="p-6 border-l-4 border-l-purple-500">
            <div className="text-sm text-muted-foreground mb-1">Inativos</div>
            <div className="text-4xl font-bold">{inactiveCount}</div>
          </Card>

          <Card className="p-6 border-l-4 border-l-blue-500">
            <div className="text-sm text-muted-foreground mb-1">Ativos</div>
            <div className="text-4xl font-bold">{activeCount}</div>
          </Card>

          <Card className="p-6">
            <div className="mb-2">
              <h3 className="font-semibold text-sm">API de Webhooks</h3>
              <p className="text-xs text-muted-foreground">Receba notificações em tempo real dos eventos</p>
            </div>
            <div className="flex flex-col gap-2">
              <Button variant="outline" size="sm">
                Acessar documentação
              </Button>
              <Button variant="outline" size="sm">
                Guia de disputas
              </Button>
            </div>
          </Card>
        </div>

        <Card className="p-6">
          <div className="flex items-center justify-between mb-6">
            <Dialog
              open={isDialogOpen}
              onOpenChange={(open) => {
                setIsDialogOpen(open)
                if (!open) {
                  setEditingWebhook(null)
                  setWebhookForm({ url: "", description: "", isActive: true })
                }
              }}
            >
              {(user?.role === "admin" || user?.permissions?.["webhooks"]?.canCreate) && (
                <DialogTrigger asChild>
                  <Button className="bg-black text-white hover:bg-gray-800">
                    <Plus className="h-4 w-4 mr-2" />
                    Novo Webhook
                  </Button>
                </DialogTrigger>
              )}
              <DialogContent>
                <DialogHeader>
                  <DialogTitle>{editingWebhook ? "Editar Webhook" : "Novo Webhook"}</DialogTitle>
                </DialogHeader>
                <div className="space-y-4 py-4">
                  <div>
                    <Label htmlFor="url">URL</Label>
                    <Input
                      id="url"
                      placeholder="https://seu-site.com/webhook"
                      value={webhookForm.url}
                      onChange={(e) => setWebhookForm({ ...webhookForm, url: e.target.value })}
                    />
                  </div>

                  <div>
                    <Label htmlFor="description">Descrição</Label>
                    <Input
                      id="description"
                      placeholder="Webhook principal"
                      value={webhookForm.description}
                      onChange={(e) => setWebhookForm({ ...webhookForm, description: e.target.value })}
                    />
                  </div>

                  <div className="flex items-center justify-between">
                    <Label htmlFor="active">Ativo</Label>
                    <Switch
                      id="active"
                      checked={webhookForm.isActive}
                      onCheckedChange={(checked) => setWebhookForm({ ...webhookForm, isActive: checked })}
                    />
                  </div>

                  <Button
                    className="w-full bg-black text-white hover:bg-gray-800"
                    onClick={handleSaveWebhook}
                    disabled={isSubmitting || !webhookForm.url}
                  >
                    {isSubmitting ? "Salvando..." : editingWebhook ? "Atualizar" : "Criar Webhook"}
                  </Button>
                </div>
              </DialogContent>
            </Dialog>

            <div className="flex gap-2">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input placeholder="Digite o código da transação" className="pl-9 w-80" />
              </div>
              <Button>Aplicar Filtros</Button>
            </div>
          </div>

          <div className="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div className="flex items-start gap-2">
              <div className="text-blue-600 mt-1">ℹ️</div>
              <div className="text-sm text-blue-900">Configure os endpoints para receber notificação dos eventos</div>
            </div>
          </div>

          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>URL</TableHead>
                <TableHead>Descrição</TableHead>
                <TableHead>Evento</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Criado em</TableHead>
                <TableHead>Ações</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {webhooks.map((webhook) => (
                <TableRow key={webhook.id}>
                  <TableCell className="font-mono text-xs max-w-xs truncate">{webhook.url}</TableCell>
                  <TableCell>{webhook.description}</TableCell>
                  <TableCell>
                    <span className="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800">
                      Todos os Eventos
                    </span>
                  </TableCell>
                  <TableCell>
                    <span
                      className={`px-2 py-1 rounded-full text-xs ${
                        webhook.is_active ? "bg-green-100 text-green-800" : "bg-gray-100 text-gray-800"
                      }`}
                    >
                      {webhook.is_active ? "Ativo" : "Inativo"}
                    </span>
                  </TableCell>
                  <TableCell className="text-sm">{new Date(webhook.created_at).toLocaleString("pt-BR")}</TableCell>
                  <TableCell>
                    <div className="flex gap-2">
                      <Button variant="ghost" size="sm" onClick={() => handleEditWebhook(webhook)}>
                        <Edit2 className="h-4 w-4" />
                      </Button>
                      <Button variant="ghost" size="sm" onClick={() => handleDeleteWebhook(webhook.id)}>
                        <Trash2 className="h-4 w-4 text-red-600" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              ))}
              {webhooks.length === 0 && (
                <TableRow>
                  <TableCell colSpan={6} className="text-center text-muted-foreground py-8">
                    Nenhum webhook encontrado
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </Card>
      </div>
    </>
  )
}
