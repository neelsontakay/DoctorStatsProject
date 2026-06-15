'use client'

import { useState } from 'react'
import { LogIn, Sparkles } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Alert } from '@/components/ui/alert'
import { AuthLayout } from '@/components/app-shell'
import { DEMO_EMAIL, DEMO_PASSWORD, isDemoCredentials } from '@/lib/demo-credentials'

type SignInProps = {
  onSuccess?: () => void
  onNavigateRegister?: () => void
  onNavigateForgot?: () => void
}

export default function SignIn({
  onSuccess,
  onNavigateRegister,
  onNavigateForgot,
}: SignInProps) {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  const authenticate = async (e: string, p: string) => {
    setLoading(true)
    setError('')
    await new Promise((resolve) => setTimeout(resolve, 500))

    if (isDemoCredentials(e, p)) {
      onSuccess?.()
    } else {
      setError('Invalid email or password. Use the demo credentials below.')
    }
    setLoading(false)
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    authenticate(email, password)
  }

  const handleDemoLogin = () => {
    setEmail(DEMO_EMAIL)
    setPassword(DEMO_PASSWORD)
    authenticate(DEMO_EMAIL, DEMO_PASSWORD)
  }

  return (
    <AuthLayout>
      <Card className="w-full max-w-md">
        <CardHeader>
          <CardTitle>Sign in</CardTitle>
          <CardDescription>Access your analyses and reports</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="mb-4 rounded-lg border border-primary-200 bg-primary-50 px-4 py-3 text-sm">
            <p className="font-semibold text-primary-900 mb-1 flex items-center gap-1.5">
              <Sparkles className="w-4 h-4" aria-hidden="true" />
              Demo credentials
            </p>
            <p className="text-primary-800">
              <span className="text-primary-700">Email:</span>{' '}
              <code className="font-mono text-xs bg-primary-100 px-1.5 py-0.5 rounded">{DEMO_EMAIL}</code>
            </p>
            <p className="text-primary-800 mt-1">
              <span className="text-primary-700">Password:</span>{' '}
              <code className="font-mono text-xs bg-primary-100 px-1.5 py-0.5 rounded">{DEMO_PASSWORD}</code>
            </p>
          </div>

          {error && (
            <Alert variant="error" title="Sign in failed" className="mb-4">
              {error}
            </Alert>
          )}

          <form className="space-y-4" onSubmit={handleSubmit}>
            <div>
              <Label htmlFor="email" required>Email</Label>
              <Input
                id="email"
                type="email"
                placeholder="you@institution.edu"
                autoComplete="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                disabled={loading}
              />
            </div>
            <div>
              <div className="flex items-center justify-between mb-2">
                <Label htmlFor="password" required className="mb-0">Password</Label>
                <button
                  type="button"
                  onClick={onNavigateForgot}
                  className="text-xs text-primary hover:underline"
                >
                  Forgot password?
                </button>
              </div>
              <Input
                id="password"
                type="password"
                autoComplete="current-password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                disabled={loading}
              />
            </div>
            <Checkbox id="remember" label="Remember me" />
            <Button type="submit" className="w-full" size="lg" disabled={loading}>
              {loading ? 'Signing in...' : 'Sign in'}
            </Button>
            <Button
              type="button"
              variant="outline"
              className="w-full gap-2"
              size="lg"
              onClick={handleDemoLogin}
              disabled={loading}
            >
              <LogIn className="w-4 h-4" aria-hidden="true" />
              Demo sign in
            </Button>
          </form>
          <p className="text-sm text-muted-foreground text-center mt-6">
            Don&apos;t have an account?{' '}
            <button
              type="button"
              onClick={onNavigateRegister}
              className="text-primary font-medium hover:underline"
            >
              Create account
            </button>
          </p>
        </CardContent>
      </Card>
    </AuthLayout>
  )
}
