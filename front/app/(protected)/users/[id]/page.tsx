"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { UserForm } from "@/components/users/user-form"
import { usersService, User } from "@/lib/services/users-service"
import { useToast } from "@/components/ui/use-toast"
import { Loader2 } from "lucide-react"

export default function EditUserPage() {
  const params = useParams()
  const router = useRouter()
  const { toast } = useToast()
  const [user, setUser] = useState<User | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function loadUser() {
      try {
        if (typeof params.id !== "string") return
        const data = await usersService.findOne(params.id)
        setUser(data)
      } catch (error) {
        toast({
          variant: "destructive",
          title: "Erro ao carregar",
          description: "Não foi possível carregar os dados do usuário.",
        })
        router.push("/users")
      } finally {
        setLoading(false)
      }
    }

    loadUser()
  }, [params.id, router, toast])

  if (loading) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
      </div>
    )
  }

  if (!user) return null

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Editar Usuário</h1>
        <p className="text-muted-foreground">
          Atualize os dados do usuário.
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Dados do Usuário</CardTitle>
        </CardHeader>
        <CardContent>
          <UserForm initialData={user} />
        </CardContent>
      </Card>
    </div>
  )
}
