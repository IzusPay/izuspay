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
import { useToast } from "@/components/ui/use-toast"
import { salesService } from "@/lib/services/sales.service"
import { productsService, Product } from "@/lib/services/products.service"

const saleSchema = z.object({
  productId: z.string().min(1, "Selecione um produto"),
  amount: z.coerce.number().min(0.01, "Valor deve ser maior que zero"),
  payerName: z.string().min(2, "Nome deve ter pelo menos 2 caracteres"),
  payerEmail: z.string().email("Email inválido"),
  payerDocument: z.string().min(11, "Documento inválido"),
  payerPhone: z.string().optional(),
})

type SaleFormValues = z.infer<typeof saleSchema>

export function SaleForm() {
  const router = useRouter()
  const { toast } = useToast()
  const [loading, setLoading] = useState(false)
  const [products, setProducts] = useState<Product[]>([])

  useEffect(() => {
    productsService.findAll().then(setProducts).catch(console.error)
  }, [])

  const form = useForm<SaleFormValues>({
    resolver: zodResolver(saleSchema),
    defaultValues: {
      productId: "",
      amount: 0,
      payerName: "",
      payerEmail: "",
      payerDocument: "",
      payerPhone: "",
    },
  })

  // Update amount when product is selected
  const handleProductChange = (productId: string) => {
    const product = products.find(p => p.id === productId)
    if (product) {
      form.setValue("amount", Number(product.amount))
    }
    form.setValue("productId", productId)
  }

  async function onSubmit(data: SaleFormValues) {
    try {
      setLoading(true)
      
      await salesService.create({
        ...data,
        paymentMethod: "PIX", // Default for manual sales
      } as any)
      
      toast({
        title: "Venda criada",
        description: "A venda foi criada com sucesso.",
      })
      
      router.push("/sales")
      router.refresh()
    } catch (error: any) {
      toast({
        variant: "destructive",
        title: "Erro ao criar",
        description: error.message || "Ocorreu um erro ao criar a venda.",
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
            name="productId"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Produto</FormLabel>
                <Select onValueChange={handleProductChange} defaultValue={field.value}>
                  <FormControl>
                    <SelectTrigger>
                      <SelectValue placeholder="Selecione um produto" />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    {products.map((product) => (
                      <SelectItem key={product.id} value={product.id}>
                        {product.productName}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
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
                <FormDescription>
                  Valor a ser cobrado.
                </FormDescription>
                <FormMessage />
              </FormItem>
            )}
          />
          
          <div className="col-span-2">
            <h3 className="mb-4 text-lg font-medium">Dados do Pagador</h3>
          </div>

          <FormField
            control={form.control}
            name="payerName"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Nome Completo</FormLabel>
                <FormControl>
                  <Input placeholder="Nome do cliente" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="payerEmail"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Email</FormLabel>
                <FormControl>
                  <Input placeholder="cliente@email.com" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="payerDocument"
            render={({ field }) => (
              <FormItem>
                <FormLabel>CPF/CNPJ</FormLabel>
                <FormControl>
                  <Input placeholder="000.000.000-00" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="payerPhone"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Telefone</FormLabel>
                <FormControl>
                  <Input placeholder="(11) 99999-9999" {...field} />
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
            Gerar Cobrança
          </Button>
        </div>
      </form>
    </Form>
  )
}
