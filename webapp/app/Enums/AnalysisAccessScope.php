<?php

namespace App\Enums;

enum AnalysisAccessScope: string
{
    case AllMembers = 'all_members';
    case SpecificMembers = 'specific_members';
    case Private = 'private';
}
