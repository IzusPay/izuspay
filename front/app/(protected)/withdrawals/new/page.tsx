import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { WithdrawalForm } from "@/components/withdrawals/withdrawal-form"

export default function NewWithdrawalPage() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Solicitar Saque</h1>
        <p className="text-muted-foreground">
          Transfira seu saldo para sua conta bancária.
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Dados do Saque</CardTitle>
        </CardHeader>
        <CardContent>
          <WithdrawalForm />
        </CardContent>
      </Card>
    </div>
  )
}
