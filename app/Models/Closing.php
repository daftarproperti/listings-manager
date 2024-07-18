<?php

namespace App\Models;

use App\Models\Enums\ClosingType;
use MongoDB\BSON\UTCDateTime;
use Spatie\LaravelData\Data;

class Closing extends Data
{
    public ClosingType $type;
    public string $clientName;
    public string $clientPhoneNumber;
    public int $transactionValue;
    public UTCDateTime $date;
}
