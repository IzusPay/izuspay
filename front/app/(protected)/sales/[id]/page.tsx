"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { format } from "date-fns"
import { ptBR } from "date-fns/locale"
import { Loader2, ArrowLeft, Copy, Check } from "lucide-react"

import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { useToast } from "@/components/ui/use-toast"
import { salesService, Sale, SaleStatus } from "@/lib/services/sales.service"

const statusMap: Record<SaleStatus, { label: string; variant: "default" | "secondary" | "destructive" | "outline" | "success" }> = {
  [SaleStatus.PENDING]: { label: "Pendente", variant: "outline" },
  [SaleStatus.PAID]: { label: "Pago", variant: "success" },
  [SaleStatus.FAILED]: { label: "Falhou", variant: "destructive" },
  [SaleStatus.REFUNDED]: { label: "Reembolsado", variant: "secondary" },
  [SaleStatus.EXPIRED]: { label: "Expirado", variant: "secondary" },
}

export default function SaleDetailsPage() {
  const params = useParams()
  const router = useRouter()
  const { toast } = useToast()
  const [sale, setSale] = useState<Sale | null>(null)
  const [loading, setLoading] = useState(true)
  const [copied, setCopied] = useState(false)

  useEffect(() => {
    async function loadSale() {
      try {
        if (typeof params.id !== "string") return
        const data = await salesService.findOne(params.id)
        setSale(data)
      } catch (error) {
        toast({
          variant: "destructive",
          title: "Erro ao carregar",
          description: "Não foi possível carregar os detalhes da venda.",
        })
        router.push("/sales")
      } finally {
        setLoading(false)
      }
    }

    loadSale()
  }, [params.id, router, toast])

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text)
    setCopied(true)
    setTimeout(() => setCopied(false), 2000)
    toast({
      title: "Copiado!",
      description: "Código PIX copiado para a área de transferência.",
    })
  }

  if (loading) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
      </div>
    )
  }

  if (!sale) return null

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <Button variant="outline" size="icon" onClick={() => router.back()}>
          <ArrowLeft className="h-4 w-4" />
        </Button>
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Detalhes da Venda</h1>
          <p className="text-muted-foreground">
            ID: {sale.id}
          </p>
        </div>
      </div>

      <div className="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Informações do Pagamento</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex justify-between items-center py-2 border-b">
              <span className="text-muted-foreground">Status</span>
              <Badge
                variant={statusMap[sale.status].variant as any}
                className={sale.status === SaleStatus.PAID ? "bg-green-500 hover:bg-green-600" : ""}
              >
                {statusMap[sale.status].label}
              </Badge>
            </div>
            
            <div className="flex justify-between items-center py-2 border-b">
              <span className="text-muted-foreground">Valor Bruto</span>
              <span className="font-medium">R$ {Number(sale.amount).toFixed(2)}</span>
            </div>

            <div className="flex justify-between items-center py-2 border-b">
              <span className="text-muted-foreground">Taxas</span>
              <span className="font-medium text-destructive">- R$ {Number(sale.fee).toFixed(2)}</span>
            </div>

            <div className="flex justify-between items-center py-2 border-b">
              <span className="text-muted-foreground">Valor Líquido</span>
              <span className="font-bold text-green-600">R$ {Number(sale.netAmount).toFixed(2)}</span>
            </div>

            <div className="flex justify-between items-center py-2 border-b">
              <span className="text-muted-foreground">Método</span>
              <span className="font-medium">{sale.paymentMethod}</span>
            </div>

            <div className="flex justify-between items-center py-2">
              <span className="text-muted-foreground">Data</span>
              <span className="font-medium">
                {format(new Date(sale.createdAt), "dd/MM/yyyy HH:mm:ss", { locale: ptBR })}
              </span>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Dados do Cliente</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex flex-col py-2 border-b">
              <span className="text-sm text-muted-foreground">Nome</span>
              <span className="font-medium">{sale.payerName}</span>
            </div>

            <div className="flex flex-col py-2 border-b">
              <span className="text-sm text-muted-foreground">Email</span>
              <span className="font-medium">{sale.payerEmail}</span>
            </div>

            <div className="flex flex-col py-2 border-b">
              <span className="text-sm text-muted-foreground">Documento</span>
              <span className="font-medium">{sale.payerDocument}</span>
            </div>

            {sale.payerPhone && (
              <div className="flex flex-col py-2">
                <span className="text-sm text-muted-foreground">Telefone</span>
                <span className="font-medium">{sale.payerPhone}</span>
              </div>
            )}
          </CardContent>
        </Card>

        {sale.status === SaleStatus.PENDING && sale.pixCode && (
          <Card className="md:col-span-2">
            <CardHeader>
              <CardTitle>Pagamento via PIX</CardTitle>
              <CardDescription>
                Escaneie o QR Code ou copie o código abaixo para pagar.
              </CardDescription>
            </CardHeader>
            <CardContent className="flex flex-col items-center space-y-6">
              {sale.pixQrCode && (
                <div className="bg-white p-4 rounded-lg shadow-sm">
                  {/* Assuming base64 image or URL */}
                  <img 
                    src={sale.pixQrCode.startsWith('http') ? sale.pixQrCode : `data:image/png;base64,${sale.pixQrCode}`} 
                    alt="QR Code PIX" 
                    className="w-48 h-48 object-contain"
                  />
                </div>
              )}

              <div className="w-full max-w-md space-y-2">
                <label className="text-sm font-medium text-muted-foreground">
                  Pix Copia e Cola
                </label>
                <div className="flex gap-2">
                  <div className="flex-1 p-3 bg-muted rounded-md font-mono text-xs break-all">
                    {sale.pixCode}
                  </div>
                  <Button
                    variant="outline"
                    size="icon"
                    className="shrink-0"
                    onClick={() => copyToClipboard(sale.pixCode!)}
                  >
                    {copied ? <Check className="h-4 w-4" /> : <Copy className="h-4 w-4" />}
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  )
}
