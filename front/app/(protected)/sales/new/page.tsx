import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { SaleForm } from "@/components/sales/sale-form"

export default function NewSalePage() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Nova Venda</h1>
        <p className="text-muted-foreground">
          Gere uma nova cobrança manualmente.
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Dados da Venda</CardTitle>
        </CardHeader>
        <CardContent>
          <SaleForm />
        </CardContent>
      </Card>
    </div>
  )
}
