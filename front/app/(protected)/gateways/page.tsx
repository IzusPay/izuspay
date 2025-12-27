"use client"

import { useEffect, useState } from "react"
import Link from "next/link"
import { Plus, Edit, Trash2 } from "lucide-react"
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
import { Switch } from "@/components/ui/switch"
import { gatewaysService, Gateway } from "@/lib/services/gateways.service"
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

export default function GatewaysPage() {
  const [gateways, setGateways] = useState<Gateway[]>([])
  const [loading, setLoading] = useState(true)
  const [deleteId, setDeleteId] = useState<string | null>(null)
  const { toast } = useToast()
  const { user } = useAuth()

  // Verify Read Permission
  if (user?.role !== "admin" && !user?.permissions?.["gateways"]?.canRead) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-red-600">Acesso Negado</h1>
          <p className="text-gray-600">Você não tem permissão para acessar este módulo.</p>
        </div>
      </div>
    )
  }

  async function loadGateways() {
    try {
      setLoading(true)
      const data = await gatewaysService.findAll()
      setGateways(data)
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao carregar gateways",
        description: "Não foi possível obter a lista de gateways.",
      })
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    loadGateways()
  }, [])

  async function handleDelete() {
    if (!deleteId) return

    try {
      await gatewaysService.delete(deleteId)
      toast({
        title: "Gateway excluído",
        description: "O gateway foi excluído com sucesso.",
      })
      loadGateways()
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao excluir",
        description: "Não foi possível excluir o gateway.",
      })
    } finally {
      setDeleteId(null)
    }
  }

  async function toggleStatus(gateway: Gateway) {
    try {
      await gatewaysService.update(gateway.id, {
        isActive: !gateway.isActive,
      })
      setGateways(gateways.map(g => 
        g.id === gateway.id ? { ...g, isActive: !g.isActive } : g
      ))
      toast({
        title: "Status atualizado",
        description: `Gateway ${!gateway.isActive ? "ativado" : "desativado"} com sucesso.`,
      })
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao atualizar",
        description: "Não foi possível alterar o status do gateway.",
      })
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Gateways</h1>
          <p className="text-muted-foreground">
            Gerencie as integrações de pagamento.
          </p>
        </div>
        {(user?.role === "admin" || user?.permissions?.["gateways"]?.canCreate) && (
          <Button asChild>
            <Link href="/gateways/new">
              <Plus className="mr-2 h-4 w-4" /> Novo Gateway
            </Link>
          </Button>
        )}
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Gateways Cadastrados</CardTitle>
          <CardDescription>
            Lista de gateways de pagamento configurados no sistema.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Nome</TableHead>
                <TableHead>Tipo</TableHead>
                <TableHead>Prioridade</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Taxas (Tx/Custo)</TableHead>
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
              ) : gateways.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} className="text-center py-8">
                    Nenhum gateway encontrado.
                  </TableCell>
                </TableRow>
              ) : (
                gateways.map((gateway) => (
                  <TableRow key={gateway.id}>
                    <TableCell className="font-medium">{gateway.name}</TableCell>
                    <TableCell>
                      <Badge variant="outline">{gateway.type?.name || "N/A"}</Badge>
                    </TableCell>
                    <TableCell>{gateway.priority}</TableCell>
                    <TableCell>
                      <Switch
                        checked={gateway.isActive}
                        onCheckedChange={() => toggleStatus(gateway)}
                        disabled={user?.role !== "admin" && !user?.permissions?.["gateways"]?.canUpdate}
                      />
                    </TableCell>
                    <TableCell>
                      <div className="flex flex-col text-sm">
                        <span>Tx: {gateway.transactionFeePercentage}% + R$ {gateway.transactionFeeFixed}</span>
                        <span className="text-muted-foreground">Custo: {gateway.costFeePercentage}% + R$ {gateway.costFeeFixed}</span>
                      </div>
                    </TableCell>
                    <TableCell className="text-right">
                      <div className="flex justify-end gap-2">
                        {(user?.role === "admin" || user?.permissions?.["gateways"]?.canUpdate) && (
                          <Button variant="ghost" size="icon" asChild>
                            <Link href={`/gateways/${gateway.id}`}>
                              <Edit className="h-4 w-4" />
                            </Link>
                          </Button>
                        )}
                        {(user?.role === "admin" || user?.permissions?.["gateways"]?.canDelete) && (
                          <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => setDeleteId(gateway.id)}
                          >
                            <Trash2 className="h-4 w-4 text-destructive" />
                          </Button>
                        )}
                      </div>
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
              Esta ação não pode ser desfeita. Isso excluirá permanentemente o gateway
              e poderá afetar transações em andamento.
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
