<?php

namespace App\DTO\Telegram;

use Spatie\LaravelData\Data;

/**
 * https://core.telegram.org/bots/api#chat
 */
class Chat extends Data
{
    public int $id;
    public string $type;
    public string $first_name;
    public string $last_name;
    public string $username;
}

/**
 * https://core.telegram.org/bots/api#user
 */
class User extends Data
{
    public int $id;
    public bool $is_bot;
    public string $first_name;
    public string $last_name;
    public string $username;
}

/**
 * https://core.telegram.org/bots/api#photosize
 */
class PhotoSize extends Data
{
    public string $file_id;
    public string $file_unique_id;
}

/**
 * https://core.telegram.org/bots/api#message
 */
class Message extends Data
{
    public int $message_id;
    public int $message_thread_id;
    public User $from;
    public Chat $chat;
    public int $date;
    public ?string $text;
    public string $caption;
    /** @var array<PhotoSize> $photo */
    public array $photo;
}

/**
 * Represents Telegram update to webhook.
 * https://core.telegram.org/bots/api#update
 */
class Update extends Data
{
    public int $update_id;
    public ?Message $message;
}
