<?php

namespace App\Models;

use Spatie\LaravelData\Data;

class PropertyUser extends Data
{
    public string $name;
    public ?string $userName;
    public int $userId;
    public string $source;
}
