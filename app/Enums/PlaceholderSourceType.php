<?php

namespace App\Enums;

enum PlaceholderSourceType: string
{
    case FORM = 'FORM';
    case PROFILE = 'PROFILE';
    case INTERNAL = 'INTERNAL';
    case SYSTEM_AUTOFILL = 'SYSTEM_AUTOFILL';
}

