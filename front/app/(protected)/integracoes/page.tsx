"use client"

import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"
import { Plus, Copy, Trash2, Eye, EyeOff, AlertTriangle, CheckCircle2 } from "lucide-react"
import { useState, useEffect } from "react"
import { apiClient } from "@/lib/api"

export default function IntegracoesPage() {
  const [apiKeys, setApiKeys] = useState<any[]>([])
  const [isDialogOpen, setIsDialogOpen] = useState(false)
  const [keyForm, setKeyForm] = useState({
    name: "",
    environment: "production",
  })
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [newKey, setNewKey] = useState<string | null>(null)
  const [visibleKeys, setVisibleKeys] = useState<Set<string>>(new Set())

  useEffect(() => {
    fetchApiKeys()
  }, [])

  const fetchApiKeys = async () => {
    try {
      const res = await apiClient("/api/api-keys")
      
      const contentType = res.headers.get("content-type")
      if (contentType && !contentType.includes("application/json")) {
        console.error("[v0] API returned non-JSON response:", contentType)
        // Optionally read text to see what it is (likely HTML error page)
        // const text = await res.text()
        // console.error("[v0] Response body preview:", text.substring(0, 200))
        return
      }

      if (res.ok) {
        const data = await res.json()
        if (Array.isArray(data)) {
          setApiKeys(data)
        } else if (data.data && Array.isArray(data.data)) {
           setApiKeys(data.data)
        }
      }
    } catch (error) {
      console.error("[v0] Error fetching API keys:", error)
    }
  }

  const handleCreateApiKey = async () => {
    setIsSubmitting(true)
    try {
      const res = await apiClient("/api/api-keys", {
        method: "POST",
        body: JSON.stringify({
          name: keyForm.name,
          environment: keyForm.environment || "production",
        }),
      })
      const data = await res.json()

      if (res.status === 201) {
        setNewKey(data.api_key.token)
        fetchApiKeys()
      } else {
        alert(data.message || "Erro ao criar chave API")
      }
    } catch (error) {
      console.error("[v0] Error creating API key:", error)
      alert("Erro ao criar chave API")
    } finally {
      setIsSubmitting(false)
    }
  }

  const handleDeleteApiKey = async (id: string) => {
    if (!confirm("Tem certeza que deseja excluir esta chave API?")) return

    try {
      const res = await apiClient(`/api/api-keys/${id}`, {
        method: "DELETE",
      })
      const data = await res.json()

      if (data.status) {
        alert("Chave API excluída com sucesso!")
        fetchApiKeys()
      } else {
        alert(data.message || "Erro ao excluir chave API")
      }
    } catch (error) {
      console.error("[v0] Error deleting API key:", error)
      alert("Erro ao excluir chave API")
    }
  }

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text)
    alert("Chave copiada para a área de transferência!")
  }

  const toggleKeyVisibility = (id: string) => {
    const newVisible = new Set(visibleKeys)
    if (newVisible.has(id)) {
      newVisible.delete(id)
    } else {
      newVisible.add(id)
    }
    setVisibleKeys(newVisible)
  }

  const maskKey = (key: string) => {
    if (!key) return ""
    return key.substring(0, 8) + "..."
  }

  return (
    <>
      <div className="space-y-6">
        <h1 className="text-2xl font-semibold">Integrações</h1>

        <Card className="p-6">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-xl font-semibold">Chaves de API</h2>
            <Dialog
              open={isDialogOpen}
              onOpenChange={(open) => {
                setIsDialogOpen(open)
                if (!open) {
                  setKeyForm({ name: "", environment: "production" })
                  setNewKey(null)
                }
              }}
            >
              <DialogTrigger asChild>
                <Button className="bg-black text-white hover:bg-gray-800">
                  <Plus className="h-4 w-4 mr-2" />
                  Adicionar
                </Button>
              </DialogTrigger>
              <DialogContent>
                <DialogHeader>
                  <DialogTitle>Nova Chave API</DialogTitle>
                </DialogHeader>
                {newKey ? (
                  <div className="space-y-4 py-4">
                    <div className="p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
                      <CheckCircle2 className="h-5 w-5 text-green-600 mt-0.5" />
                      <div>
                        <h4 className="font-semibold text-green-900">Chave criada com sucesso!</h4>
                        <p className="text-sm text-green-700 mt-1">
                          Copie sua chave agora. Por segurança, ela não será exibida novamente.
                        </p>
                      </div>
                    </div>

                    <div className="space-y-2">
                      <Label>Sua Chave API</Label>
                      <div className="flex items-center gap-2">
                        <Input value={newKey} readOnly className="font-mono bg-muted" />
                        <Button size="icon" variant="outline" onClick={() => copyToClipboard(newKey)}>
                          <Copy className="h-4 w-4" />
                        </Button>
                      </div>
                    </div>

                    <Button className="w-full" onClick={() => setIsDialogOpen(false)}>
                      Concluir
                    </Button>
                  </div>
                ) : (
                  <div className="space-y-4 py-4">
                    <div>
                      <Label htmlFor="name">Nome da Chave</Label>
                      <Input
                        id="name"
                        placeholder="Ex: integração-produção"
                        value={keyForm.name}
                        onChange={(e) => setKeyForm({ ...keyForm, name: e.target.value })}
                      />
                      <p className="text-xs text-muted-foreground mt-1">
                        Escolha um nome descritivo para identificar esta chave
                      </p>
                    </div>

                    <div>
                      <Label htmlFor="environment">Ambiente</Label>
                      <Select
                        value={keyForm.environment}
                        onValueChange={(value) => setKeyForm({ ...keyForm, environment: value })}
                      >
                        <SelectTrigger>
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="production">Produção</SelectItem>
                          <SelectItem value="sandbox">Sandbox</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>

                    <Button
                      className="w-full bg-black text-white hover:bg-gray-800"
                      onClick={handleCreateApiKey}
                      disabled={isSubmitting || !keyForm.name}
                    >
                      {isSubmitting ? "Criando..." : "Criar Chave API"}
                    </Button>
                  </div>
                )}
              </DialogContent>
            </Dialog>
          </div>

          <div className="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div className="flex items-start gap-2">
              <div className="text-blue-600 mt-1">ℹ️</div>
              <div className="text-sm text-blue-900">
                Utilize estas chaves API para integrar sua operação. Não compartilhe com terceiros
              </div>
            </div>
          </div>

          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Nome</TableHead>
                <TableHead>Chave Secreta</TableHead>
                <TableHead>Criado em</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Ambiente</TableHead>
                <TableHead>Ações</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {apiKeys.map((key) => (
                <TableRow key={key.id}>
                  <TableCell className="font-medium">{key.name}</TableCell>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      <code className="text-sm font-mono">{visibleKeys.has(key.id) ? key.key : maskKey(key.key)}</code>
                      <Button variant="ghost" size="sm" onClick={() => toggleKeyVisibility(key.id)}>
                        {visibleKeys.has(key.id) ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                      </Button>
                      <Button variant="ghost" size="sm" onClick={() => copyToClipboard(key.key)}>
                        <Copy className="h-4 w-4" />
                      </Button>
                    </div>
                  </TableCell>
                  <TableCell className="text-sm">{new Date(key.created_at).toLocaleString("pt-BR")}</TableCell>
                  <TableCell>
                    <span className="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Ativo</span>
                  </TableCell>
                  <TableCell>
                    <span className="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Produção</span>
                  </TableCell>
                  <TableCell>
                    <Button variant="ghost" size="sm" onClick={() => handleDeleteApiKey(key.id)}>
                      <Trash2 className="h-4 w-4 text-red-600" />
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
              {apiKeys.length === 0 && (
                <TableRow>
                  <TableCell colSpan={6} className="text-center text-muted-foreground py-8">
                    Nenhuma chave API encontrada
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
