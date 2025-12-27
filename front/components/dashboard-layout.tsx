"use client"

import { type ReactNode, useState, useEffect } from "react"
import Link from "next/link"
import { usePathname } from "next/navigation"
import { cn } from "@/lib/utils"
import * as LucideIcons from "lucide-react"
import { Button } from "@/components/ui/button"
import { useAuth } from "@/contexts/auth-context"
import { accessControlService, ACLModule } from "@/lib/services/access-control.service"

export function DashboardLayout({ children }: { children: ReactNode }) {
  const pathname = usePathname()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const { user, logout } = useAuth()
  const [modules, setModules] = useState<ACLModule[]>([])

  useEffect(() => {
    accessControlService.findAllModules().then(setModules).catch(console.error)
  }, [])

  const navigation = [
    { name: "Dashboard", href: "/dashboard", icon: LucideIcons.Home, module: null },
    ...modules.map((mod) => ({
      name: mod.name,
      href: mod.route || "#",
      icon: (LucideIcons as Record<string, any>)[mod.icon || "Circle"] || LucideIcons.Circle,
      module: mod.key,
    })),
  ]

  const filteredNavigation = navigation.filter((item) => {
    if (!item.module) return true
    // Safety net: Admin role bypasses strict ACL
    if (user?.role === "admin") return true
    if (!user?.permissions) return false
    return user.permissions[item.module]?.canRead
  })

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Mobile sidebar backdrop */}
      {sidebarOpen && (
        <div className="fixed inset-0 bg-black/50 z-40 lg:hidden" onClick={() => setSidebarOpen(false)} />
      )}

      {/* Sidebar */}
      <aside
        className={cn(
          "fixed inset-y-0 left-0 z-50 w-64 bg-black text-white transform transition-transform duration-200 ease-in-out lg:translate-x-0",
          sidebarOpen ? "translate-x-0" : "-translate-x-full",
        )}
      >
        <div className="flex flex-col h-full">
          {/* Logo */}
          <div className="flex items-center justify-between h-16 px-6 border-b border-gray-800">
            <div className="flex items-center gap-2">
              <div className="text-2xl font-bold">W</div>
            </div>
            <Button
              variant="ghost"
              size="sm"
              className="lg:hidden text-white hover:bg-gray-800"
              onClick={() => setSidebarOpen(false)}
            >
              <LucideIcons.X className="h-5 w-5" />
            </Button>
          </div>

          {/* Navigation */}
          <nav className="flex-1 px-4 py-6 space-y-2">
            {filteredNavigation.map((item) => {
              const isActive = pathname === item.href
              return (
                <Link
                  key={item.name}
                  href={item.href}
                  className={cn(
                    "flex items-center gap-3 px-4 py-3 rounded-lg transition-colors",
                    isActive ? "bg-white text-black" : "text-gray-300 hover:bg-gray-800 hover:text-white",
                  )}
                >
                  <item.icon className="h-5 w-5" />
                  <span className="font-medium">{item.name}</span>
                </Link>
              )
            })}
          </nav>

          {/* User section */}
          <div className="p-4 border-t border-gray-800">
            <div className="flex items-center gap-3 px-4 py-3">
              <div className="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center">
                <span className="text-sm font-semibold">
                  {user?.name
                    ?.split(" ")
                    .map((n) => n[0])
                    .join("")
                    .toUpperCase()
                    .slice(0, 2) || "U"}
                </span>
              </div>
              <div className="flex-1 min-w-0">
                <div className="text-sm font-medium truncate">{user?.name || "Usuário"}</div>
                <div className="text-xs text-gray-400 truncate">{user?.email || ""}</div>
              </div>
              <Button
                variant="ghost"
                size="sm"
                className="text-gray-400 hover:text-white hover:bg-gray-800"
                onClick={() => logout()}
                title="Sair"
              >
                <LucideIcons.LogOut className="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
      </aside>

      {/* Main content */}
      <div className="lg:pl-64">
        {/* Mobile header */}
        <header className="lg:hidden sticky top-0 z-30 flex h-16 items-center gap-4 border-b bg-white px-6">
          <Button variant="ghost" size="sm" onClick={() => setSidebarOpen(true)}>
            <LucideIcons.Menu className="h-5 w-5" />
          </Button>
          <div className="text-xl font-bold">IzusPay</div>
        </header>

        {/* Page content */}
        <main className="p-6 lg:p-8">{children}</main>
      </div>
    </div>
  )
}
