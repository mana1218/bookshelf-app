<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $favorites = [
            [
                'user_id' => 1,
                'book_id' => [1, 3, 6, 9, 10]
            ],
            [
                'user_id' => 2,
                'book_id' => [2, 6, 8]
            ],
            [
                'user_id' => 3,
                'book_id' => [4, 10, 11]
            ],
            [
                'user_id' => 4,
                'book_id' => [3, 7, 9, 11]
            ],
            [
                'user_id' => 5,
                'book_id' => [5, 7, 9]
            ],
        ];

        foreach ($favorites as $favorite) {
            $user = User::find($favorite['user_id']);

        $user->favoriteBooks()->syncWithoutDetaching($favorite['book_id']);
        }
    }
}
