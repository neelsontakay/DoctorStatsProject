<?php

namespace App\Enums;

enum PaymentType: string
{
    case PayPerJob = 'pay_per_job';
    case Subscription = 'subscription';
    case Renewal = 'renewal';
}
