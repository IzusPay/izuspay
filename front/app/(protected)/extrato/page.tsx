"use client"

import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Search, Filter, X, Download } from "lucide-react"
import { useState, useEffect } from "react"
import { apiClient } from "@/lib/api"

export default function ExtratoPage() {
  const [transactions, setTransactions] = useState<any[]>([])
  const [meta, setMeta] = useState<any>({})
  const [filters, setFilters] = useState({
    status: "all",
    paymentMethod: "all",
    search: "",
    metaKey: "",
    metaValue: "",
  })
  const [currentPage, setCurrentPage] = useState(1)

  useEffect(() => {
    fetchTransactions()
  }, [currentPage])

  const fetchTransactions = async () => {
    try {
      const params = new URLSearchParams({
        page: currentPage.toString(),
        limit: "10",
      })

      if (filters.status !== "all") params.append("status", filters.status)
      if (filters.paymentMethod !== "all") params.append("paymentMethod", filters.paymentMethod)

      const res = await apiClient(`/api/transactions?${params}`)
      const data = await res.json()
      console.log("[v0] Transactions data:", data)
      if (data.data) {
        setTransactions(data.data)
        setMeta(data.meta || {})
      }
    } catch (error) {
      console.error("[v0] Error fetching transactions:", error)
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
      case "refunded":
        return "bg-purple-100 text-purple-800"
      case "expired":
        return "bg-gray-100 text-gray-800"
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
      case "refunded":
        return "Reembolsado"
      case "expired":
        return "Expirado"
      default:
        return status
    }
  }

  const approvedSum = transactions
    .filter((t) => t.status === "paid")
    .reduce((sum, t) => sum + (Number.parseFloat(t.amount) || 0), 0)
  const pendingSum = transactions
    .filter((t) => t.status === "pending")
    .reduce((sum, t) => sum + (Number.parseFloat(t.amount) || 0), 0)

  return (
    <>
      <div className="space-y-6">
        <h1 className="text-2xl font-semibold">Extrato</h1>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Card className="p-6 border-l-4 border-l-green-500">
            <div className="text-sm text-muted-foreground mb-1">📊 Transações aprovadas</div>
            <div className="text-3xl font-bold">R$ {approvedSum.toFixed(2)}</div>
          </Card>

          <Card className="p-6 border-l-4 border-l-yellow-500">
            <div className="text-sm text-muted-foreground mb-1">⏰ Transações pendentes</div>
            <div className="text-3xl font-bold">R$ {pendingSum.toFixed(2)}</div>
          </Card>

          <Card className="p-6">
            <div className="text-sm text-muted-foreground mb-1">✅ Aprovadas</div>
            <div className="text-3xl font-bold">{transactions.filter((t) => t.status === "paid").length}</div>
          </Card>
        </div>

        <Card className="p-6">
          <div className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Buscar por código da transação..."
                  className="pl-9"
                  value={filters.search}
                  onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                />
              </div>
              <Select value={filters.status} onValueChange={(v) => setFilters({ ...filters, status: v })}>
                <SelectTrigger>
                  <SelectValue placeholder="Todos os status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Todos os status</SelectItem>
                  <SelectItem value="pending">Pendente</SelectItem>
                  <SelectItem value="paid">Pago</SelectItem>
                  <SelectItem value="failed">Falhou</SelectItem>
                  <SelectItem value="refunded">Reembolsado</SelectItem>
                  <SelectItem value="expired">Expirado</SelectItem>
                </SelectContent>
              </Select>
              <Select value={filters.paymentMethod} onValueChange={(v) => setFilters({ ...filters, paymentMethod: v })}>
                <SelectTrigger>
                  <SelectValue placeholder="Todos os métodos" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Todos os métodos</SelectItem>
                  <SelectItem value="PIX">PIX</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Input
                placeholder="Chave do metadata (ex: sellerExternalRef)"
                value={filters.metaKey}
                onChange={(e) => setFilters({ ...filters, metaKey: e.target.value })}
              />
              <Input
                placeholder="Valor do metadata"
                value={filters.metaValue}
                onChange={(e) => setFilters({ ...filters, metaValue: e.target.value })}
              />
            </div>

            <div className="flex gap-2">
              <Button onClick={fetchTransactions} className="bg-black text-white hover:bg-gray-800">
                <Filter className="h-4 w-4 mr-2" />
                Aplicar Filtros
              </Button>
              <Button
                variant="outline"
                onClick={() =>
                  setFilters({ status: "all", paymentMethod: "all", search: "", metaKey: "", metaValue: "" })
                }
              >
                <X className="h-4 w-4 mr-2" />
                Limpar
              </Button>
              <Button variant="outline">
                <Download className="h-4 w-4 mr-2" />
                Exportar
              </Button>
            </div>
          </div>
        </Card>

        <Card>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>ID</TableHead>
                <TableHead>Metadata</TableHead>
                <TableHead>Cliente</TableHead>
                <TableHead>Método</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Valor Bruto</TableHead>
                <TableHead>Taxa</TableHead>
                <TableHead>Valor Líquido</TableHead>
                <TableHead>Data</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {transactions.map((t) => (
                <TableRow key={t.transaction_id}>
                  <TableCell className="font-mono text-xs">{t.transaction_id?.substring(0, 20)}...</TableCell>
                  <TableCell className="text-xs">-</TableCell>
                  <TableCell>
                    <div className="text-sm">{t.customer?.name || "N/A"}</div>
                    <div className="text-xs text-muted-foreground">{t.customer?.email || ""}</div>
                  </TableCell>
                  <TableCell>PIX</TableCell>
                  <TableCell>
                    <span className={`px-2 py-1 rounded-full text-xs ${getStatusColor(t.status)}`}>
                      {getStatusLabel(t.status)}
                    </span>
                  </TableCell>
                  <TableCell className="font-semibold">R$ {Number.parseFloat(t.amount || 0).toFixed(2)}</TableCell>
                  <TableCell className="text-red-600">R$ 0,22</TableCell>
                  <TableCell className="text-green-600 font-semibold">
                    R$ {(Number.parseFloat(t.amount || 0) - 0.22).toFixed(2)}
                  </TableCell>
                  <TableCell className="text-xs">{new Date(t.created_at).toLocaleDateString("pt-BR")}</TableCell>
                </TableRow>
              ))}
              {transactions.length === 0 && (
                <TableRow>
                  <TableCell colSpan={9} className="text-center text-muted-foreground py-8">
                    Nenhuma transação encontrada
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>

          {meta.last_page > 1 && (
            <div className="flex items-center justify-between p-4 border-t">
              <div className="text-sm text-muted-foreground">
                Mostrando {transactions.length} de {meta.total} transações
              </div>
              <div className="flex gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  disabled={currentPage === 1}
                  onClick={() => setCurrentPage((p) => p - 1)}
                >
                  Anterior
                </Button>
                {Array.from({ length: Math.min(5, meta.last_page) }, (_, i) => i + 1).map((page) => (
                  <Button
                    key={page}
                    variant={currentPage === page ? "default" : "outline"}
                    size="sm"
                    onClick={() => setCurrentPage(page)}
                    className={currentPage === page ? "bg-black text-white" : ""}
                  >
                    {page}
                  </Button>
                ))}
                <Button
                  variant="outline"
                  size="sm"
                  disabled={currentPage === meta.last_page}
                  onClick={() => setCurrentPage((p) => p + 1)}
                >
                  Próxima
                </Button>
              </div>
            </div>
          )}
        </Card>
      </div>
    </>
  )
}
