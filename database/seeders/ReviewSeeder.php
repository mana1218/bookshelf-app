<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $comments = [
            1 => 'あまりおすすめしません。',
            2 => '少し物足りなく感じました。',
            3 => '面白いです。',
            4 => '読みやすかったです。',
            5 => '名作でした！',
        ];

        foreach ($books as $book) {
            $count = rand(2, 4);

            for ($i = 0; $i < $count; $i++) {
                $rating = rand(1, 5);

                Review::create([
                    'user_id' => $users->random()->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => $comments[$rating],
                ]);
            }
        }
    }
}
