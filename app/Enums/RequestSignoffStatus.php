<?php

namespace App\Enums;

enum RequestSignoffStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REVISION_REQUESTED = 'REVISION_REQUESTED';
    case REJECTED = 'REJECTED';
}

