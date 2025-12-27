import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { UserForm } from "@/components/users/user-form"

export default function NewUserPage() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Novo Usuário</h1>
        <p className="text-muted-foreground">
          Crie um novo usuário para acessar o sistema.
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Dados do Usuário</CardTitle>
        </CardHeader>
        <CardContent>
          <UserForm />
        </CardContent>
      </Card>
    </div>
  )
}
