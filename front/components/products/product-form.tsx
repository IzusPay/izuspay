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
import { Textarea } from "@/components/ui/textarea"
import { Switch } from "@/components/ui/switch"
import { useToast } from "@/components/ui/use-toast"
import { Product, productsService } from "@/lib/services/products.service"
import { useAuth } from "@/contexts/auth-context"

const productSchema = z.object({
  name: z.string().min(2, "Nome deve ter pelo menos 2 caracteres"),
  productName: z.string().min(2, "Nome público deve ter pelo menos 2 caracteres"),
  description: z.string().optional(),
  amount: z.coerce.number().min(0.01, "Valor deve ser maior que zero"),
  active: z.boolean().default(true),
  paymentMethods: z.array(z.string()).default(["PIX"]), // Assuming PIX for now, can be expanded
})

type ProductFormValues = z.infer<typeof productSchema>

interface ProductFormProps {
  initialData?: Product
}

export function ProductForm({ initialData }: ProductFormProps) {
  const router = useRouter()
  const { toast } = useToast()
  const { user } = useAuth()
  const [loading, setLoading] = useState(false)

  const form = useForm<ProductFormValues>({
    resolver: zodResolver(productSchema),
    defaultValues: {
      name: initialData?.name || "",
      productName: initialData?.productName || "",
      description: initialData?.description || "",
      amount: initialData?.amount || 0,
      active: initialData?.active ?? true,
      paymentMethods: initialData?.paymentMethods || ["PIX"],
    },
  })

  async function onSubmit(data: ProductFormValues) {
    try {
      setLoading(true)
      
      const payload = {
        ...data,
        companyId: user?.companyId || "",
      }

      if (initialData) {
        await productsService.update(initialData.id, payload)
        toast({
          title: "Produto atualizado",
          description: "Os dados do produto foram salvos com sucesso.",
        })
      } else {
        await productsService.create(payload as any)
        toast({
          title: "Produto criado",
          description: "O novo produto foi criado com sucesso.",
        })
      }
      
      router.push("/products")
      router.refresh()
    } catch (error: any) {
      toast({
        variant: "destructive",
        title: "Erro ao salvar",
        description: error.message || "Ocorreu um erro ao salvar o produto.",
      })
    } finally {
      setLoading(false)
    }
  }

  return (
    <Form {...form}>
      <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-8">
        <div className="grid gap-4 md:grid-cols-2">
          <FormField
            control={form.control}
            name="name"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Nome Interno</FormLabel>
                <FormControl>
                  <Input placeholder="Identificador interno" {...field} />
                </FormControl>
                <FormDescription>
                  Nome usado para controle interno da empresa.
                </FormDescription>
                <FormMessage />
              </FormItem>
            )}
          />
          
          <FormField
            control={form.control}
            name="productName"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Nome Público</FormLabel>
                <FormControl>
                  <Input placeholder="Nome exibido no checkout" {...field} />
                </FormControl>
                <FormDescription>
                  Nome que aparecerá para o cliente final.
                </FormDescription>
                <FormMessage />
              </FormItem>
            )}
          />
          
          <FormField
            control={form.control}
            name="amount"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Valor (R$)</FormLabel>
                <FormControl>
                  <Input type="number" step="0.01" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="active"
            render={({ field }) => (
              <FormItem className="flex flex-row items-center justify-between rounded-lg border p-4 shadow-sm">
                <div className="space-y-0.5">
                  <FormLabel className="text-base">Ativo</FormLabel>
                  <FormDescription>
                    Produtos inativos não podem ser vendidos.
                  </FormDescription>
                </div>
                <FormControl>
                  <Switch
                    checked={field.value}
                    onCheckedChange={field.onChange}
                  />
                </FormControl>
              </FormItem>
            )}
          />
        </div>

        <FormField
          control={form.control}
          name="description"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Descrição</FormLabel>
              <FormControl>
                <Textarea 
                  placeholder="Descrição detalhada do produto..." 
                  className="resize-none" 
                  {...field} 
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

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
            {initialData ? "Salvar Alterações" : "Criar Produto"}
          </Button>
        </div>
      </form>
    </Form>
  )
}
