'use client'

import { Mail, MoreHorizontal, UserPlus } from 'lucide-react'
import { useState } from 'react'
import { AppShell } from '@/components/app-shell'
import { Avatar } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import type { MemberStatus, OrgRole } from '@/lib/constants'
import { formatOrgRole } from '@/lib/constants'

type Member = {
  id: number
  name: string
  email: string
  role: OrgRole
  status: MemberStatus
}

const members: Member[] = [
  { id: 1, name: 'Dr. Sarah Chen', email: 'sarah.chen@stmarys.edu', role: 'admin', status: 'active' },
  { id: 2, name: 'James Okonkwo', email: 'j.okonkwo@stmarys.edu', role: 'analyst', status: 'active' },
  { id: 3, name: 'Maria Lopez', email: 'm.lopez@stmarys.edu', role: 'viewer', status: 'active' },
  { id: 4, name: 'pending@research.edu', email: 'pending@research.edu', role: 'analyst', status: 'pending' },
]

const ROLE_BADGE: Record<OrgRole, 'admin' | 'analyst' | 'viewer'> = {
  admin: 'admin',
  analyst: 'analyst',
  viewer: 'viewer',
}

const STATUS_BADGE: Record<MemberStatus, 'active' | 'invited' | 'inactive'> = {
  active: 'active',
  pending: 'invited',
  inactive: 'inactive',
}

function InviteMemberDialog({ open, onClose }: { open: boolean; onClose: () => void }) {
  return (
    <Dialog
      open={open}
      onClose={onClose}
      title="Invite team member"
      description="Send an invitation to join St. Mary's Research Lab"
    >
      <form className="space-y-4" onSubmit={(e) => { e.preventDefault(); onClose() }}>
        <div>
          <Label htmlFor="invite-email" required>Email address</Label>
          <Input id="invite-email" type="email" placeholder="colleague@institution.edu" />
        </div>
        <div>
          <Label htmlFor="invite-role" required>Role</Label>
          <Select id="invite-role" defaultValue="analyst">
            <option value="admin">Admin — full organisation management</option>
            <option value="analyst">Analyst — create and view analyses</option>
            <option value="viewer">Viewer — read-only access to shared reports</option>
          </Select>
        </div>
        <div className="flex gap-3 justify-end pt-2">
          <Button type="button" variant="outline" onClick={onClose}>Cancel</Button>
          <Button type="submit" className="gap-2">
            <Mail className="w-4 h-4" aria-hidden="true" />
            Send invitation
          </Button>
        </div>
      </form>
    </Dialog>
  )
}

export default function OrgMembers() {
  const [inviteOpen, setInviteOpen] = useState(false)

  return (
    <AppShell activeNav="Org">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
          <h1 className="text-3xl font-bold text-foreground mb-2">Team members</h1>
          <p className="text-muted-foreground">Manage who has access to your organisation&apos;s analyses</p>
        </div>
        <Button className="gap-2" onClick={() => setInviteOpen(true)}>
          <UserPlus className="w-4 h-4" aria-hidden="true" />
          Invite member
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Members ({members.length})</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Member</TableHead>
                <TableHead>Role</TableHead>
                <TableHead>Status</TableHead>
                <TableHead className="w-12"><span className="sr-only">Actions</span></TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {members.map((member) => (
                <TableRow key={member.id}>
                  <TableCell>
                    <div className="flex items-center gap-3">
                      <Avatar name={member.name} size="sm" />
                      <div>
                        <p className="font-medium">{member.name}</p>
                        <p className="text-xs text-muted-foreground">{member.email}</p>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge variant={ROLE_BADGE[member.role]}>
                      {formatOrgRole(member.role)}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Badge variant={STATUS_BADGE[member.status]}>
                      {member.status === 'pending' ? 'Invited' : member.status.charAt(0).toUpperCase() + member.status.slice(1)}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Button variant="ghost" size="icon" aria-label={`Actions for ${member.name}`}>
                      <MoreHorizontal className="w-4 h-4" />
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <InviteMemberDialog open={inviteOpen} onClose={() => setInviteOpen(false)} />
    </AppShell>
  )
}
