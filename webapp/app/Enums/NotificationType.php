<?php

namespace App\Enums;

enum NotificationType: string
{
    case AnalysisComplete = 'analysis_complete';
    case Payment = 'payment';
    case Invitation = 'invitation';
    case Share = 'share';
    case System = 'system';
}
