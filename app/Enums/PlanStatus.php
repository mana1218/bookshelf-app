<?php

namespace App\Enums;

enum PlanStatus: string
{
    case Reading = 'reading';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Reading => '進行中',
            self::Completed => '読了',
            self::Overdue => '期限切れ'
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Reading => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::Overdue => 'bg-red-100 text-red-800'
        };
    }
}