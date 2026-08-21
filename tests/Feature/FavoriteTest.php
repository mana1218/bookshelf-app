<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
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
            'isbn' => '9784000000001',
            'published_date' => '2020-01-01',
        ]);
    }

    public function test_guest_cannot_access_favorites(): void
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_favorites(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/favorites');

        $response->assertOk();
        $response->assertViewIs('favorites.index');
    }

    public function test_user_can_add_favorite(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->actingAs($user)
            ->post("/books/{$book->id}/favorites");

        $response->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_user_can_remove_favorite(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)
            ->post("/books/{$book->id}/favorites");

        $response->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_guest_cannot_toggle_favorite(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->post("/books/{$book->id}/favorites");

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }
}