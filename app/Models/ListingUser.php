<?php

namespace App\Models;

use Spatie\LaravelData\Data;

class ListingUser extends Data
{
    public ?string $name = null;
    public ?string $userName = null;
    public int $userId;
    public ?string $source = null;
}
