<?php

namespace App\Models;

class TelegramUserProfile
{
    public ?string $name = null;
    public ?string $phoneNumber = null;
    public ?string $city = null;
    public ?string $description = null;
    public ?string $company = null;
    public ?string $picture = null;
    public ?bool $isPublicProfile = null;

    /**
     * @param array<mixed> $data
     **/
    public function __construct(array $data = [])
    {
        if ($data) {
            $this->name = $data['name'] ?? null;
            $this->phoneNumber = $data['phone_number'] ?? null;
            $this->city = $data['city'] ?? null;
            $this->description = $data['description'] ?? null;
            $this->company = $data['company'] ?? null;
            $this->picture = $data['picture'] ?? null;
            $this->isPublicProfile = $data['isPublicProfile'] ?? null;
        }
    }
}
