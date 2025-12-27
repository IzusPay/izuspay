import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { GatewayForm } from "@/components/gateways/gateway-form"

export default function NewGatewayPage() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Novo Gateway</h1>
        <p className="text-muted-foreground">
          Configure uma nova integração de pagamento.
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Configuração do Gateway</CardTitle>
        </CardHeader>
        <CardContent>
          <GatewayForm />
        </CardContent>
      </Card>
    </div>
  )
}
