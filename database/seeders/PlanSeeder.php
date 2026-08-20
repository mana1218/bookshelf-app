<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\PlanStatus;
use App\Models\Book;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today();

        $users = User::all();
        $books = Book::all();

        $yamada = User::where('name', '山田太郎')->first();

        if (!$yamada) {
            $yamada = $users->first();
        }

        Plan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[0]->id,
            'target_date' => $today->copy()->addDays(3),
            'status' => PlanStatus::Reading,
        ]);

        Plan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[1]->id,
            'target_date' => $today->copy()->addDay(),
            'status' => PlanStatus::Reading,
        ]);

        Plan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[2]->id,
            'target_date' => $today->copy(),
            'status' => PlanStatus::Reading,
        ]);

        Plan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[3]->id,
            'target_date' => $today->copy()->subDay(),
            'status' => PlanStatus::Reading,
        ]);

        Plan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[4]->id,
            'target_date' => $today->copy()->addDays(7),
            'status' => PlanStatus::Reading,
        ]);

        Plan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[5]->id,
            'target_date' => $today->copy()->subDays(3),
            'status' => PlanStatus::Completed,
        ]);

        if ($users->count() >= 2) {
            $otherUser = $users->firstWhere('id', '!=', $yamada->id);

            Plan::create([
                'user_id' => $otherUser->id,
                'book_id' => $books[6]->id,
                'target_date' => $today->copy()->addDays(5),
                'status' => PlanStatus::Reading,
            ]);
        }
    }
}
