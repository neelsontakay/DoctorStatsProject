<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case Admin = 'admin';
    case Analyst = 'analyst';
    case Viewer = 'viewer';
}
