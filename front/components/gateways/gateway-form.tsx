"use client"

import { useState, useEffect } from "react"
import { useRouter } from "next/navigation"
import { useForm, useFieldArray } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import * as z from "zod"
import { Loader2, Plus, Trash2 } from "lucide-react"

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
import { gatewaysService, Gateway, GatewayType } from "@/lib/services/gateways.service"

const gatewaySchema = z.object({
  name: z.string().min(2, "Nome deve ter pelo menos 2 caracteres"),
  apiUrl: z.string().url("URL inválida"),
  typeId: z.string().optional(),
  priority: z.coerce.number().min(0, "Prioridade deve ser positiva"),
  isActive: z.boolean().default(true),
  transactionFeePercentage: z.coerce.number().min(0),
  transactionFeeFixed: z.coerce.number().min(0),
  costFeePercentage: z.coerce.number().min(0),
  costFeeFixed: z.coerce.number().min(0),
  params: z.array(z.object({
    label: z.string().min(1, "Chave obrigatória"),
    value: z.string().min(1, "Valor obrigatório"),
  })),
})

type GatewayFormValues = z.infer<typeof gatewaySchema>

interface GatewayFormProps {
  initialData?: Gateway
}

export function GatewayForm({ initialData }: GatewayFormProps) {
  const router = useRouter()
  const { toast } = useToast()
  const [loading, setLoading] = useState(false)
  const [types, setTypes] = useState<GatewayType[]>([])

  const form = useForm<GatewayFormValues>({
    resolver: zodResolver(gatewaySchema),
    defaultValues: initialData ? {
      name: initialData.name,
      apiUrl: initialData.apiUrl,
      typeId: initialData.typeId,
      priority: initialData.priority,
      isActive: initialData.isActive,
      transactionFeePercentage: Number(initialData.transactionFeePercentage),
      transactionFeeFixed: Number(initialData.transactionFeeFixed),
      costFeePercentage: Number(initialData.costFeePercentage),
      costFeeFixed: Number(initialData.costFeeFixed),
      params: initialData.params || [],
    } : {
      name: "",
      apiUrl: "",
      priority: 0,
      isActive: true,
      transactionFeePercentage: 0,
      transactionFeeFixed: 0,
      costFeePercentage: 0,
      costFeeFixed: 0,
      params: [],
    },
  })

  const { fields, append, remove } = useFieldArray({
    control: form.control,
    name: "params",
  })

  useEffect(() => {
    gatewaysService.findAllTypes().then(setTypes).catch(console.error)
  }, [])

  async function onSubmit(data: GatewayFormValues) {
    try {
      setLoading(true)
      
      if (initialData) {
        await gatewaysService.update(initialData.id, data as any)
        toast({
          title: "Gateway atualizado",
          description: "As informações foram salvas com sucesso.",
        })
      } else {
        await gatewaysService.create(data as any)
        toast({
          title: "Gateway criado",
          description: "O novo gateway foi cadastrado com sucesso.",
        })
      }
      
      router.push("/gateways")
      router.refresh()
    } catch (error: any) {
      toast({
        variant: "destructive",
        title: "Erro ao salvar",
        description: error.message || "Ocorreu um erro ao salvar o gateway.",
      })
    } finally {
      setLoading(false)
    }
  }

  return (
    <Form {...form}>
      <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-8">
        <div className="grid gap-6 md:grid-cols-2">
          {/* Basic Info */}
          <div className="space-y-4">
            <h3 className="text-lg font-medium">Informações Básicas</h3>
            <FormField
              control={form.control}
              name="name"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Nome</FormLabel>
                  <FormControl>
                    <Input placeholder="Ex: PagSeguro" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="typeId"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Tipo de Gateway</FormLabel>
                  <Select onValueChange={field.onChange} defaultValue={field.value}>
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Selecione o tipo" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      {types.map((type) => (
                        <SelectItem key={type.id} value={type.id}>
                          {type.name}
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
              name="apiUrl"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>URL da API</FormLabel>
                  <FormControl>
                    <Input placeholder="https://api.gateway.com" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <div className="flex gap-4">
              <FormField
                control={form.control}
                name="priority"
                render={({ field }) => (
                  <FormItem className="flex-1">
                    <FormLabel>Prioridade</FormLabel>
                    <FormControl>
                      <Input type="number" {...field} />
                    </FormControl>
                    <FormDescription>Maior = mais prioritário</FormDescription>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="isActive"
                render={({ field }) => (
                  <FormItem className="space-y-3">
                    <FormLabel>Status</FormLabel>
                    <FormControl>
                      <RadioGroup
                        onValueChange={(value) => field.onChange(value === "true")}
                        defaultValue={field.value ? "true" : "false"}
                        className="flex flex-col space-y-1"
                      >
                        <FormItem className="flex items-center space-x-3 space-y-0">
                          <FormControl>
                            <RadioGroupItem value="true" />
                          </FormControl>
                          <FormLabel className="font-normal">
                            Ativo
                          </FormLabel>
                        </FormItem>
                        <FormItem className="flex items-center space-x-3 space-y-0">
                          <FormControl>
                            <RadioGroupItem value="false" />
                          </FormControl>
                          <FormLabel className="font-normal">
                            Inativo
                          </FormLabel>
                        </FormItem>
                      </RadioGroup>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>
          </div>

          {/* Fees */}
          <div className="space-y-4">
            <h3 className="text-lg font-medium">Taxas e Custos</h3>
            <div className="grid grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="transactionFeePercentage"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Taxa (%)</FormLabel>
                    <FormControl>
                      <Input type="number" step="0.01" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="transactionFeeFixed"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Taxa Fixa (R$)</FormLabel>
                    <FormControl>
                      <Input type="number" step="0.01" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="costFeePercentage"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Custo (%)</FormLabel>
                    <FormControl>
                      <Input type="number" step="0.01" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="costFeeFixed"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Custo Fixo (R$)</FormLabel>
                    <FormControl>
                      <Input type="number" step="0.01" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>
          </div>
        </div>

        {/* Parameters */}
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="text-lg font-medium">Parâmetros de Configuração</h3>
            <Button
              type="button"
              variant="outline"
              size="sm"
              onClick={() => append({ label: "", value: "" })}
            >
              <Plus className="mr-2 h-4 w-4" /> Adicionar Parâmetro
            </Button>
          </div>
          
          {fields.length === 0 && (
            <div className="text-sm text-muted-foreground text-center py-4 border border-dashed rounded-lg">
              Nenhum parâmetro configurado.
            </div>
          )}

          {fields.map((field, index) => (
            <div key={field.id} className="flex gap-4 items-start">
              <FormField
                control={form.control}
                name={`params.${index}.label`}
                render={({ field }) => (
                  <FormItem className="flex-1">
                    <FormControl>
                      <Input placeholder="Chave (ex: api_key)" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name={`params.${index}.value`}
                render={({ field }) => (
                  <FormItem className="flex-1">
                    <FormControl>
                      <Input placeholder="Valor" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <Button
                type="button"
                variant="ghost"
                size="icon"
                onClick={() => remove(index)}
              >
                <Trash2 className="h-4 w-4 text-destructive" />
              </Button>
            </div>
          ))}
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
            Salvar Gateway
          </Button>
        </div>
      </form>
    </Form>
  )
}
