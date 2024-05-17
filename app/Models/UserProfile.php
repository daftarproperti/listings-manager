<?php

namespace App\Models;

use Spatie\LaravelData\Data;

class UserProfile extends Data
{
    public ?string $name = null;
    public ?string $city = null;
    public ?string $description = null;
    public ?string $picture = null;
    public ?bool $isPublicProfile = null;

    // Only applies to Professional account type
    public ?string $company = null;
    public ?string $cityOfOperation = null;
}
