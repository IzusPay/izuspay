import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { BankAccountForm } from "@/components/bank-accounts/bank-account-form"

export default function NewBankAccountPage() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Nova Conta Bancária</h1>
        <p className="text-muted-foreground">
          Cadastre uma nova conta para recebimento de saques.
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Dados da Conta</CardTitle>
        </CardHeader>
        <CardContent>
          <BankAccountForm />
        </CardContent>
      </Card>
    </div>
  )
}
