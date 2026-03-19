<?php

namespace App\Enums;

enum CompletionStatus: int
{
    case NotStarted = 1;
    case Edit = 2;
    case Review = 3;
    case Completed = 4;

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Not Started',
            self::Edit => 'Edit',
            self::Review => 'Revi1ew',
            self::Completed => 'Completed',
        };
    }
}
