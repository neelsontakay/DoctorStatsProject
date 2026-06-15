'use client'

import { useState } from 'react'
import Link from 'next/link'
import { Building2, User } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { AuthLayout } from '@/components/app-shell'
import { cn } from '@/lib/utils'

type AccountType = 'individual' | 'organisation'

function PasswordStrength({ password }: { password: string }) {
  const score = [
    password.length >= 8,
    /[A-Z]/.test(password),
    /[a-z]/.test(password),
    /[0-9]/.test(password),
    /[^A-Za-z0-9]/.test(password),
  ].filter(Boolean).length

  const labels = ['', 'Weak', 'Fair', 'Good', 'Strong', 'Very strong']
  const colors = ['', 'bg-destructive', 'bg-secondary', 'bg-secondary', 'bg-accent', 'bg-accent']

  if (!password) return null

  return (
    <div className="mt-2" aria-live="polite">
      <div className="flex gap-1 mb-1">
        {[1, 2, 3, 4, 5].map((i) => (
          <div
            key={i}
            className={cn('h-1 flex-1 rounded-full bg-muted', i <= score && colors[score])}
          />
        ))}
      </div>
      <p className="text-xs text-muted-foreground">{labels[score]}</p>
    </div>
  )
}

export default function Register() {
  const [accountType, setAccountType] = useState<AccountType>('individual')
  const [password, setPassword] = useState('')

  return (
    <AuthLayout>
      <Card className="w-full max-w-lg">
        <CardHeader>
          <CardTitle>Create account</CardTitle>
          <CardDescription>Start analyzing clinical and research data</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 gap-3 mb-6" role="radiogroup" aria-label="Account type">
            {([
              { type: 'individual' as const, icon: User, label: 'Individual' },
              { type: 'organisation' as const, icon: Building2, label: 'Organisation' },
            ]).map(({ type, icon: Icon, label }) => (
              <button
                key={type}
                type="button"
                role="radio"
                aria-checked={accountType === type}
                onClick={() => setAccountType(type)}
                className={cn(
                  'flex flex-col items-center gap-2 p-4 rounded-lg border-2 transition-all min-h-[44px]',
                  accountType === type
                    ? 'border-primary bg-primary-50 text-primary'
                    : 'border-border hover:border-primary/50',
                )}
              >
                <Icon className="w-6 h-6" aria-hidden="true" />
                <span className="text-sm font-medium">{label}</span>
              </button>
            ))}
          </div>

          <form className="space-y-4" onSubmit={(e) => e.preventDefault()}>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <Label htmlFor="first-name" required>First name</Label>
                <Input id="first-name" autoComplete="given-name" />
              </div>
              <div>
                <Label htmlFor="last-name" required>Last name</Label>
                <Input id="last-name" autoComplete="family-name" />
              </div>
            </div>
            <div>
              <Label htmlFor="reg-email" required>Email</Label>
              <Input id="reg-email" type="email" autoComplete="email" />
            </div>
            {accountType === 'organisation' && (
              <div>
                <Label htmlFor="org-name" required>Organisation name</Label>
                <Input id="org-name" placeholder="St. Mary's Research Lab" />
              </div>
            )}
            <div>
              <Label htmlFor="reg-password" required>Password</Label>
              <Input
                id="reg-password"
                type="password"
                autoComplete="new-password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
              />
              <PasswordStrength password={password} />
              <p className="text-xs text-muted-foreground mt-1">
                Min 8 characters with uppercase, lowercase, number, and special character
              </p>
            </div>
            <Checkbox
              id="terms"
              label="I agree to the Terms of Service and Privacy Policy"
              required
            />
            <Button type="submit" className="w-full" size="lg">
              Create account
            </Button>
          </form>
          <p className="text-sm text-muted-foreground text-center mt-6">
            Already have an account?{' '}
            <Link href="#signin" className="text-primary font-medium hover:underline">
              Sign in
            </Link>
          </p>
        </CardContent>
      </Card>
    </AuthLayout>
  )
}
