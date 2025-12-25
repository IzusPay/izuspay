"use client"

import { DashboardLayout } from "@/components/dashboard-layout"
import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { RefreshCw, EyeOff } from "lucide-react"
import { useState } from "react"
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, ResponsiveContainer, BarChart, Bar } from "recharts"

export default function DashboardPage() {
  const [stats, setStats] = useState({
    pix: { total: 0, ontem: 0 },
    card: { total: 0, ontem: 0 },
    boleto: { total: 0, ontem: 0 },
    balance: 0,
    affiliates: 0,
    transactions: 0,
    avgTicket: 0,
  })

  const salesData = [
    { day: "1", value: 2 },
    { day: "15", value: 1.5 },
    { day: "30", value: 1 },
  ]

  const ticketData = [
    { day: "1", value: 40 },
    { day: "2", value: 45 },
    { day: "3", value: 50 },
    { day: "4", value: 43 },
    { day: "5", value: 44 },
  ]

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h1 className="text-2xl font-semibold">Olá, ArMatch!</h1>
          <div className="flex gap-2">
            <Button variant="outline" size="sm">
              <RefreshCw className="h-4 w-4 mr-2" />
              Atualizar
            </Button>
            <Button variant="outline" size="sm">
              <EyeOff className="h-4 w-4 mr-2" />
              Ocultar dados
            </Button>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card className="p-6">
            <div className="flex items-center gap-2 mb-2">
              <div className="text-lg">💎</div>
              <span className="text-sm text-muted-foreground">Pix (Total)</span>
            </div>
            <div className="text-2xl font-bold">R$ 33,00</div>
            <div className="text-xs text-muted-foreground mt-1">Ontem: R$ 0,00</div>
          </Card>

          <Card className="p-6">
            <div className="flex items-center gap-2 mb-2">
              <div className="text-lg">💳</div>
              <span className="text-sm text-muted-foreground">Cartão (Total)</span>
            </div>
            <div className="text-2xl font-bold">R$ 0,00</div>
            <div className="text-xs text-muted-foreground mt-1">Ontem: R$ 0,00</div>
          </Card>

          <Card className="p-6">
            <div className="flex items-center gap-2 mb-2">
              <div className="text-lg">🎫</div>
              <span className="text-sm text-muted-foreground">Boleto (Total)</span>
            </div>
            <div className="text-2xl font-bold">R$ 0,00</div>
            <div className="text-xs text-muted-foreground mt-1">Ontem: R$ 0,00</div>
          </Card>

          <Card className="p-6 bg-black text-white">
            <div className="flex items-center gap-2 mb-2">
              <div className="text-lg">💰</div>
              <span className="text-sm text-gray-300">Saldo disponível</span>
            </div>
            <div className="text-2xl font-bold">R$ 0,38</div>
          </Card>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <Card className="p-6 lg:col-span-2">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold">Gráfico de Vendas (Últimos 30 Dias)</h3>
              <Button variant="ghost" size="sm">
                <EyeOff className="h-4 w-4 mr-2" />
                Ocultar
              </Button>
            </div>
            <ResponsiveContainer width="100%" height={300}>
              <AreaChart data={salesData}>
                <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
                <XAxis dataKey="day" />
                <YAxis />
                <Area type="monotone" dataKey="value" stroke="#000" fill="#999" />
              </AreaChart>
            </ResponsiveContainer>
          </Card>

          <Card className="p-6">
            <div className="flex flex-col items-center justify-center h-full">
              <div className="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mb-4">
                <div className="text-4xl">🏢</div>
              </div>
              <h3 className="text-xl font-bold">ArMatch</h3>
              <p className="text-sm text-muted-foreground">CNPJ: 34562746000100</p>
              <div className="grid grid-cols-2 gap-8 mt-6 w-full">
                <div className="text-center">
                  <div className="text-sm text-muted-foreground">Afiliados</div>
                  <div className="text-2xl font-bold">0</div>
                </div>
                <div className="text-center">
                  <div className="text-sm text-muted-foreground">Transações</div>
                  <div className="text-2xl font-bold">19</div>
                </div>
              </div>
            </div>
          </Card>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card className="p-6">
            <h3 className="text-lg font-semibold mb-4">Relatório Diário</h3>
            <div className="space-y-4">
              <div className="flex justify-between items-center">
                <span className="text-sm text-muted-foreground">Pix (Hoje)</span>
                <span className="font-semibold">R$ 0,00</span>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-sm text-muted-foreground">Cartão (Hoje)</span>
                <span className="font-semibold">R$ 0,00</span>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-sm text-muted-foreground">Boleto (Hoje)</span>
                <span className="font-semibold">R$ 0,00</span>
              </div>
            </div>
          </Card>

          <Card className="p-6">
            <h3 className="text-lg font-semibold mb-4">Ticket médio</h3>
            <div className="text-2xl font-bold mb-4">R$ 43,71</div>
            <ResponsiveContainer width="100%" height={150}>
              <BarChart data={ticketData}>
                <Bar dataKey="value" fill="#000" />
              </BarChart>
            </ResponsiveContainer>
          </Card>
        </div>
      </div>
    </DashboardLayout>
  )
}
