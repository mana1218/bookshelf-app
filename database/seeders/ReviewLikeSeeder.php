<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;


class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = Review::all();

        foreach ($reviews as $review) {
            $users = User::where('id', '!=', $review->user_id)->get();

            $count = rand(0, 3);

            $likeUsers = $users->random(min($count, $users->count()));

            $review->likedByUsers()->syncWithoutDetaching(
                $likeUsers->pluck('id')->toArray()
            );
        }
    }
}
