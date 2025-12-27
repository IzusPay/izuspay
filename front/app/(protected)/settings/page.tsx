"use client"

import { useEffect, useState, useRef } from "react"
import { Upload, Trash2, Save, Image as ImageIcon } from "lucide-react"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { useToast } from "@/components/ui/use-toast"
import {
  settingsService,
  SystemSetting,
  Banner,
  BannerType,
} from "@/lib/services/settings.service"
import { format } from "date-fns"
import { ptBR } from "date-fns/locale"
import { useAuth } from "@/contexts/auth-context"

export default function SettingsPage() {
  const { toast } = useToast()
  const { user } = useAuth()
  const [settings, setSettings] = useState<SystemSetting[]>([])
  const [banners, setBanners] = useState<Banner[]>([])
  const [loading, setLoading] = useState(true)

  // Verify Read Permission
  if (user?.role !== "admin" && !user?.permissions?.["system_settings"]?.canRead) {
    return (
      <div className="flex h-[50vh] items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-red-600">Acesso Negado</h1>
          <p className="text-gray-600">Você não tem permissão para acessar este módulo.</p>
        </div>
      </div>
    )
  }

  // System Settings state
  const [siteName, setSiteName] = useState("")
  const [supportEmail, setSupportEmail] = useState("")
  const [termsUrl, setTermsUrl] = useState("")

  // File inputs
  const faviconInputRef = useRef<HTMLInputElement>(null)
  const logoInputRef = useRef<HTMLInputElement>(null)
  const bannerInputRef = useRef<HTMLInputElement>(null)

  async function loadData() {
    try {
      setLoading(true)
      const [settingsData, bannersData] = await Promise.all([
        settingsService.getSettings(),
        settingsService.getBanners(),
      ])
      
      setSettings(settingsData)
      setBanners(bannersData)

      // Map settings to state
      const findSetting = (key: string) => settingsData.find(s => s.key === key)?.value || ""
      setSiteName(findSetting("site_name"))
      setSupportEmail(findSetting("support_email"))
      setTermsUrl(findSetting("terms_url"))

    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao carregar configurações",
        description: "Não foi possível carregar as informações do sistema.",
      })
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    loadData()
  }, [])

  async function handleSaveSettings() {
    try {
      setLoading(true)
      await Promise.all([
        settingsService.updateSetting("site_name", siteName, "Nome do Site"),
        settingsService.updateSetting("support_email", supportEmail, "Email de Suporte"),
        settingsService.updateSetting("terms_url", termsUrl, "URL dos Termos de Uso"),
      ])
      
      toast({
        title: "Configurações salvas",
        description: "As informações do sistema foram atualizadas.",
      })
      
      loadData()
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao salvar",
        description: "Não foi possível atualizar as configurações.",
      })
    } finally {
      setLoading(false)
    }
  }

  async function handleUpload(type: BannerType, file: File) {
    try {
      await settingsService.uploadBanner(type, file)
      toast({
        title: "Upload concluído",
        description: "O arquivo foi enviado com sucesso.",
      })
      loadData()
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro no upload",
        description: "Não foi possível enviar o arquivo.",
      })
    }
  }

  async function handleDeleteBanner(id: string) {
    try {
      await settingsService.deleteBanner(id)
      setBanners(banners.filter(b => b.id !== id))
      toast({
        title: "Arquivo removido",
        description: "O banner foi removido com sucesso.",
      })
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Erro ao remover",
        description: "Não foi possível excluir o arquivo.",
      })
    }
  }

  const getBannersByType = (type: BannerType) => banners.filter(b => b.type === type)

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Configurações do Sistema</h1>
        <p className="text-muted-foreground">
          Gerencie informações gerais, identidade visual e banners.
        </p>
      </div>

      <Tabs defaultValue="general">
        <TabsList>
          <TabsTrigger value="general">Geral</TabsTrigger>
          <TabsTrigger value="branding">Identidade Visual</TabsTrigger>
          <TabsTrigger value="banners">Banners Rotativos</TabsTrigger>
        </TabsList>

        <TabsContent value="general">
          <Card>
            <CardHeader>
              <CardTitle>Informações Básicas</CardTitle>
              <CardDescription>
                Configure os dados principais da plataforma.
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid gap-2">
                <Label htmlFor="siteName">Nome do Site</Label>
                <Input
                  id="siteName"
                  value={siteName}
                  onChange={(e) => setSiteName(e.target.value)}
                  placeholder="Ex: IzusPay"
                />
              </div>
              <div className="grid gap-2">
                <Label htmlFor="supportEmail">Email de Suporte</Label>
                <Input
                  id="supportEmail"
                  type="email"
                  value={supportEmail}
                  onChange={(e) => setSupportEmail(e.target.value)}
                  placeholder="suporte@izuspay.com"
                />
              </div>
              <div className="grid gap-2">
                <Label htmlFor="termsUrl">URL dos Termos de Uso</Label>
                <Input
                  id="termsUrl"
                  value={termsUrl}
                  onChange={(e) => setTermsUrl(e.target.value)}
                  placeholder="https://izuspay.com/termos"
                />
              </div>
              <div className="flex justify-end pt-4">
                <Button onClick={handleSaveSettings} disabled={loading}>
                  <Save className="mr-2 h-4 w-4" /> Salvar Alterações
                </Button>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="branding">
          <div className="grid gap-6 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Logotipo</CardTitle>
                <CardDescription>Logo principal do sistema (PNG/SVG).</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="border-2 border-dashed rounded-lg p-6 flex flex-col items-center justify-center min-h-[200px] bg-muted/50">
                  {getBannersByType(BannerType.LOGO).length > 0 ? (
                    <div className="relative w-full h-full flex items-center justify-center">
                      <img
                        src={getBannersByType(BannerType.LOGO)[0].url}
                        alt="Logo"
                        className="max-h-[150px] object-contain"
                      />
                      <Button
                        variant="destructive"
                        size="icon"
                        className="absolute -top-2 -right-2 h-6 w-6"
                        onClick={() => handleDeleteBanner(getBannersByType(BannerType.LOGO)[0].id)}
                      >
                        <Trash2 className="h-3 w-3" />
                      </Button>
                    </div>
                  ) : (
                    <div className="text-center text-muted-foreground">
                      <ImageIcon className="mx-auto h-12 w-12 opacity-50 mb-2" />
                      <p>Nenhum logo enviado</p>
                    </div>
                  )}
                </div>
                <div className="flex justify-center">
                  <input
                    type="file"
                    ref={logoInputRef}
                    className="hidden"
                    accept="image/*"
                    onChange={(e) => {
                      const file = e.target.files?.[0]
                      if (file) handleUpload(BannerType.LOGO, file)
                    }}
                  />
                  <Button variant="outline" onClick={() => logoInputRef.current?.click()}>
                    <Upload className="mr-2 h-4 w-4" /> Selecionar Logo
                  </Button>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Favicon</CardTitle>
                <CardDescription>Ícone da aba do navegador (ICO/PNG).</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="border-2 border-dashed rounded-lg p-6 flex flex-col items-center justify-center min-h-[200px] bg-muted/50">
                  {getBannersByType(BannerType.FAVICON).length > 0 ? (
                    <div className="relative">
                      <img
                        src={getBannersByType(BannerType.FAVICON)[0].url}
                        alt="Favicon"
                        className="w-16 h-16 object-contain"
                      />
                      <Button
                        variant="destructive"
                        size="icon"
                        className="absolute -top-2 -right-2 h-6 w-6"
                        onClick={() => handleDeleteBanner(getBannersByType(BannerType.FAVICON)[0].id)}
                      >
                        <Trash2 className="h-3 w-3" />
                      </Button>
                    </div>
                  ) : (
                    <div className="text-center text-muted-foreground">
                      <ImageIcon className="mx-auto h-12 w-12 opacity-50 mb-2" />
                      <p>Nenhum favicon</p>
                    </div>
                  )}
                </div>
                <div className="flex justify-center">
                  <input
                    type="file"
                    ref={faviconInputRef}
                    className="hidden"
                    accept="image/*"
                    onChange={(e) => {
                      const file = e.target.files?.[0]
                      if (file) handleUpload(BannerType.FAVICON, file)
                    }}
                  />
                  <Button variant="outline" onClick={() => faviconInputRef.current?.click()}>
                    <Upload className="mr-2 h-4 w-4" /> Selecionar Favicon
                  </Button>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="banners">
          <Card>
            <CardHeader>
              <CardTitle>Banners da Home</CardTitle>
              <CardDescription>
                Imagens rotativas exibidas na página inicial.
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="flex justify-end">
                <input
                  type="file"
                  ref={bannerInputRef}
                  className="hidden"
                  accept="image/*"
                  onChange={(e) => {
                    const file = e.target.files?.[0]
                    if (file) handleUpload(BannerType.CAROUSEL_BANNER, file)
                  }}
                />
                <Button onClick={() => bannerInputRef.current?.click()}>
                  <Upload className="mr-2 h-4 w-4" /> Adicionar Banner
                </Button>
              </div>

              <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                {getBannersByType(BannerType.CAROUSEL_BANNER).map((banner) => (
                  <div
                    key={banner.id}
                    className="group relative aspect-video rounded-lg overflow-hidden border bg-muted"
                  >
                    <img
                      src={banner.url}
                      alt="Banner"
                      className="w-full h-full object-cover transition-transform group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                      <Button
                        variant="destructive"
                        size="icon"
                        onClick={() => handleDeleteBanner(banner.id)}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </div>
                    <div className="absolute bottom-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded">
                      {format(new Date(banner.createdAt), "dd/MM/yyyy", { locale: ptBR })}
                    </div>
                  </div>
                ))}
                {getBannersByType(BannerType.CAROUSEL_BANNER).length === 0 && (
                  <div className="col-span-full py-12 text-center text-muted-foreground border-2 border-dashed rounded-lg">
                    <ImageIcon className="mx-auto h-12 w-12 opacity-50 mb-2" />
                    <p>Nenhum banner cadastrado</p>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  )
}
