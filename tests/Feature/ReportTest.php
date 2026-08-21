<?php

namespace Tests\Feature;

use App\Enums\PlanStatus;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Plan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
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
            'isbn' => '978' . str_pad(
                (string) random_int(0, 9999999999),
                10,
                '0',
                STR_PAD_LEFT
            ),
            'published_date' => '2020-01-01',
        ]);
    }

    private function createReview(
        User $user,
        Book $book,
        int $rating
    ): Review {
        return Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => $rating,
            'comment' => 'テストコメント',
        ]);
    }

    private function createPlan(
        User $user,
        Book $book,
        PlanStatus $status
    ): Plan {
        return Plan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today(),
            'status' => $status,
        ]);
    }

    public function test_guest_cannot_access_report(): void
    {
        $response = $this->get('/reports');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_report(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/reports');

        $response->assertOk();
        $response->assertViewIs('reports.index');
        $response->assertViewHas('stats');
    }

    public function test_report_calculates_summary(): void
    {
        $user = $this->createUser();

        $book1 = $this->createBook($user, '本1');
        $book2 = $this->createBook($user, '本2');

        $this->createReview($user, $book1, 5);
        $this->createReview($user, $book2, 3);

        $this->createPlan($user, $book1, PlanStatus::Completed);
        $this->createPlan($user, $book2, PlanStatus::Completed);

        $response = $this->actingAs($user)
            ->get('/reports');

        $stats = $response->viewData('stats');

        $this->assertSame(2, $stats['summary']['total_reviews']);
        $this->assertSame(2, $stats['summary']['books_read']);
        $this->assertEquals(4, $stats['summary']['average_rating']);
    }

    public function test_report_calculates_rating_distribution(): void
    {
        $user = $this->createUser();

        $book1 = $this->createBook($user, '本1');
        $book2 = $this->createBook($user, '本2');
        $book3 = $this->createBook($user, '本3');

        $this->createReview($user, $book1, 5);
        $this->createReview($user, $book2, 5);
        $this->createReview($user, $book3, 3);

        $response = $this->actingAs($user)
            ->get('/reports');

        $stats = $response->viewData('stats');

        $this->assertSame([0, 0, 1, 0, 2], $stats['rating_distribution']->values()->all());
    }

    public function test_report_contains_top_rated_books(): void
    {
        $user = $this->createUser();

        $book = $this->createBook($user, 'お気に入りの本');

        $this->createReview($user, $book, 5);

        $response = $this->actingAs($user)
            ->get('/reports');

        $stats = $response->viewData('stats');

        $this->assertTrue(
            $stats['top_rated_books']->contains(function ($item) use ($book) {
                return $item['id'] === $book->id
                    && $item['title'] === 'お気に入りの本'
                    && $item['rating'] === 5;
            })
        );
    }

    public function test_report_does_not_include_other_users_data(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();

        $book = $this->createBook($user, '自分の本');
        $otherBook = $this->createBook($otherUser, '他人の本');

        $this->createReview($user, $book, 5);
        $this->createReview($otherUser, $otherBook, 1);

        $response = $this->actingAs($user)
            ->get('/reports');

        $stats = $response->viewData('stats');

        $this->assertSame(1, $stats['summary']['total_reviews']);
        $this->assertEquals(5, $stats['summary']['average_rating']);

        $this->assertFalse(
            $stats['top_rated_books']->contains(function ($item) use ($otherBook) {
                return $item['id'] === $otherBook->id;
            })
        );
    }

    public function test_report_contains_genre_ratings(): void
    {
        $user = $this->createUser();

        $book = $this->createBook($user);

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $book->genres()->attach($genre->id);

        $this->createReview($user, $book, 5);

        $response = $this->actingAs($user)
            ->get('/reports');

        $stats = $response->viewData('stats');

        $this->assertTrue(
            $stats['genre_ratings']->contains(function ($item) use ($genre) {
                return $item['id'] === $genre->id
                    && $item['name'] === 'テストジャンル'
                    && (float) $item['average_rating'] === 5.0
                    && $item['count'] === 1;
            })
        );
    }
}