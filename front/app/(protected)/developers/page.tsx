"use client"

import { useEffect, useState } from "react"
import { Plus, Trash2, Key, Globe, Eye } from "lucide-react"
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
import { Checkbox } from "@/components/ui/checkbox"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Badge } from "@/components/ui/badge"
import { useToast } from "@/components/ui/use-toast"
import { devToolsService, ApiKey, Webhook, WebhookLog } from "@/lib/services/dev-tools.service"
import { format } from "date-fns"
import { ptBR } from "date-fns/locale"

export default function DevelopersPage() {
  const { toast } = useToast()
  const [apiKeys, setApiKeys] = useState<ApiKey[]>([])
  const [webhooks, setWebhooks] = useState<Webhook[]>([])
  const [loading, setLoading] = useState(true)

  // API Key creation
  const [newKeyName, setNewKeyName] = useState("")
  const [createdKey, setCreatedKey] = useState<string | null>(null)
  const [isKeyDialogOpen, setIsKeyDialogOpen] = useState(false)

  // Webhook creation
  const [newWebhookUrl, setNewWebhookUrl] = useState("")
  const [newWebhookDesc, setNewWebhookDesc] = useState("")
  const [selectedEvents, setSelectedEvents] = useState<string[]>([])
  const [isWebhookDialogOpen, setIsWebhookDialogOpen] = useState(false)

  const availableEvents = [
    "sale.created",
    "sale.paid",
    "sale.failed",
    "sale.refunded",
    "withdrawal.updated",
  ]

  async function loadData() {
    try {
      setLoading(true)
      const [keysData, webhooksData] = await Promise.all([
        devToolsService.findAllApiKeys(),
        devToolsService.findAllWebhooks(),
      ])
      setApiKeys(keysData)
      setWebhooks(webhooksData)
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao carregar dados",
        description: "Não foi possível carregar as informações de desenvolvedor.",
      })
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    loadData()
  }, [])

  async function handleCreateApiKey() {
    if (!newKeyName) return

    try {
      const data = await devToolsService.createApiKey({ name: newKeyName })
      setCreatedKey(data.secretKey)
      setApiKeys([...apiKeys, data])
      setNewKeyName("")
      toast({
        title: "Chave API criada",
        description: "Copie a chave agora, você não poderá vê-la novamente.",
      })
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao criar",
        description: "Não foi possível criar a chave de API.",
      })
    }
  }

  async function handleDeleteApiKey(id: string) {
    try {
      await devToolsService.deleteApiKey(id)
      setApiKeys(apiKeys.filter(k => k.id !== id))
      toast({
        title: "Chave excluída",
        description: "A chave de API foi revogada.",
      })
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao excluir",
        description: "Não foi possível revogar a chave.",
      })
    }
  }

  async function handleCreateWebhook() {
    if (!newWebhookUrl || selectedEvents.length === 0) return

    try {
      const data = await devToolsService.createWebhook({
        url: newWebhookUrl,
        events: selectedEvents,
        description: newWebhookDesc,
      })
      setWebhooks([...webhooks, data])
      setNewWebhookUrl("")
      setNewWebhookDesc("")
      setSelectedEvents([])
      setIsWebhookDialogOpen(false)
      toast({
        title: "Webhook criado",
        description: "O webhook foi registrado com sucesso.",
      })
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao criar",
        description: "Não foi possível registrar o webhook.",
      })
    }
  }

  async function handleDeleteWebhook(id: string) {
    try {
      await devToolsService.deleteWebhook(id)
      setWebhooks(webhooks.filter(w => w.id !== id))
      toast({
        title: "Webhook excluído",
        description: "O webhook foi removido com sucesso.",
      })
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao excluir",
        description: "Não foi possível remover o webhook.",
      })
    }
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Desenvolvedores</h1>
        <p className="text-muted-foreground">
          Gerencie chaves de API e Webhooks para integrações.
        </p>
      </div>

      <Tabs defaultValue="apikeys">
        <TabsList>
          <TabsTrigger value="apikeys">Chaves de API</TabsTrigger>
          <TabsTrigger value="webhooks">Webhooks</TabsTrigger>
        </TabsList>

        <TabsContent value="apikeys" className="space-y-4">
          <div className="flex justify-end">
            <Dialog open={isKeyDialogOpen} onOpenChange={setIsKeyDialogOpen}>
              <DialogTrigger asChild>
                <Button>
                  <Plus className="mr-2 h-4 w-4" /> Nova Chave
                </Button>
              </DialogTrigger>
              <DialogContent>
                <DialogHeader>
                  <DialogTitle>Nova Chave de API</DialogTitle>
                  <DialogDescription>
                    Crie uma nova chave para autenticar suas requisições.
                  </DialogDescription>
                </DialogHeader>
                {!createdKey ? (
                  <div className="grid gap-4 py-4">
                    <div className="grid grid-cols-4 items-center gap-4">
                      <Label htmlFor="name" className="text-right">
                        Nome
                      </Label>
                      <Input
                        id="name"
                        value={newKeyName}
                        onChange={(e) => setNewKeyName(e.target.value)}
                        className="col-span-3"
                        placeholder="Ex: Integração Site"
                      />
                    </div>
                  </div>
                ) : (
                  <div className="space-y-4 py-4">
                    <div className="p-4 bg-muted rounded-lg break-all font-mono text-sm">
                      {createdKey}
                    </div>
                    <p className="text-sm text-destructive font-medium">
                      Copie esta chave agora! Ela não será mostrada novamente.
                    </p>
                  </div>
                )}
                <DialogFooter>
                  {!createdKey ? (
                    <Button onClick={handleCreateApiKey} disabled={!newKeyName}>
                      Gerar Chave
                    </Button>
                  ) : (
                    <Button onClick={() => {
                      setCreatedKey(null)
                      setIsKeyDialogOpen(false)
                    }}>
                      Concluir
                    </Button>
                  )}
                </DialogFooter>
              </DialogContent>
            </Dialog>
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Chaves Ativas</CardTitle>
              <CardDescription>
                Lista de chaves de API geradas para sua conta.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Nome</TableHead>
                    <TableHead>Prefixo</TableHead>
                    <TableHead>Criada em</TableHead>
                    <TableHead>Último Uso</TableHead>
                    <TableHead className="text-right">Ações</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {loading ? (
                    <TableRow>
                      <TableCell colSpan={5} className="text-center py-8">
                        Carregando...
                      </TableCell>
                    </TableRow>
                  ) : apiKeys.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={5} className="text-center py-8">
                        Nenhuma chave encontrada.
                      </TableCell>
                    </TableRow>
                  ) : (
                    apiKeys.map((key) => (
                      <TableRow key={key.id}>
                        <TableCell className="font-medium">{key.name}</TableCell>
                        <TableCell className="font-mono">{key.prefix}...</TableCell>
                        <TableCell>
                          {format(new Date(key.createdAt), "dd/MM/yyyy", { locale: ptBR })}
                        </TableCell>
                        <TableCell>
                          {key.lastUsedAt
                            ? format(new Date(key.lastUsedAt), "dd/MM/yyyy HH:mm", { locale: ptBR })
                            : "-"}
                        </TableCell>
                        <TableCell className="text-right">
                          <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => handleDeleteApiKey(key.id)}
                          >
                            <Trash2 className="h-4 w-4 text-destructive" />
                          </Button>
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="webhooks" className="space-y-4">
          <div className="flex justify-end">
            <Dialog open={isWebhookDialogOpen} onOpenChange={setIsWebhookDialogOpen}>
              <DialogTrigger asChild>
                <Button>
                  <Plus className="mr-2 h-4 w-4" /> Novo Webhook
                </Button>
              </DialogTrigger>
              <DialogContent className="max-w-2xl">
                <DialogHeader>
                  <DialogTitle>Novo Webhook</DialogTitle>
                  <DialogDescription>
                    Receba notificações em tempo real sobre eventos.
                  </DialogDescription>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                  <div className="grid gap-2">
                    <Label htmlFor="url">URL de Callback</Label>
                    <Input
                      id="url"
                      value={newWebhookUrl}
                      onChange={(e) => setNewWebhookUrl(e.target.value)}
                      placeholder="https://seu-site.com/webhook"
                    />
                  </div>
                  <div className="grid gap-2">
                    <Label htmlFor="desc">Descrição (Opcional)</Label>
                    <Input
                      id="desc"
                      value={newWebhookDesc}
                      onChange={(e) => setNewWebhookDesc(e.target.value)}
                      placeholder="Identificador do webhook"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label>Eventos</Label>
                    <div className="grid grid-cols-2 gap-2">
                      {availableEvents.map((event) => (
                        <div key={event} className="flex items-center space-x-2">
                          <Checkbox
                            id={event}
                            checked={selectedEvents.includes(event)}
                            onCheckedChange={(checked) => {
                              if (checked) {
                                setSelectedEvents([...selectedEvents, event])
                              } else {
                                setSelectedEvents(selectedEvents.filter(e => e !== event))
                              }
                            }}
                          />
                          <Label htmlFor={event} className="text-sm font-normal">
                            {event}
                          </Label>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
                <DialogFooter>
                  <Button
                    onClick={handleCreateWebhook}
                    disabled={!newWebhookUrl || selectedEvents.length === 0}
                  >
                    Salvar Webhook
                  </Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Webhooks Configurados</CardTitle>
              <CardDescription>
                Pontos de notificação ativos.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>URL</TableHead>
                    <TableHead>Eventos</TableHead>
                    <TableHead>Descrição</TableHead>
                    <TableHead className="text-right">Ações</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {loading ? (
                    <TableRow>
                      <TableCell colSpan={4} className="text-center py-8">
                        Carregando...
                      </TableCell>
                    </TableRow>
                  ) : webhooks.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={4} className="text-center py-8">
                        Nenhum webhook configurado.
                      </TableCell>
                    </TableRow>
                  ) : (
                    webhooks.map((webhook) => (
                      <TableRow key={webhook.id}>
                        <TableCell className="font-mono text-xs truncate max-w-[200px]" title={webhook.url}>
                          {webhook.url}
                        </TableCell>
                        <TableCell>
                          <div className="flex flex-wrap gap-1">
                            {webhook.events.map(event => (
                              <Badge key={event} variant="secondary" className="text-[10px]">
                                {event}
                              </Badge>
                            ))}
                          </div>
                        </TableCell>
                        <TableCell>{webhook.description || "-"}</TableCell>
                        <TableCell className="text-right">
                          <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => handleDeleteWebhook(webhook.id)}
                          >
                            <Trash2 className="h-4 w-4 text-destructive" />
                          </Button>
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  )
}
