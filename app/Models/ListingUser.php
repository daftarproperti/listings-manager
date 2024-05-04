<?php

namespace App\Models;

use Spatie\LaravelData\Data;

class ListingUser extends Data
{
    public string $name;
    public ?string $userName;
    public int $userId;
    public string $source;
}
