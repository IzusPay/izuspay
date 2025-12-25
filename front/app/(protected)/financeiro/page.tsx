"use client"

import { DashboardLayout } from "@/components/dashboard-layout"
import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"
import { RefreshCw } from "lucide-react"
import { useState, useEffect } from "react"
import { apiClient } from "@/lib/api"

export default function FinanceiroPage() {
  const [withdrawals, setWithdrawals] = useState<any[]>([])
  const [wallet, setWallet] = useState({ balance: 0, blockedBalance: 0 })
  const [isWithdrawOpen, setIsWithdrawOpen] = useState(false)
  const [withdrawForm, setWithdrawForm] = useState({
    amount: "",
    pixKey: "",
    pixKeyType: "phone",
  })
  const [isSubmitting, setIsSubmitting] = useState(false)

  useEffect(() => {
    fetchWallet()
    fetchWithdrawals()
  }, [])

  const fetchWallet = async () => {
    try {
      const res = await apiClient("/api/seller-wallet/gestao")
      const data = await res.json()
      if (data.status && data.data) {
        setWallet(data.data)
      }
    } catch (error) {
      console.error("[v0] Error fetching wallet:", error)
    }
  }

  const fetchWithdrawals = async () => {
    try {
      const res = await apiClient("/api/withdrawals")
      const data = await res.json()
      if (data.data) {
        setWithdrawals(data.data)
      }
    } catch (error) {
      console.error("[v0] Error fetching withdrawals:", error)
    }
  }

  const handleWithdraw = async () => {
    setIsSubmitting(true)
    try {
      const res = await apiClient("/api/withdrawals", {
        method: "POST",
        body: JSON.stringify({
          amount: Number.parseFloat(withdrawForm.amount),
          pix_key: withdrawForm.pixKey,
          pix_key_type: withdrawForm.pixKeyType,
        }),
      })
      const data = await res.json()

      if (data.status) {
        alert("Saque solicitado com sucesso!")
        setIsWithdrawOpen(false)
        setWithdrawForm({ amount: "", pixKey: "", pixKeyType: "phone" })
        fetchWithdrawals()
        fetchWallet()
      } else {
        alert(data.message || "Erro ao solicitar saque")
      }
    } catch (error) {
      console.error("[v0] Error creating withdrawal:", error)
      alert("Erro ao solicitar saque")
    } finally {
      setIsSubmitting(false)
    }
  }

  const getStatusColor = (status: string) => {
    switch (status) {
      case "pending":
        return "bg-yellow-100 text-yellow-800"
      case "paid":
        return "bg-green-100 text-green-800"
      case "failed":
        return "bg-red-100 text-red-800"
      case "rejected":
        return "bg-red-100 text-red-800"
      default:
        return "bg-gray-100 text-gray-800"
    }
  }

  const getStatusLabel = (status: string) => {
    switch (status) {
      case "pending":
        return "Pendente"
      case "paid":
        return "Pago"
      case "failed":
        return "Falhou"
      case "rejected":
        return "Rejeitado"
      default:
        return status
    }
  }

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-2xl font-semibold">Split Financeiro</h1>

        <Tabs defaultValue="saque">
          <TabsList>
            <TabsTrigger value="saque">Saque</TabsTrigger>
            <TabsTrigger value="retido">Saldo retido</TabsTrigger>
          </TabsList>

          <TabsContent value="saque" className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Card className="p-6">
                <h3 className="text-sm font-medium mb-4">Saque Pix</h3>
                <div className="space-y-2">
                  <div>
                    <div className="text-xs text-muted-foreground">💰 Saldo Disponível</div>
                    <div className="text-2xl font-bold">R$ {(wallet.balance || 0).toFixed(2)}</div>
                  </div>
                </div>
                <Dialog open={isWithdrawOpen} onOpenChange={setIsWithdrawOpen}>
                  <DialogTrigger asChild>
                    <Button className="w-full mt-4 bg-black text-white hover:bg-gray-800">Efetuar Saque</Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Solicitar Saque PIX</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-4 py-4">
                      <div>
                        <Label htmlFor="amount">Valor</Label>
                        <Input
                          id="amount"
                          type="number"
                          step="0.01"
                          placeholder="0.00"
                          value={withdrawForm.amount}
                          onChange={(e) => setWithdrawForm({ ...withdrawForm, amount: e.target.value })}
                        />
                        <p className="text-xs text-muted-foreground mt-1">
                          Saldo disponível: R$ {(wallet.balance || 0).toFixed(2)}
                        </p>
                      </div>

                      <div>
                        <Label htmlFor="pixKeyType">Tipo de Chave PIX</Label>
                        <Select
                          value={withdrawForm.pixKeyType}
                          onValueChange={(value) => setWithdrawForm({ ...withdrawForm, pixKeyType: value })}
                        >
                          <SelectTrigger>
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="phone">Telefone</SelectItem>
                            <SelectItem value="email">E-mail</SelectItem>
                            <SelectItem value="cpf">CPF</SelectItem>
                            <SelectItem value="cnpj">CNPJ</SelectItem>
                            <SelectItem value="random">Chave Aleatória</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>

                      <div>
                        <Label htmlFor="pixKey">Chave PIX</Label>
                        <Input
                          id="pixKey"
                          placeholder="Digite sua chave PIX"
                          value={withdrawForm.pixKey}
                          onChange={(e) => setWithdrawForm({ ...withdrawForm, pixKey: e.target.value })}
                        />
                      </div>

                      <Button
                        className="w-full bg-black text-white hover:bg-gray-800"
                        onClick={handleWithdraw}
                        disabled={isSubmitting || !withdrawForm.amount || !withdrawForm.pixKey}
                      >
                        {isSubmitting ? "Processando..." : "Confirmar Saque"}
                      </Button>
                    </div>
                  </DialogContent>
                </Dialog>
              </Card>

              <Card className="p-6">
                <h3 className="text-sm font-medium mb-4">Saque cartão</h3>
                <div className="space-y-2">
                  <div>
                    <div className="text-xs text-muted-foreground">💰 Saldo Disponível</div>
                    <div className="text-2xl font-bold">R$ 0,00</div>
                  </div>
                  <div>
                    <div className="text-xs text-muted-foreground">⏰ Saldo Pendente</div>
                    <div className="text-xl font-semibold">R$ 0,00</div>
                  </div>
                </div>
                <Button className="w-full mt-4 bg-gray-300 text-gray-500" disabled>
                  Efetuar Saque
                </Button>
              </Card>

              <Card className="p-6">
                <h3 className="text-sm font-medium mb-4">Saque Boleto</h3>
                <div className="space-y-2">
                  <div>
                    <div className="text-xs text-muted-foreground">💰 Saldo Disponível</div>
                    <div className="text-2xl font-bold">R$ 0,00</div>
                  </div>
                  <div>
                    <div className="text-xs text-muted-foreground">⏰ Saldo Pendente</div>
                    <div className="text-xl font-semibold">R$ 0,00</div>
                  </div>
                </div>
                <Button className="w-full mt-4 bg-gray-300 text-gray-500" disabled>
                  Efetuar Saque
                </Button>
              </Card>
            </div>

            <Card className="p-6">
              <div className="mb-4 flex items-center gap-4">
                <div>
                  <div className="text-sm text-muted-foreground">Conversão em tempo real</div>
                  <div className="text-xl font-bold">0.068841 USDT</div>
                  <div className="text-xs text-muted-foreground">Saldo PIX: R$ 0,38 • Cotação: R$ 5,52</div>
                  <div className="text-xs text-blue-600">Cotação pode variar</div>
                </div>
              </div>
            </Card>

            <Tabs defaultValue="historico">
              <TabsList>
                <TabsTrigger value="historico">Histórico de saques</TabsTrigger>
                <TabsTrigger value="taxas">Taxas</TabsTrigger>
              </TabsList>

              <TabsContent value="historico">
                <Card>
                  <div className="flex items-center justify-between p-4 border-b">
                    <h3 className="font-semibold">Histórico de Saques</h3>
                    <Button variant="ghost" size="sm" onClick={fetchWithdrawals}>
                      <RefreshCw className="h-4 w-4" />
                    </Button>
                  </div>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Data</TableHead>
                        <TableHead>Valor</TableHead>
                        <TableHead>Método</TableHead>
                        <TableHead>Chave PIX</TableHead>
                        <TableHead>Tipo</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Processado</TableHead>
                        <TableHead>Ref. Externa</TableHead>
                        <TableHead>ID</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {withdrawals.map((w) => (
                        <TableRow key={w.id}>
                          <TableCell className="text-xs">{new Date(w.created_at).toLocaleString("pt-BR")}</TableCell>
                          <TableCell className="font-medium">
                            R$ {Number.parseFloat(w.amount || 0).toFixed(2)}
                          </TableCell>
                          <TableCell>PIX</TableCell>
                          <TableCell className="text-xs">{w.pix_key || "-"}</TableCell>
                          <TableCell className="text-xs">
                            {w.pix_key_type === "phone" ? "Telefone" : w.pix_key_type === "email" ? "E-mail" : "-"}
                          </TableCell>
                          <TableCell>
                            <span className={`px-2 py-1 rounded-full text-xs ${getStatusColor(w.status)}`}>
                              {getStatusLabel(w.status)}
                            </span>
                          </TableCell>
                          <TableCell className="text-xs">
                            {w.updated_at ? new Date(w.updated_at).toLocaleString("pt-BR") : "-"}
                          </TableCell>
                          <TableCell className="text-xs">-</TableCell>
                          <TableCell className="text-xs font-mono">{w.id}</TableCell>
                        </TableRow>
                      ))}
                      {withdrawals.length === 0 && (
                        <TableRow>
                          <TableCell colSpan={9} className="text-center text-muted-foreground py-8">
                            Nenhum saque encontrado
                          </TableCell>
                        </TableRow>
                      )}
                    </TableBody>
                  </Table>
                </Card>
              </TabsContent>
            </Tabs>
          </TabsContent>
        </Tabs>
      </div>
    </DashboardLayout>
  )
}
