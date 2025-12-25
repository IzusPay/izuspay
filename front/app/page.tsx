"use client"

import { useRouter } from "next/navigation"
import { useEffect, useState } from "react"
import { getAuthToken } from "@/lib/api"

export default function Home() {
  const router = useRouter()
  const [checking, setChecking] = useState(true)

  useEffect(() => {
    const token = getAuthToken()
    if (token) {
      router.push("/dashboard")
    } else {
      router.push("/login")
    }
    setChecking(false)
  }, [router])

  if (checking) {
    return (
      <div className="flex h-screen items-center justify-center">
        <div className="h-8 w-8 animate-spin rounded-full border-4 border-gray-300 border-t-black" />
      </div>
    )
  }

  return null
}
