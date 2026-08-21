<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

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
            'isbn' => '978' . str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT),
            'published_date' => '2020-01-01',
        ]);
    }

    private function createReview(User $user, Book $book): Review
    {
        return Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テストコメント',
        ]);
    }

    public function test_guest_cannot_like_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $review = $this->createReview($user, $book);

        $response = $this->post("/reviews/{$review->id}/like");

        $response->assertRedirect('/login');
    }

    public function test_user_can_like_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $review = $this->createReview($user, $book);

        $response = $this->actingAs($user)
            ->post("/reviews/{$review->id}/like");

        $response->assertRedirect();

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_user_can_unlike_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $review = $this->createReview($user, $book);

        $user->likedReviews()->attach($review->id);

        $response = $this->actingAs($user)
            ->post("/reviews/{$review->id}/like");

        $response->assertRedirect();

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }
}
