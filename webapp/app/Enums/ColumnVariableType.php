<?php

namespace App\Enums;

enum ColumnVariableType: string
{
    case Independent = 'independent';
    case Dependent = 'dependent';
    case Control = 'control';
    case Identifier = 'identifier';
    case Excluded = 'excluded';
}
