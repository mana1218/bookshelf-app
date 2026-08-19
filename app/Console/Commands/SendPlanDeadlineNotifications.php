<?php

namespace App\Console\Commands;

use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Notifications\PlanDeadlineNotification;
use Illuminate\Console\Command;

class SendPlanDeadlineNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plans:deadline-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '読書計画の期限通知を送信する';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $plans = Plan::where('status', '!=', PlanStatus::Completed)
            ->get();

        foreach ($plans as $plan) {
            $daysRemaining = today()->diffInDays(
                $plan->target_date,
                false
            );

            if ($daysRemaining === 3) {
                $type = 'three_days_before';
                $message = '読書計画の期限まであと3日です。進捗を確認してね！';
            } elseif ($daysRemaining === 0) {
                $type = 'on_day_before';
                $message = '読書計画の期限は今日です。頑張って！';
            } elseif ($daysRemaining < 0) {
                $type = 'expired';
                $message = '読書計画の期限を過ぎました。必要に応じて計画を見直してみよう！';
            } else {
                continue;
            }

            $alreadyNotified = $plan->user
                ->notifications()
                ->where('type', PlanDeadlineNotification::class)
                ->where('data->plan_id', $plan->id)
                ->where('data->type', $type)
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $plan->user->notify(
                new PlanDeadlineNotification($plan, $message, $type)
            );
        }

        return self::SUCCESS;
    }
}
