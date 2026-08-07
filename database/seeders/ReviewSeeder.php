<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = [
            [
                'user_id' => 1,
                'book_id' => 1,
                'rating' => 3,
                'comment' => 'とても面白いです。',
            ],
            [
                'user_id' => 2,
                'book_id' => 2,
                'rating' => 4,
                'comment' => '読みやすかったです。',
            ],
            [
                'user_id' => 3,
                'book_id' => 3,
                'rating' => 5,
                'comment' => '名作です。',
            ],
            [
                'user_id' => 4,
                'book_id' => 4,
                'rating' => 3,
                'comment' => '他の人にもおすすめしたいです。',
            ],
            [
                'user_id' => 5,
                'book_id' => 5,
                'rating' => 4,
                'comment' => 'とても面白いです。',
            ],
            [
                'user_id' => 1,
                'book_id' => 6,
                'rating' => 5,
                'comment' => '読みやすかったです。',
            ],
            [
                'user_id' => 2,
                'book_id' => 7,
                'rating' => 3,
                'comment' => '名作です。',
            ],
            [
                'user_id' => 3,
                'book_id' => 8,
                'rating' => 4,
                'comment' => '他の人にもおすすめしたいです。',
            ],
            [
                'user_id' => 4,
                'book_id' => 9,
                'rating' => 5,
                'comment' => 'とても面白いです。',
            ],
            [
                'user_id' => 5,
                'book_id' => 10,
                'rating' => 3,
                'comment' => '読みやすかったです。',
            ],
            [
                'user_id' => 1,
                'book_id' => 11,
                'rating' => 4,
                'comment' => '名作です。',
            ],
            [
                'user_id' => 2,
                'book_id' => 1,
                'rating' => 5,
                'comment' => '他の人にもおすすめしたいです。',
            ],
            [
                'user_id' => 3,
                'book_id' => 2,
                'rating' => 3,
                'comment' => 'とても面白いです。',
            ],
            [
                'user_id' => 4,
                'book_id' => 3,
                'rating' => 4,
                'comment' => '読みやすかったです。',
            ],
            [
                'user_id' => 5,
                'book_id' => 4,
                'rating' => 5,
                'comment' => '名作です。',
            ],
            [
                'user_id' => 1,
                'book_id' => 5,
                'rating' => 3,
                'comment' => '他の人にもおすすめしたいです。',
            ],
            [
                'user_id' => 2,
                'book_id' => 6,
                'rating' => 4,
                'comment' => 'とても面白いです。',
            ],
            [
                'user_id' => 3,
                'book_id' => 7,
                'rating' => 5,
                'comment' => '読みやすかったです。',
            ],
            [
                'user_id' => 4,
                'book_id' => 8,
                'rating' => 3,
                'comment' => '名作です。',
            ],
            [
                'user_id' => 5,
                'book_id' => 9,
                'rating' => 4,
                'comment' => '他の人にもおすすめしたいです。',
            ],
            [
                'user_id' => 1,
                'book_id' => 10,
                'rating' => 5,
                'comment' => 'とても面白いです。',
            ],
            [
                'user_id' => 2,
                'book_id' => 11,
                'rating' => 3,
                'comment' => '読みやすかったです。',
            ],
            [
                'user_id' => 3,
                'book_id' => 1,
                'rating' => 4,
                'comment' => '名作です。',
            ],
            [
                'user_id' => 4,
                'book_id' => 2,
                'rating' => 5,
                'comment' => '他の人にもおすすめしたいです。',
            ],
            [
                'user_id' => 5,
                'book_id' => 3,
                'rating' => 3,
                'comment' => 'とても面白いです。',
            ],
            [
                'user_id' => 1,
                'book_id' => 4,
                'rating' => 4,
                'comment' => '読みやすかったです。',
            ],
            [
                'user_id' => 2,
                'book_id' => 5,
                'rating' => 5,
                'comment' => '名作です。',
            ],
            [
                'user_id' => 3,
                'book_id' => 6,
                'rating' => 3,
                'comment' => '他の人にもおすすめしたいです。',
            ],
            [
                'user_id' => 4,
                'book_id' => 7,
                'rating' => 4,
                'comment' => 'とても面白いです。',
            ],
            [
                'user_id' => 5,
                'book_id' => 8,
                'rating' => 5,
                'comment' => '読みやすかったです。',
            ],
            [
                'user_id' => 1,
                'book_id' => 9,
                'rating' => 3,
                'comment' => '名作です。',
            ],
            [
                'user_id' => 2,
                'book_id' => 10,
                'rating' => 4,
                'comment' => '他の人にもおすすめしたいです。',
            ],
            [
                'user_id' => 3,
                'book_id' => 11,
                'rating' => 5,
                'comment' => 'とても面白いです。',
            ],
            [
                'user_id' => 4,
                'book_id' => 1,
                'rating' => 3,
                'comment' => '読みやすかったです。',
            ],
            [
                'user_id' => 5,
                'book_id' => 2,
                'rating' => 4,
                'comment' => '名作です。',
            ],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}
