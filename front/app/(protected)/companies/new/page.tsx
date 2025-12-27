import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { CompanyForm } from "@/components/companies/company-form"

export default function NewCompanyPage() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Nova Empresa</h1>
        <p className="text-muted-foreground">
          Cadastre uma nova empresa ou parceiro.
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Dados da Empresa</CardTitle>
        </CardHeader>
        <CardContent>
          <CompanyForm />
        </CardContent>
      </Card>
    </div>
  )
}
