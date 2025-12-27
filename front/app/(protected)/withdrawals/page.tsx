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
import { withdrawalsService, Withdrawal, WithdrawalStatus, WithdrawalMethod } from "@/lib/services/withdrawals.service"
import { useToast } from "@/components/ui/use-toast"
import { format } from "date-fns"
import { ptBR } from "date-fns/locale"
import { useAuth } from "@/contexts/auth-context"

const statusMap: Record<WithdrawalStatus, { label: string; variant: "default" | "secondary" | "destructive" | "outline" | "success" }> = {
  [WithdrawalStatus.PENDING]: { label: "Pendente", variant: "outline" },
  [WithdrawalStatus.PROCESSING]: { label: "Processando", variant: "secondary" },
  [WithdrawalStatus.COMPLETED]: { label: "Concluído", variant: "success" },
  [WithdrawalStatus.REJECTED]: { label: "Rejeitado", variant: "destructive" },
  [WithdrawalStatus.FAILED]: { label: "Falhou", variant: "destructive" },
}

export default function WithdrawalsPage() {
  const [withdrawals, setWithdrawals] = useState<Withdrawal[]>([])
  const [loading, setLoading] = useState(true)
  const { toast } = useToast()
  const { user } = useAuth()

  // Verify Read Permission
  if (user?.role !== "admin" && !user?.permissions?.["withdrawals"]?.canRead) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-red-600">Acesso Negado</h1>
          <p className="text-gray-600">Você não tem permissão para acessar este módulo.</p>
        </div>
      </div>
    )
  }

  async function loadWithdrawals() {
    try {
      setLoading(true)
      const data = await withdrawalsService.findAll()
      setWithdrawals(data)
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao carregar saques",
        description: "Não foi possível obter a lista de saques.",
      })
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    loadWithdrawals()
  }, [])

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Saques</h1>
          <p className="text-muted-foreground">
            Gerencie suas solicitações de retirada.
          </p>
        </div>
        {(user?.role === "admin" || user?.permissions?.["withdrawals"]?.canCreate) && (
          <Button asChild>
            <Link href="/withdrawals/new">
              <Plus className="mr-2 h-4 w-4" /> Solicitar Saque
            </Link>
          </Button>
        )}
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Histórico de Saques</CardTitle>
          <CardDescription>
            Lista de todas as solicitações de saque realizadas.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Data</TableHead>
                <TableHead>Valor</TableHead>
                <TableHead>Taxa</TableHead>
                <TableHead>Método</TableHead>
                <TableHead>Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {loading ? (
                <TableRow>
                  <TableCell colSpan={5} className="text-center py-8">
                    Carregando...
                  </TableCell>
                </TableRow>
              ) : withdrawals.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={5} className="text-center py-8">
                    Nenhum saque encontrado.
                  </TableCell>
                </TableRow>
              ) : (
                withdrawals.map((withdrawal) => (
                  <TableRow key={withdrawal.id}>
                    <TableCell>
                      {format(new Date(withdrawal.createdAt), "dd/MM/yyyy HH:mm", { locale: ptBR })}
                    </TableCell>
                    <TableCell className="font-medium">
                      R$ {Number(withdrawal.amount).toFixed(2)}
                    </TableCell>
                    <TableCell className="text-muted-foreground">
                      R$ {Number(withdrawal.fee).toFixed(2)}
                    </TableCell>
                    <TableCell>
                      {withdrawal.method === WithdrawalMethod.BANK_ACCOUNT ? "Conta Bancária" : "PIX"}
                    </TableCell>
                    <TableCell>
                      <Badge
                        variant={statusMap[withdrawal.status].variant as any}
                        className={withdrawal.status === WithdrawalStatus.COMPLETED ? "bg-green-500 hover:bg-green-600" : ""}
                      >
                        {statusMap[withdrawal.status].label}
                      </Badge>
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
