<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property integer $chatId
 * @property boolean $allowed
 * @property string $sampleMessage
*/
class TelegramAllowlistGroup extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'telegram_allowlist_groups';
    protected $fillable = ['chatId', 'allowed', 'sampleMessage'];

}
