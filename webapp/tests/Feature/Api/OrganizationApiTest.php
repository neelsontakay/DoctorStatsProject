<?php

namespace Tests\Feature\Api;

use App\Enums\OrganizationMemberStatus;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrganizationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_org_member_can_view_organization_and_members(): void
    {
        $organization = Organization::factory()->create();
        $member = User::factory()->create();
        OrganizationMember::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $member->id,
            'role' => OrganizationRole::Analyst,
            'status' => OrganizationMemberStatus::Active,
        ]);

        $this->actingAs($member)
            ->getJson("/api/v1/organizations/{$organization->id}")
            ->assertOk()
            ->assertJsonPath('data.name', $organization->name);

        $this->actingAs($member)
            ->getJson("/api/v1/organizations/{$organization->id}/members")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_non_member_cannot_view_organization(): void
    {
        $organization = Organization::factory()->create();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->getJson("/api/v1/organizations/{$organization->id}")
            ->assertForbidden();
    }

    public function test_admin_can_invite_member(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $organization = Organization::factory()->create(['admin_user_id' => $admin->id]);
        OrganizationMember::factory()->admin()->create([
            'organization_id' => $organization->id,
            'user_id' => $admin->id,
            'status' => OrganizationMemberStatus::Active,
        ]);

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/organizations/{$organization->id}/invitations", [
                'email' => 'newmember@example.com',
                'role' => OrganizationRole::Analyst->value,
            ]);

        $response->assertCreated()
            ->assertJsonPath('invitation.email', 'newmember@example.com');

        $this->assertDatabaseHas('organization_invitations', [
            'organization_id' => $organization->id,
            'email' => 'newmember@example.com',
            'status' => 'pending',
        ]);
    }

    public function test_user_can_accept_invitation_for_matching_email(): void
    {
        $organization = Organization::factory()->create();
        $inviter = User::factory()->create();
        $invitee = User::factory()->create(['email' => 'invitee@example.com']);

        $invitation = OrganizationInvitation::query()->create([
            'organization_id' => $organization->id,
            'invited_by' => $inviter->id,
            'email' => 'invitee@example.com',
            'token' => 'test-invitation-token',
            'role' => OrganizationRole::Viewer,
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
        ]);

        $this->actingAs($invitee)
            ->postJson('/api/v1/invitations/test-invitation-token/accept')
            ->assertOk()
            ->assertJsonPath('membership.role', OrganizationRole::Viewer->value);

        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $organization->id,
            'user_id' => $invitee->id,
            'role' => 'viewer',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('organization_invitations', [
            'id' => $invitation->id,
            'status' => 'accepted',
        ]);
    }
}
