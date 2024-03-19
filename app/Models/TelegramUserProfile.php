<?php

namespace App\Models;

use App\Helpers\Assert;
use App\Helpers\TelegramPhoto;

class TelegramUserProfile extends BaseAttributeCaster
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
            $picture = $data['picture'] ?? null;

            if ($picture) {
                $fileName = TelegramPhoto::getFileNameFromUrl(Assert::string($picture));
                $picture = TelegramPhoto::getGcsUrlFromFileName(Assert::string($fileName));
            }

            $this->name = $data['name'] ?? null;
            $this->phoneNumber = $data['phone_number'] ?? null;
            $this->city = $data['city'] ?? null;
            $this->description = $data['description'] ?? null;
            $this->company = $data['company'] ?? null;
            $this->picture = $picture ? Assert::string($picture) : null;
            $this->isPublicProfile = $data['isPublicProfile'] ?? null;
        }
    }
}
