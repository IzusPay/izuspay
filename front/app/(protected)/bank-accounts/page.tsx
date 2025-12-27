"use client"

import { useEffect, useState } from "react"
import Link from "next/link"
import { Plus, Trash2, Edit } from "lucide-react"
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
import { bankAccountsService, BankAccount } from "@/lib/services/bank-accounts.service"
import { useToast } from "@/components/ui/use-toast"
import { useAuth } from "@/contexts/auth-context"
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog"

export default function BankAccountsPage() {
  const [accounts, setAccounts] = useState<BankAccount[]>([])
  const [loading, setLoading] = useState(true)
  const [deleteId, setDeleteId] = useState<string | null>(null)
  const { toast } = useToast()
  const { user } = useAuth()

  // Verify Read Permission
  if (user?.role !== "admin" && !user?.permissions?.["bank_accounts"]?.canRead) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-red-600">Acesso Negado</h1>
          <p className="text-gray-600">Você não tem permissão para acessar este módulo.</p>
        </div>
      </div>
    )
  }

  async function loadAccounts() {
    try {
      setLoading(true)
      const data = await bankAccountsService.findAll()
      setAccounts(data)
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao carregar contas",
        description: "Não foi possível obter a lista de contas bancárias.",
      })
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    loadAccounts()
  }, [])

  async function handleDelete() {
    if (!deleteId) return

    try {
      await bankAccountsService.delete(deleteId)
      toast({
        title: "Conta excluída",
        description: "A conta bancária foi removida com sucesso.",
      })
      loadAccounts()
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao excluir",
        description: "Não foi possível excluir a conta bancária.",
      })
    } finally {
      setDeleteId(null)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Contas Bancárias</h1>
          <p className="text-muted-foreground">
            Gerencie suas contas para recebimento de saques.
          </p>
        </div>
        {(user?.role === "admin" || user?.permissions?.["bank_accounts"]?.canCreate) && (
          <Button asChild>
            <Link href="/bank-accounts/new">
              <Plus className="mr-2 h-4 w-4" /> Nova Conta
            </Link>
          </Button>
        )}
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Minhas Contas</CardTitle>
          <CardDescription>
            Contas cadastradas para transferência de saldo.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Banco</TableHead>
                <TableHead>Agência/Conta</TableHead>
                <TableHead>Chave PIX</TableHead>
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
              ) : accounts.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={4} className="text-center py-8">
                    Nenhuma conta encontrada.
                  </TableCell>
                </TableRow>
              ) : (
                accounts.map((account) => (
                  <TableRow key={account.id}>
                    <TableCell className="font-medium">{account.bankName}</TableCell>
                    <TableCell>
                      Ag: {account.agency} / CC: {account.accountNumber}-{account.accountDigit}
                    </TableCell>
                    <TableCell>
                      {account.pixKey ? (
                        <span className="text-muted-foreground text-sm">
                          {account.pixKeyType?.toUpperCase()}: {account.pixKey}
                        </span>
                      ) : (
                        "-"
                      )}
                    </TableCell>
                    <TableCell className="text-right space-x-2">
                      {(user?.role === "admin" || user?.permissions?.["bank_accounts"]?.canUpdate) && (
                        <Button
                          variant="ghost"
                          size="icon"
                          asChild
                        >
                          <Link href={`/bank-accounts/${account.id}`}>
                            <Edit className="h-4 w-4" />
                          </Link>
                        </Button>
                      )}
                      {(user?.role === "admin" || user?.permissions?.["bank_accounts"]?.canDelete) && (
                        <Button
                          variant="ghost"
                          size="icon"
                          onClick={() => setDeleteId(account.id)}
                        >
                          <Trash2 className="h-4 w-4 text-destructive" />
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

      <AlertDialog open={!!deleteId} onOpenChange={() => setDeleteId(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Tem certeza?</AlertDialogTitle>
            <AlertDialogDescription>
              Esta ação não pode ser desfeita. Isso removerá permanentemente a conta bancária.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancelar</AlertDialogCancel>
            <AlertDialogAction onClick={handleDelete} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">
              Excluir
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  )
}
