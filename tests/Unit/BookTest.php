<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Plan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
        public function test_book_belongs_to_user(): void
    {
        $book = new Book();

        $this->assertInstanceOf(
            BelongsTo::class,
            $book->user()
        );
    }

    public function test_book_belongs_to_many_genres(): void
    {
        $book = new Book();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $book->genres()
        );
    }

    public function test_book_has_many_reviews(): void
    {
        $book = new Book();

        $this->assertInstanceOf(
            HasMany::class,
            $book->reviews()
        );
    }

    public function test_book_has_many_plans(): void
    {
        $book = new Book();

        $this->assertInstanceOf(
            HasMany::class,
            $book->plans()
        );
    }

    public function test_book_belongs_to_many_favorite_users(): void
    {
        $book = new Book();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $book->favoriteBooks()
        );
    }

    public function test_book_has_expected_fillable_attributes(): void
    {
        $book = new Book();

        $this->assertEquals([
            'user_id',
            'title',
            'author',
            'isbn',
            'published_date',
            'description',
            'image_url',
        ], $book->getFillable());
    }

    public function test_published_date_is_cast_to_date(): void
    {
        $book = new Book();

        $this->assertEquals(
            'date',
            $book->getCasts()['published_date']
        );
    }
}
