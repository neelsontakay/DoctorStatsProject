<?php

namespace App\Enums;

enum ShareMethod: string
{
    case SecureLink = 'secure_link';
    case Email = 'email';
    case OrgInternal = 'org_internal';
    case External = 'external';
}
