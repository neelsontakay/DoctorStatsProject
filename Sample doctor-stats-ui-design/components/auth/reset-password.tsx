'use client'

import Link from 'next/link'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { AuthLayout } from '@/components/app-shell'

export default function ResetPassword() {
  return (
    <AuthLayout>
      <Card className="w-full max-w-md">
        <CardHeader>
          <CardTitle>Set new password</CardTitle>
          <CardDescription>Choose a strong password for your account</CardDescription>
        </CardHeader>
        <CardContent>
          <form className="space-y-4" onSubmit={(e) => e.preventDefault()}>
            <div>
              <Label htmlFor="new-password" required>New password</Label>
              <Input id="new-password" type="password" autoComplete="new-password" />
            </div>
            <div>
              <Label htmlFor="confirm-password" required>Confirm password</Label>
              <Input id="confirm-password" type="password" autoComplete="new-password" />
            </div>
            <Button type="submit" className="w-full" size="lg">
              Update password
            </Button>
            <Link href="#signin" className="block text-center text-sm text-primary hover:underline">
              Back to sign in
            </Link>
          </form>
        </CardContent>
      </Card>
    </AuthLayout>
  )
}
