'use client'

import { useState } from 'react'
import Link from 'next/link'
import { ArrowLeft, Mail } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Alert } from '@/components/ui/alert'
import { AuthLayout } from '@/components/app-shell'

export default function ForgotPassword() {
  const [submitted, setSubmitted] = useState(false)

  return (
    <AuthLayout>
      <Card className="w-full max-w-md">
        <CardHeader>
          <CardTitle>Reset password</CardTitle>
          <CardDescription>
            {submitted
              ? 'Check your inbox for a reset link'
              : 'Enter your email and we\'ll send you a reset link'}
          </CardDescription>
        </CardHeader>
        <CardContent>
          {submitted ? (
            <div className="space-y-4">
              <Alert variant="success" title="Email sent">
                If an account exists for that address, you will receive a password reset link shortly.
              </Alert>
              <Link href="#signin">
                <Button variant="outline" className="w-full gap-2">
                  <ArrowLeft className="w-4 h-4" /> Back to sign in
                </Button>
              </Link>
            </div>
          ) : (
            <form
              className="space-y-4"
              onSubmit={(e) => {
                e.preventDefault()
                setSubmitted(true)
              }}
            >
              <div>
                <Label htmlFor="reset-email" required>Email</Label>
                <Input id="reset-email" type="email" placeholder="you@institution.edu" />
              </div>
              <Button type="submit" className="w-full gap-2" size="lg">
                <Mail className="w-4 h-4" /> Send reset link
              </Button>
              <Link href="#signin" className="block text-center text-sm text-primary hover:underline">
                Back to sign in
              </Link>
            </form>
          )}
        </CardContent>
      </Card>
    </AuthLayout>
  )
}
