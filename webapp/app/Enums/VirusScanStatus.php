<?php

namespace App\Enums;

enum VirusScanStatus: string
{
    case Pending = 'pending';
    case Clean = 'clean';
    case Rejected = 'rejected';
}
