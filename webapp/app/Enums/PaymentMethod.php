<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case PayPerJob = 'pay_per_job';
    case Subscription = 'subscription';
}
