"use client"

import { useEffect, useState } from "react"
import { format } from "date-fns"
import { ptBR } from "date-fns/locale"
import { DollarSign, TrendingUp } from "lucide-react"
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
import { useToast } from "@/components/ui/use-toast"
import { financialService, SystemProfit, ProfitSource } from "@/lib/services/financial.service"
import { useAuth } from "@/contexts/auth-context"

const sourceMap: Record<ProfitSource, { label: string; variant: "default" | "secondary" | "outline" }> = {
  [ProfitSource.TRANSACTION_FEE]: { label: "Taxa de Transação", variant: "default" },
  [ProfitSource.WITHDRAWAL_FEE]: { label: "Taxa de Saque", variant: "secondary" },
}

export default function FinancialPage() {
  const [profits, setProfits] = useState<SystemProfit[]>([])
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)
  const { toast } = useToast()
  const { user } = useAuth()

  // Verify Read Permission
  if (user?.role !== "admin" && !user?.permissions?.["financial"]?.canRead) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-red-600">Acesso Negado</h1>
          <p className="text-gray-600">Você não tem permissão para acessar este módulo.</p>
        </div>
      </div>
    )
  }

  async function loadData() {
    try {
      setLoading(true)
      const [profitsData, totalData] = await Promise.all([
        financialService.findAll(),
        financialService.getTotal(),
      ])
      setProfits(profitsData)
      setTotal(totalData.total)
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao carregar dados",
        description: "Não foi possível obter os dados financeiros.",
      })
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    loadData()
  }, [])

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Financeiro</h1>
        <p className="text-muted-foreground">
          Visão geral dos lucros do sistema.
        </p>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Lucro Total
            </CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {loading ? "..." : `R$ ${Number(total).toFixed(2)}`}
            </div>
            <p className="text-xs text-muted-foreground">
              Acumulado de todas as taxas
            </p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Histórico de Lucros</CardTitle>
          <CardDescription>
            Registro detalhado das taxas cobradas pelo sistema.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Data</TableHead>
                <TableHead>Origem</TableHead>
                <TableHead>Descrição</TableHead>
                <TableHead className="text-right">Valor</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {loading ? (
                <TableRow>
                  <TableCell colSpan={4} className="text-center py-8">
                    Carregando...
                  </TableCell>
                </TableRow>
              ) : profits.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={4} className="text-center py-8">
                    Nenhum registro encontrado.
                  </TableCell>
                </TableRow>
              ) : (
                profits.map((profit) => (
                  <TableRow key={profit.id}>
                    <TableCell>
                      {format(new Date(profit.createdAt), "dd/MM/yyyy HH:mm", { locale: ptBR })}
                    </TableCell>
                    <TableCell>
                      <Badge variant={sourceMap[profit.source]?.variant as any || "outline"}>
                        {sourceMap[profit.source]?.label || profit.source}
                      </Badge>
                    </TableCell>
                    <TableCell>{profit.description || "-"}</TableCell>
                    <TableCell className="text-right font-medium text-green-600">
                      + R$ {Number(profit.amount).toFixed(2)}
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
