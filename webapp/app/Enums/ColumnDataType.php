<?php

namespace App\Enums;

enum ColumnDataType: string
{
    case Categorical = 'categorical';
    case Numerical = 'numerical';
    case Date = 'date';
    case Text = 'text';
}
