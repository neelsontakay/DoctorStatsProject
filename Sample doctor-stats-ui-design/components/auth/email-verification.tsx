'use client'

import Link from 'next/link'
import { CheckCircle2, Clock, XCircle } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { AuthLayout } from '@/components/app-shell'

type VerificationState = 'pending' | 'verified' | 'expired'

const STATE_CONFIG: Record<
  VerificationState,
  { icon: typeof Clock; title: string; description: string; color: string }
> = {
  pending: {
    icon: Clock,
    title: 'Verify your email',
    description: 'We sent a verification link to your inbox. Click the link to activate your account.',
    color: 'text-secondary',
  },
  verified: {
    icon: CheckCircle2,
    title: 'Email verified',
    description: 'Your account is active. You can now sign in and start your first analysis.',
    color: 'text-accent',
  },
  expired: {
    icon: XCircle,
    title: 'Link expired',
    description: 'This verification link has expired. Request a new one to continue.',
    color: 'text-destructive',
  },
}

export default function EmailVerification({ state = 'pending' }: { state?: VerificationState }) {
  const config = STATE_CONFIG[state]
  const Icon = config.icon

  return (
    <AuthLayout>
      <Card className="w-full max-w-md text-center">
        <CardHeader>
          <div className="flex justify-center mb-4">
            <div className="w-16 h-16 rounded-full bg-muted flex items-center justify-center">
              <Icon className={`w-8 h-8 ${config.color}`} aria-hidden="true" />
            </div>
          </div>
          <CardTitle>{config.title}</CardTitle>
          <CardDescription>{config.description}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-3">
          {state === 'pending' && (
            <>
              <Button variant="outline" className="w-full">Resend verification email</Button>
              <Link href="#signin" className="block text-sm text-primary hover:underline">
                Back to sign in
              </Link>
            </>
          )}
          {state === 'verified' && (
            <Link href="#dashboard">
              <Button className="w-full">Go to dashboard</Button>
            </Link>
          )}
          {state === 'expired' && (
            <Button className="w-full">Request new link</Button>
          )}
        </CardContent>
      </Card>
    </AuthLayout>
  )
}
