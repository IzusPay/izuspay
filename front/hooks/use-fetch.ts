import { useState, useEffect, useCallback } from "react"
import { apiClient } from "@/lib/api"

interface UseFetchOptions {
  onSuccess?: (data: any) => void
  onError?: (error: any) => void
  initialData?: any
}

export function useFetch<T = any>(endpoint: string, options: UseFetchOptions = {}) {
  const [data, setData] = useState<T | null>(options.initialData || null)
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState<Error | null>(null)

  const fetchData = useCallback(async () => {
    try {
      setIsLoading(true)
      setError(null)
      const res = await apiClient(endpoint)
      
      if (!res.ok) {
        throw new Error(`Error ${res.status}: ${res.statusText}`)
      }

      // Tenta fazer parse do JSON, mas trata caso não seja JSON
      const contentType = res.headers.get("content-type")
      let result
      if (contentType && contentType.includes("application/json")) {
        result = await res.json()
      } else {
        // Se não for JSON, tenta ler como texto ou assume vazio
        const text = await res.text()
        try {
            result = JSON.parse(text)
        } catch {
            result = text // Retorna texto puro se falhar o parse
        }
      }

      // Normaliza a resposta se vier dentro de { data: ... }
      const finalData = result.data ? result.data : result

      setData(finalData)
      options.onSuccess?.(finalData)
    } catch (err: any) {
      console.error(`[useFetch] Error fetching ${endpoint}:`, err)
      setError(err)
      options.onError?.(err)
    } finally {
      setIsLoading(false)
    }
  }, [endpoint])

  useEffect(() => {
    fetchData()
  }, [fetchData])

  return { data, isLoading, error, refresh: fetchData }
}
