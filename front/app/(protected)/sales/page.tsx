"use client"

import { useEffect, useState } from "react"
import Link from "next/link"
import { Plus, Eye } from "lucide-react"
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
import { Badge } from "@/components/ui/badge"
import { salesService, Sale, SaleStatus } from "@/lib/services/sales.service"
import { useToast } from "@/components/ui/use-toast"
import { format } from "date-fns"
import { ptBR } from "date-fns/locale"
import { useAuth } from "@/contexts/auth-context"

const statusMap: Record<SaleStatus, { label: string; variant: "default" | "secondary" | "destructive" | "outline" | "success" }> = {
  [SaleStatus.PENDING]: { label: "Pendente", variant: "outline" },
  [SaleStatus.PAID]: { label: "Pago", variant: "success" }, // Need to define success variant or use default
  [SaleStatus.FAILED]: { label: "Falhou", variant: "destructive" },
  [SaleStatus.REFUNDED]: { label: "Reembolsado", variant: "secondary" },
  [SaleStatus.EXPIRED]: { label: "Expirado", variant: "secondary" },
}

export default function SalesPage() {
  const [sales, setSales] = useState<Sale[]>([])
  const [loading, setLoading] = useState(true)
  const { toast } = useToast()
  const { user } = useAuth()

  // Verify Read Permission
  if (user?.role !== "admin" && !user?.permissions?.["sales"]?.canRead) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-red-600">Acesso Negado</h1>
          <p className="text-gray-600">Você não tem permissão para acessar este módulo.</p>
        </div>
      </div>
    )
  }

  async function loadSales() {
    try {
      setLoading(true)
      const data = await salesService.findAll()
      setSales(data)
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao carregar vendas",
        description: "Não foi possível obter a lista de vendas.",
      })
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    loadSales()
  }, [])

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Vendas</h1>
          <p className="text-muted-foreground">
            Visualize todas as transações realizadas.
          </p>
        </div>
        {(user?.role === "admin" || user?.permissions?.["sales"]?.canCreate) && (
          <Button asChild>
            <Link href="/sales/new">
              <Plus className="mr-2 h-4 w-4" /> Nova Venda (Manual)
            </Link>
          </Button>
        )}
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Histórico de Vendas</CardTitle>
          <CardDescription>
            Lista de todas as vendas processadas.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Data</TableHead>
                <TableHead>Produto</TableHead>
                <TableHead>Cliente</TableHead>
                <TableHead>Valor</TableHead>
                <TableHead>Status</TableHead>
                <TableHead className="text-right">Ações</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {loading ? (
                <TableRow>
                  <TableCell colSpan={6} className="text-center py-8">
                    Carregando...
                  </TableCell>
                </TableRow>
              ) : sales.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} className="text-center py-8">
                    Nenhuma venda encontrada.
                  </TableCell>
                </TableRow>
              ) : (
                sales.map((sale) => (
                  <TableRow key={sale.id}>
                    <TableCell>
                      {format(new Date(sale.createdAt), "dd/MM/yyyy HH:mm", { locale: ptBR })}
                    </TableCell>
                    <TableCell>{sale.product?.productName || "Produto Removido"}</TableCell>
                    <TableCell>
                      <div className="flex flex-col">
                        <span className="font-medium">{sale.payerName}</span>
                        <span className="text-xs text-muted-foreground">{sale.payerEmail}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      R$ {Number(sale.amount).toFixed(2)}
                    </TableCell>
                    <TableCell>
                      <Badge
                        variant={statusMap[sale.status].variant as any}
                        className={sale.status === SaleStatus.PAID ? "bg-green-500 hover:bg-green-600" : ""}
                      >
                        {statusMap[sale.status].label}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-right">
                      <Button variant="ghost" size="icon" asChild>
                        <Link href={`/sales/${sale.id}`}>
                          <Eye className="h-4 w-4" />
                        </Link>
                      </Button>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  )
}
