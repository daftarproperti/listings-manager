<?php

namespace App\DTO\Telegram;

/**
 * https://core.telegram.org/bots/api#chat
 */
class Chat
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
class User
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
class PhotoSize
{
    public string $file_id;
    public string $file_unique_id;
}

/**
 * https://core.telegram.org/bots/api#message
 */
class Message
{
    public int $message_id;
    public int $message_thread_id;
    public User $from;
    public Chat $chat;
    public int $date;
    public string $text;
    public string $caption;
    /** @var array<PhotoSize> $photo */
    public array $photo;
}

/**
 * Represents Telegram update to webhook.
 * https://core.telegram.org/bots/api#update
 */
class Update
{
    public int $update_id;
    public Message $message;
}
