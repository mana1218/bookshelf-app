<?php

namespace Tests\Feature;

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
            'isbn' => '978' . str_pad((string) random_int(1, 9999999999), 10, '0', STR_PAD_LEFT),
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

    public function test_authenticated_user_can_create_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->actingAs($user)
            ->post("/books/{$book->id}/reviews", [
                'rating' => 5,
                'comment' => 'とても面白い本でした。',
            ]);

        $response->assertRedirect("/books/{$book->id}");

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても面白い本でした。',
        ]);
    }

    public function test_guest_cannot_create_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->post("/books/{$book->id}/reviews", [
            'rating' => 5,
            'comment' => 'テストコメント',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_owner_can_access_review_edit(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $review = $this->createReview($user, $book);

        $response = $this->actingAs($user)
            ->get("/reviews/{$review->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('reviews.edit');
    }

    public function test_other_user_cannot_access_review_edit(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $book = $this->createBook($owner);
        $review = $this->createReview($owner, $book);

        $response = $this->actingAs($otherUser)
            ->get("/reviews/{$review->id}/edit");

        $response->assertForbidden();
    }

    public function test_owner_can_update_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $review = $this->createReview($user, $book);

        $response = $this->actingAs($user)
            ->put("/reviews/{$review->id}", [
                'rating' => 4,
                'comment' => '変更後のコメント',
            ]);

        $response->assertRedirect("/books/{$book->id}");

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => '変更後のコメント',
        ]);
    }

    public function test_other_user_cannot_update_review(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $book = $this->createBook($owner);
        $review = $this->createReview($owner, $book);

        $response = $this->actingAs($otherUser)
            ->put("/reviews/{$review->id}", [
                'rating' => 1,
                'comment' => '勝手に変更',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => 'テストコメント',
        ]);
    }

    public function test_owner_can_delete_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $review = $this->createReview($user, $book);

        $response = $this->actingAs($user)
            ->delete("/reviews/{$review->id}");

        $response->assertRedirect("/books/{$book->id}");

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_other_user_cannot_delete_review(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $book = $this->createBook($owner);
        $review = $this->createReview($owner, $book);

        $response = $this->actingAs($otherUser)
            ->delete("/reviews/{$review->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_review_validation_fails_with_invalid_rating(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->actingAs($user)
            ->post("/books/{$book->id}/reviews", [
                'rating' => 6,
                'comment' => 'テストコメント',
            ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_review_validation_fails_with_empty_comment(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->actingAs($user)
            ->post("/books/{$book->id}/reviews", [
                'rating' => 5,
                'comment' => '',
            ]);

        $response->assertSessionHasErrors('comment');
    }
}
