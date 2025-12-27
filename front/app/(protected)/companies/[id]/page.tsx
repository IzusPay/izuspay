"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { CompanyForm } from "@/components/companies/company-form"
import { companiesService, Company } from "@/lib/services/companies.service"
import { useToast } from "@/components/ui/use-toast"
import { Loader2 } from "lucide-react"

export default function EditCompanyPage() {
  const params = useParams()
  const router = useRouter()
  const { toast } = useToast()
  const [company, setCompany] = useState<Company | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function loadCompany() {
      try {
        if (typeof params.id !== "string") return
        const data = await companiesService.findOne(params.id)
        setCompany(data)
      } catch (error) {
        toast({
          variant: "destructive",
          title: "Erro ao carregar",
          description: "Não foi possível carregar os dados da empresa.",
        })
        router.push("/companies")
      } finally {
        setLoading(false)
      }
    }

    loadCompany()
  }, [params.id, router, toast])

  if (loading) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
      </div>
    )
  }

  if (!company) return null

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Editar Empresa</h1>
        <p className="text-muted-foreground">
          Atualize os dados da empresa.
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Dados da Empresa</CardTitle>
        </CardHeader>
        <CardContent>
          <CompanyForm initialData={company} />
        </CardContent>
      </Card>
    </div>
  )
}
