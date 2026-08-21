<?php

namespace App\Console\Commands;

use App\Enums\PlanStatus;
use App\Models\Plan;
use Illuminate\Console\Command;

class ExpireReadingPlans extends Command
{
    protected $signature = 'plans:expire';

    protected $description = '期限切れの読書計画を自動的に失効させる';

    public function handle(): int
    {
        Plan::where('target_date', '<', today())
            ->where('status', '!=', PlanStatus::Completed)
            ->update([
                'status' => PlanStatus::Overdue,
            ]);

        return self::SUCCESS;
    }
}
