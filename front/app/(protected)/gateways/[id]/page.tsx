"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { GatewayForm } from "@/components/gateways/gateway-form"
import { gatewaysService, Gateway } from "@/lib/services/gateways.service"
import { useToast } from "@/components/ui/use-toast"
import { Loader2 } from "lucide-react"

export default function EditGatewayPage() {
  const params = useParams()
  const router = useRouter()
  const { toast } = useToast()
  const [gateway, setGateway] = useState<Gateway | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function loadGateway() {
      try {
        if (typeof params.id !== "string") return
        const data = await gatewaysService.findOne(params.id)
        setGateway(data)
      } catch (error) {
        toast({
          variant: "destructive",
          title: "Erro ao carregar",
          description: "Não foi possível carregar os dados do gateway.",
        })
        router.push("/gateways")
      } finally {
        setLoading(false)
      }
    }

    loadGateway()
  }, [params.id, router, toast])

  if (loading) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
      </div>
    )
  }

  if (!gateway) return null

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Editar Gateway</h1>
        <p className="text-muted-foreground">
          Atualize as configurações da integração.
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Configuração do Gateway</CardTitle>
        </CardHeader>
        <CardContent>
          <GatewayForm initialData={gateway} />
        </CardContent>
      </Card>
    </div>
  )
}
