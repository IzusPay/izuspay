"use client"

import { useState } from "react"
import { useRouter } from "next/navigation"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import * as z from "zod"
import { Loader2 } from "lucide-react"

import { Button } from "@/components/ui/button"
import {
  Form,
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form"
import { Input } from "@/components/ui/input"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { useToast } from "@/components/ui/use-toast"
import { bankAccountsService, PixKeyType } from "@/lib/services/bank-accounts.service"

const bankAccountSchema = z.object({
  bankName: z.string().min(2, "Nome do banco obrigatório"),
  agency: z.string().min(1, "Agência obrigatória"),
  accountNumber: z.string().min(1, "Número da conta obrigatório"),
  accountDigit: z.string().min(1, "Dígito obrigatório"),
  pixKey: z.string().optional(),
  pixKeyType: z.nativeEnum(PixKeyType).optional(),
}).refine((data) => {
  if (data.pixKey && !data.pixKeyType) return false
  if (!data.pixKey && data.pixKeyType) return false
  return true
}, {
  message: "Preencha a chave e o tipo de chave PIX se desejar cadastrar",
  path: ["pixKey"],
})

type BankAccountFormValues = z.infer<typeof bankAccountSchema>

export function BankAccountForm({ initialData }: { initialData?: any }) {
  const router = useRouter()
  const { toast } = useToast()
  const [loading, setLoading] = useState(false)

  const form = useForm<BankAccountFormValues>({
    resolver: zodResolver(bankAccountSchema),
    defaultValues: {
      bankName: initialData?.bankName || "",
      agency: initialData?.agency || "",
      accountNumber: initialData?.accountNumber || "",
      accountDigit: initialData?.accountDigit || "",
      pixKey: initialData?.pixKey || "",
      pixKeyType: initialData?.pixKeyType || undefined,
    },
  })

  async function onSubmit(data: BankAccountFormValues) {
    try {
      setLoading(true)
      
      if (initialData?.id) {
        await bankAccountsService.update(initialData.id, data as any)
        toast({
          title: "Conta atualizada",
          description: "Conta bancária atualizada com sucesso.",
        })
      } else {
        await bankAccountsService.create(data as any)
        toast({
          title: "Conta cadastrada",
          description: "Conta bancária adicionada com sucesso.",
        })
      }
      
      router.push("/bank-accounts")
      router.refresh()
    } catch (error: any) {
      toast({
        variant: "destructive",
        title: initialData ? "Erro ao atualizar" : "Erro ao cadastrar",
        description: error.message || "Ocorreu um erro ao processar a conta.",
      })
    } finally {
      setLoading(false)
    }
  }

  return (
    <Form {...form}>
      <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-8">
        <div className="grid gap-4 md:grid-cols-2">
          <div className="col-span-2">
            <h3 className="text-lg font-medium mb-4">Dados Bancários</h3>
          </div>

          <FormField
            control={form.control}
            name="bankName"
            render={({ field }) => (
              <FormItem className="col-span-2">
                <FormLabel>Nome do Banco</FormLabel>
                <FormControl>
                  <Input placeholder="Ex: Banco do Brasil" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="agency"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Agência</FormLabel>
                <FormControl>
                  <Input placeholder="0000" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <div className="flex gap-4">
            <FormField
              control={form.control}
              name="accountNumber"
              render={({ field }) => (
                <FormItem className="flex-1">
                  <FormLabel>Conta</FormLabel>
                  <FormControl>
                    <Input placeholder="00000" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="accountDigit"
              render={({ field }) => (
                <FormItem className="w-24">
                  <FormLabel>Dígito</FormLabel>
                  <FormControl>
                    <Input placeholder="0" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          </div>

          <div className="col-span-2 mt-4">
            <h3 className="text-lg font-medium mb-4">Chave PIX (Opcional)</h3>
          </div>

          <FormField
            control={form.control}
            name="pixKeyType"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Tipo de Chave</FormLabel>
                <Select onValueChange={field.onChange} defaultValue={field.value}>
                  <FormControl>
                    <SelectTrigger>
                      <SelectValue placeholder="Selecione o tipo" />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    <SelectItem value={PixKeyType.CPF}>CPF</SelectItem>
                    <SelectItem value={PixKeyType.CNPJ}>CNPJ</SelectItem>
                    <SelectItem value={PixKeyType.EMAIL}>E-mail</SelectItem>
                    <SelectItem value={PixKeyType.PHONE}>Telefone</SelectItem>
                    <SelectItem value={PixKeyType.RANDOM}>Chave Aleatória</SelectItem>
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="pixKey"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Chave PIX</FormLabel>
                <FormControl>
                  <Input placeholder="Digite sua chave PIX" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
        </div>

        <div className="flex justify-end gap-4">
          <Button
            type="button"
            variant="outline"
            onClick={() => router.back()}
            disabled={loading}
          >
            Cancelar
          </Button>
          <Button type="submit" disabled={loading}>
            {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
            Salvar Conta
          </Button>
        </div>
      </form>
    </Form>
  )
}
