"use client"

import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { AlertTriangle, Search } from "lucide-react"

export default function DisputasPage() {
  return (
    <>
      <div className="space-y-6">
        <h1 className="text-2xl font-semibold">Disputas</h1>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Card className="p-6">
            <div className="text-sm text-muted-foreground mb-2">Porcentagem em disputa</div>
            <div className="text-4xl font-bold">0,00%</div>
          </Card>

          <Card className="p-6">
            <div className="mb-4">
              <h3 className="font-semibold">API de disputas</h3>
              <p className="text-sm text-muted-foreground">Otimize respostas com a nossa API exclusiva.</p>
            </div>
            <div className="flex gap-2">
              <Button className="bg-black text-white hover:bg-gray-800">Acessar a documentação</Button>
              <Button variant="outline">Guia de disputas</Button>
            </div>
          </Card>
        </div>

        <Card className="p-6">
          <div className="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div className="flex items-start gap-2">
              <div className="text-blue-600 mt-1">ℹ️</div>
              <div className="text-sm text-blue-900">Acesse o nosso material educativo</div>
            </div>
          </div>

          <Tabs defaultValue="disputas">
            <TabsList>
              <TabsTrigger value="disputas">Disputas</TabsTrigger>
              <TabsTrigger value="chargeback">Chargeback</TabsTrigger>
            </TabsList>

            <TabsContent value="disputas" className="space-y-4">
              <div className="flex gap-4">
                <div className="relative flex-1">
                  <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input placeholder="Digite o código da transação" className="pl-9" />
                </div>
                <Button className="bg-black text-white hover:bg-gray-800">Aplicar Filtros</Button>
              </div>

              <div className="flex flex-col items-center justify-center py-16">
                <AlertTriangle className="h-16 w-16 text-gray-300 mb-4" />
                <p className="text-lg text-muted-foreground">Nenhuma disputa encontrada</p>
              </div>
            </TabsContent>

            <TabsContent value="chargeback">
              <div className="flex flex-col items-center justify-center py-16">
                <AlertTriangle className="h-16 w-16 text-gray-300 mb-4" />
                <p className="text-lg text-muted-foreground">Nenhum chargeback encontrado</p>
              </div>
            </TabsContent>
          </Tabs>
        </Card>
      </div>
    </>
  )
}
