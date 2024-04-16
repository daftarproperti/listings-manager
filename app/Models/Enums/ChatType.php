<?php
namespace App\Models\Enums;

enum ChatType: string
{
    case PRIVATE = 'private';
    case GROUP = 'group';
}
