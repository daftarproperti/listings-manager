<?php

namespace App\Models;

class PropertyUser extends BaseAttributeCaster
{
    public string $name;
    public ?string $userName;
    public int $userId;
    public string $source;
}
