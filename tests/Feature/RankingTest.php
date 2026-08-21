<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
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

    private function createBook(User $user, string $title = 'テスト本'): Book
    {
        return Book::create([
            'user_id' => $user->id,
            'title' => $title,
            'author' => 'テスト著者',
            'isbn' => '978' . str_pad((string) mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
            'published_date' => '2020-01-01',
        ]);
    }

    private function createReview(User $user, Book $book, int $rating): Review
    {
        return Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => $rating,
            'comment' => 'テストコメント',
        ]);
    }

    public function test_guest_can_access_ranking(): void
    {
        $response = $this->get('/ranking');

        $response->assertOk();
        $response->assertViewIs('ranking.index');
    }

    public function test_books_are_ranked_by_average_rating(): void
    {
        $user = $this->createUser();

        $lowBook = $this->createBook($user, '低評価の本');
        $highBook = $this->createBook($user, '高評価の本');

        $this->createReview($user, $lowBook, 2);
        $this->createReview($user, $highBook, 5);

        $response = $this->get('/ranking');

        $response->assertOk();

        $rankedBooks = $response->viewData('rankedBooks');

        $this->assertSame('高評価の本', $rankedBooks->first()->title);
        $this->assertSame('低評価の本', $rankedBooks->last()->title);
    }

    public function test_book_without_reviews_is_not_ranked(): void
    {
        $user = $this->createUser();

        $reviewedBook = $this->createBook($user, 'レビューあり');
        $unreviewedBook = $this->createBook($user, 'レビューなし');

        $this->createReview($user, $reviewedBook, 5);

        $response = $this->get('/ranking');

        $rankedBooks = $response->viewData('rankedBooks');

        $this->assertTrue(
            $rankedBooks->contains('id', $reviewedBook->id)
        );

        $this->assertFalse(
            $rankedBooks->contains('id', $unreviewedBook->id)
        );
    }

    public function test_ranking_contains_at_most_ten_books(): void
    {
        $user = $this->createUser();

        for ($i = 1; $i <= 11; $i++) {
            $book = $this->createBook($user, "テスト本{$i}");

            $this->createReview($user, $book, 5);
        }

        $response = $this->get('/ranking');

        $rankedBooks = $response->viewData('rankedBooks');

        $this->assertCount(10, $rankedBooks);
    }
}