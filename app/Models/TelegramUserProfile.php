<?php

namespace App\Models;

class TelegramUserProfile
{
    public string $name;
    public ?string $city = null;
    public ?string $description = null;
    public ?string $company = null;
    public ?string $picture = null;
}
