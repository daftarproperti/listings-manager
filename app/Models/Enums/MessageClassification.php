<?php
namespace App\Models\Enums;

enum MessageClassification: string
{
    case LISTING = 'LISTING';
    case BUYER_REQUEST = 'BUYER_REQUEST';
}
