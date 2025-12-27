"use client"

import { useState, useEffect } from "react"
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
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group"
import { useToast } from "@/components/ui/use-toast"
import { withdrawalsService, WithdrawalMethod, PixKeyType } from "@/lib/services/withdrawals.service"
import { bankAccountsService, BankAccount } from "@/lib/services/bank-accounts.service"

const withdrawalSchema = z.object({
  amount: z.coerce.number().min(5, "Valor mínimo de R$ 5,00"),
  method: z.nativeEnum(WithdrawalMethod),
  bankAccountId: z.string().optional(),
  pixKey: z.string().optional(),
  pixKeyType: z.nativeEnum(PixKeyType).optional(),
}).refine((data) => {
  if (data.method === WithdrawalMethod.BANK_ACCOUNT) {
    return !!data.bankAccountId
  }
  if (data.method === WithdrawalMethod.PIX) {
    return !!data.pixKey && !!data.pixKeyType
  }
  return false
}, {
  message: "Preencha os dados do método de saque selecionado",
  path: ["method"],
})

type WithdrawalFormValues = z.infer<typeof withdrawalSchema>

export function WithdrawalForm() {
  const router = useRouter()
  const { toast } = useToast()
  const [loading, setLoading] = useState(false)
  const [bankAccounts, setBankAccounts] = useState<BankAccount[]>([])

  useEffect(() => {
    bankAccountsService.findAll().then(setBankAccounts).catch(console.error)
  }, [])

  const form = useForm<WithdrawalFormValues>({
    resolver: zodResolver(withdrawalSchema),
    defaultValues: {
      amount: 0,
      method: WithdrawalMethod.BANK_ACCOUNT,
    },
  })

  const method = form.watch("method")

  async function onSubmit(data: WithdrawalFormValues) {
    try {
      setLoading(true)
      
      await withdrawalsService.request(data as any)
      
      toast({
        title: "Solicitação enviada",
        description: "Seu pedido de saque foi realizado com sucesso.",
      })
      
      router.push("/withdrawals")
      router.refresh()
    } catch (error: any) {
      toast({
        variant: "destructive",
        title: "Erro ao solicitar",
        description: error.message || "Ocorreu um erro ao solicitar o saque.",
      })
    } finally {
      setLoading(false)
    }
  }

  return (
    <Form {...form}>
      <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-8">
        <FormField
          control={form.control}
          name="amount"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Valor do Saque (R$)</FormLabel>
              <FormControl>
                <Input type="number" step="0.01" placeholder="0.00" {...field} />
              </FormControl>
              <FormDescription>
                Valor disponível para saque.
              </FormDescription>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={form.control}
          name="method"
          render={({ field }) => (
            <FormItem className="space-y-3">
              <FormLabel>Método de Recebimento</FormLabel>
              <FormControl>
                <RadioGroup
                  onValueChange={field.onChange}
                  defaultValue={field.value}
                  className="flex flex-col space-y-1"
                >
                  <FormItem className="flex items-center space-x-3 space-y-0">
                    <FormControl>
                      <RadioGroupItem value={WithdrawalMethod.BANK_ACCOUNT} />
                    </FormControl>
                    <FormLabel className="font-normal">
                      Conta Bancária Salva
                    </FormLabel>
                  </FormItem>
                  <FormItem className="flex items-center space-x-3 space-y-0">
                    <FormControl>
                      <RadioGroupItem value={WithdrawalMethod.PIX} />
                    </FormControl>
                    <FormLabel className="font-normal">
                      Chave PIX
                    </FormLabel>
                  </FormItem>
                </RadioGroup>
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        {method === WithdrawalMethod.BANK_ACCOUNT && (
          <FormField
            control={form.control}
            name="bankAccountId"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Selecione a Conta</FormLabel>
                <Select onValueChange={field.onChange} defaultValue={field.value}>
                  <FormControl>
                    <SelectTrigger>
                      <SelectValue placeholder="Selecione uma conta bancária" />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    {bankAccounts.map((account) => (
                      <SelectItem key={account.id} value={account.id}>
                        {account.bankName} - Ag: {account.agency} CC: {account.accountNumber}-{account.accountDigit}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {bankAccounts.length === 0 && (
                  <FormDescription className="text-destructive">
                    Nenhuma conta bancária cadastrada. Cadastre uma conta antes de solicitar o saque.
                  </FormDescription>
                )}
                <FormMessage />
              </FormItem>
            )}
          />
        )}

        {method === WithdrawalMethod.PIX && (
          <div className="grid gap-4 md:grid-cols-2">
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
        )}

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
            Solicitar Saque
          </Button>
        </div>
      </form>
    </Form>
  )
}
