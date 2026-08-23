<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
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
            'isbn' => '978' . str_pad(
                (string) random_int(0, 9999999999),
                10,
                '0',
                STR_PAD_LEFT
            ),
            'published_date' => '2020-01-01',
        ]);
    }

    private function createReview(User $user, Book $book): Review
    {
        return Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テストレビュー',
        ]);
    }

    public function test_authenticated_user_can_create_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/books/{$book->id}/reviews", [
                'rating' => 4,
                'comment' => 'APIからのレビュー',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => 'APIからのレビュー',
        ]);
    }

    public function test_guest_cannot_create_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->postJson("/api/v1/books/{$book->id}/reviews", [
            'rating' => 4,
            'comment' => 'レビュー',
        ]);

        $response->assertUnauthorized();
    }

    public function test_owner_can_update_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $review = $this->createReview($user, $book);

        $response = $this->actingAs($user)
            ->putJson("/api/v1/reviews/{$review->id}", [
                'rating' => 3,
                'comment' => '更新レビュー',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '更新レビュー',
        ]);
    }

    public function test_other_user_cannot_update_review(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $book = $this->createBook($owner);
        $review = $this->createReview($owner, $book);

        $response = $this->actingAs($otherUser)
            ->putJson("/api/v1/reviews/{$review->id}", [
                'rating' => 3,
                'comment' => '不正な更新',
            ]);

        $response->assertForbidden();
    }

    public function test_owner_can_delete_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $review = $this->createReview($user, $book);

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/reviews/{$review->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_guest_cannot_delete_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $review = $this->createReview($user, $book);

        $response = $this->deleteJson("/api/v1/reviews/{$review->id}");

        $response->assertUnauthorized();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }
}