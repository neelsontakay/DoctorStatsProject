'use client'

import Link from 'next/link'
import { Building2 } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { AuthLayout } from '@/components/app-shell'

export default function AcceptInvitation() {
  return (
    <AuthLayout>
      <Card className="w-full max-w-md text-center">
        <CardHeader>
          <div className="flex justify-center mb-4">
            <div className="w-16 h-16 rounded-lg bg-primary-100 flex items-center justify-center">
              <Building2 className="w-8 h-8 text-primary" aria-hidden="true" />
            </div>
          </div>
          <CardTitle>Join organisation</CardTitle>
          <CardDescription>
            You&apos;ve been invited to join <strong>St. Mary&apos;s Research Lab</strong>
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex justify-center">
            <Badge variant="analyst">Analyst role</Badge>
          </div>
          <p className="text-sm text-muted-foreground">
            As an Analyst, you can create analyses, view shared reports, and collaborate with your team.
          </p>
          <div className="space-y-3">
            <Button className="w-full" size="lg">Accept invitation</Button>
            <p className="text-xs text-muted-foreground">
              Not signed in?{' '}
              <Link href="#signin" className="text-primary hover:underline">Sign in</Link>
              {' '}or{' '}
              <Link href="#register" className="text-primary hover:underline">create an account</Link>
              {' '}first.
            </p>
          </div>
        </CardContent>
      </Card>
    </AuthLayout>
  )
}
