<?php

namespace App\Enums;

enum CharacterSet: string
{
    case Numeric = 'numeric';
    case Alphabetic = 'alphabetic';
    case Alphanumeric = 'alphanumeric';

    public function getCharacters(): string
    {
        return match ($this) {
            self::Numeric => '123456789',
            self::Alphabetic => 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz',
            self::Alphanumeric => '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz',
        };
    }
}
