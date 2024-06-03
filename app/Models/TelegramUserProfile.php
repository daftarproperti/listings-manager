<?php

namespace App\Models;

use Spatie\LaravelData\Data;

class TelegramUserProfile extends Data
{
    public ?string $name = null;
    public ?string $phoneNumber = null;
    public ?string $city = null;
    public ?int $cityId = null;
    public ?string $cityName = null;
    public ?string $description = null;
    public ?string $company = null;
    public ?string $picture = null;
    public ?bool $isPublicProfile = null;
}
