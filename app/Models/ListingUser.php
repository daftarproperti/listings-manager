<?php

namespace App\Models;

/**
 * @extends BaseAttributeCaster<ListingUser, ListingUser>
 */
class ListingUser extends BaseAttributeCaster
{
    protected string $_castType = self::class;

    public string $name;
    public ?string $userName;
    public int $userId;
    public string $source;
}
