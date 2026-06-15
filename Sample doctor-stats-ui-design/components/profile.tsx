'use client'

import { AppShell } from '@/components/app-shell'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useState } from 'react'

export default function Profile() {
  const [deleteOpen, setDeleteOpen] = useState(false)

  return (
    <AppShell activeNav="Dashboard">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-foreground mb-2">Profile</h1>
        <p className="text-muted-foreground">Manage your account settings</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-4xl">
        <Card>
          <CardHeader>
            <CardTitle>Personal information</CardTitle>
            <CardDescription>Update your profile details</CardDescription>
          </CardHeader>
          <CardContent>
            <form className="space-y-4" onSubmit={(e) => e.preventDefault()}>
              <div>
                <Label htmlFor="profile-name" required>Full name</Label>
                <Input id="profile-name" defaultValue="Dr. Sarah Chen" />
              </div>
              <div>
                <Label htmlFor="profile-title">Title</Label>
                <Input id="profile-title" defaultValue="Principal Investigator" />
              </div>
              <div>
                <Label htmlFor="profile-affiliation">Affiliation</Label>
                <Input id="profile-affiliation" defaultValue="St. Mary's Research Lab" />
              </div>
              <div>
                <Label htmlFor="profile-email">Email</Label>
                <Input id="profile-email" type="email" defaultValue="sarah.chen@stmarys.edu" disabled />
                <p className="text-xs text-muted-foreground mt-1">Contact support to change your email</p>
              </div>
              <Button type="submit">Save changes</Button>
            </form>
          </CardContent>
        </Card>

        <div className="space-y-8">
          <Card>
            <CardHeader>
              <CardTitle>Change password</CardTitle>
            </CardHeader>
            <CardContent>
              <form className="space-y-4" onSubmit={(e) => e.preventDefault()}>
                <div>
                  <Label htmlFor="current-pw" required>Current password</Label>
                  <Input id="current-pw" type="password" />
                </div>
                <div>
                  <Label htmlFor="new-pw" required>New password</Label>
                  <Input id="new-pw" type="password" />
                </div>
                <Button type="submit" variant="outline">Update password</Button>
              </form>
            </CardContent>
          </Card>

          <Card className="border-destructive/30">
            <CardHeader>
              <CardTitle className="text-destructive">Danger zone</CardTitle>
              <CardDescription>Permanently deactivate your account and all personal data</CardDescription>
            </CardHeader>
            <CardContent>
              <Button variant="destructive" onClick={() => setDeleteOpen(true)}>
                Deactivate account
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>

      <Dialog
        open={deleteOpen}
        onClose={() => setDeleteOpen(false)}
        title="Deactivate account?"
        description="This action cannot be undone. All your personal data will be scheduled for deletion."
      >
        <div className="flex gap-3 justify-end">
          <Button variant="outline" onClick={() => setDeleteOpen(false)}>Cancel</Button>
          <Button variant="destructive">Confirm deactivation</Button>
        </div>
      </Dialog>
    </AppShell>
  )
}
