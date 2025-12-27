"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { BankAccountForm } from "@/components/bank-accounts/bank-account-form"
import { bankAccountsService, BankAccount } from "@/lib/services/bank-accounts.service"
import { useToast } from "@/components/ui/use-toast"
import { Loader2 } from "lucide-react"

export default function EditBankAccountPage() {
  const params = useParams()
  const router = useRouter()
  const { toast } = useToast()
  const [account, setAccount] = useState<BankAccount | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function loadAccount() {
      try {
        const data = await bankAccountsService.findOne(params.id as string)
        setAccount(data)
      } catch (error) {
        toast({
          variant: "destructive",
          title: "Erro ao carregar",
          description: "Não foi possível carregar os dados da conta.",
        })
        router.push("/bank-accounts")
      } finally {
        setLoading(false)
      }
    }

    loadAccount()
  }, [params.id, router, toast])

  if (loading) {
    return (
      <div className="flex items-center justify-center h-96">
        <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Editar Conta Bancária</h1>
        <p className="text-muted-foreground">
          Atualize os dados da conta bancária.
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Dados da Conta</CardTitle>
        </CardHeader>
        <CardContent>
          <BankAccountForm initialData={account} />
        </CardContent>
      </Card>
    </div>
  )
}
