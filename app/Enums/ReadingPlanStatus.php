<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case Pending = 'pending';
    case Reading = 'reading';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => '未着手',
            self::Reading => '進行中',
            self::Completed => '読了',
        };
    }
}