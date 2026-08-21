<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    private function createUser(): User
    {
        return User::create([
            'name' => 'テストユーザー',
            'email' => uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }

    private function createBook(User $user): Book
    {
        return Book::create([
            'user_id' => $user->id,
            'title' => 'テスト本',
            'author' => 'テスト著者',
            'isbn' => str_pad(
                (string) random_int(1000000000000, 9999999999999),
                13,
                '0',
                STR_PAD_LEFT
            ),
            'published_date' => '2020-01-01',
        ]);
    }

    public function test_review_has_user(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '面白かったです。',
        ]);

        $this->assertTrue(
            $review->user->is($user)
        );
    }

    public function test_review_has_book(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '面白かったです。',
        ]);

        $this->assertTrue(
            $review->book->is($book)
        );
    }

    public function test_review_has_liked_users(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '面白かったです。',
        ]);

        $likedUser = User::create([
            'name' => 'いいねユーザー',
            'email' => uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $review->likedByUsers()->attach($likedUser->id);

        $this->assertTrue(
            $review->likedByUsers->contains($likedUser)
        );
    }
}
