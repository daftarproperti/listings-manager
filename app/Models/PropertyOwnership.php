<?php

namespace App\Models;

enum PropertyOwnership: string
{
    case Unknown = 'unknown';
    case SHM = 'shm';
    case HGB = 'hgb';
    case Strata = 'strata';
    case Girik = 'girik';
}
