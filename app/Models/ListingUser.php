<?php

namespace App\Models;

class ListingUser extends BaseAttributeCaster
{
    public string $name;
    public ?string $userName;
    public int $userId;
    public string $source;
}
