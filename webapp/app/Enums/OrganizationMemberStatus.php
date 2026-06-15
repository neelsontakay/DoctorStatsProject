<?php

namespace App\Enums;

enum OrganizationMemberStatus: string
{
    case Active = 'active';
    case PendingInvitation = 'pending_invitation';
    case Inactive = 'inactive';
}
