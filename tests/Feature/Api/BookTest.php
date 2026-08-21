<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::create([
            'name' => 'テストユーザー',
            'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'),
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
                (string) random_int(1, 9999999999),
                10,
                '0',
                STR_PAD_LEFT
            ),
            'published_date' => '2020-01-01',
        ]);
    }

    public function test_guest_can_get_book_list(): void
    {
        $user = $this->createUser();
        $this->createBook($user);

        $response = $this->getJson('/api/v1/books');

        $response->assertOk();
    }

    public function test_guest_can_get_book_detail(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'title' => 'テスト本',
            ]);
    }

    public function test_authenticated_user_can_create_book(): void
    {
        $user = $this->createUser();
        $genre = Genre::create([
            'name' => '小説',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', [
                'title' => '新しい本',
                'author' => '新しい著者',
                'isbn' => '9781234567890',
                'published_date' => '2024-01-01',
                'genres' => [$genre->id],
                'image_url' => null,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => '新しい本',
            'isbn' => '9781234567890',
        ]);
    }

    public function test_guest_cannot_create_book(): void
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => '新しい本',
            'author' => '新しい著者',
            'isbn' => '9781234567890',
            'published_date' => '2024-01-01',
            'genres' => [],
        ]);

        $response->assertUnauthorized();
    }

    public function test_owner_can_update_book(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $genre = Genre::create([
            'name' => '小説',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", [
                'title' => '変更した本',
                'author' => '変更した著者',
                'isbn' => '9781234567890',
                'published_date' => '2024-01-01',
                'genres' => [$genre->id],
                'image_url' => null,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '変更した本',
        ]);
    }

    public function test_other_user_cannot_update_book(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();
        $book = $this->createBook($owner);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", [
                'title' => '変更した本',
                'author' => '変更した著者',
                'isbn' => '9781234567890',
                'published_date' => '2024-01-01',
                'genres' => [],
                'image_url' => null,
            ]);

        $response->assertForbidden();
    }

    public function test_owner_can_delete_book(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/books/{$book->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_other_user_cannot_delete_book(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();
        $book = $this->createBook($owner);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/v1/books/{$book->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    public function test_can_get_book_by_isbn(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'APIテスト本',
                            'authors' => ['APIテスト著者'],
                            'publishedDate' => '2020-01-01',
                            'description' => 'APIから取得した説明',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/image.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/v1/books/isbn/9781234567890');

        $response->assertOk()
            ->assertJson([
                'isbn' => '9781234567890',
                'title' => 'APIテスト本',
                'author' => 'APIテスト著者',
                'published_date' => '2020-01-01',
                'description' => 'APIから取得した説明',
                'image_url' => 'https://example.com/image.jpg',
            ]);

        Http::assertSent(function ($request) {
            return str_contains(
                $request->url(),
                'https://www.googleapis.com/books/v1/volumes'
            );
        });
    }

    public function test_invalid_isbn_returns_validation_error(): void
    {
        Http::fake();

        $response = $this->getJson('/api/v1/books/isbn/123');

        $response->assertStatus(422);

        Http::assertNothingSent();
    }

    public function test_isbn_api_failure_returns_server_error(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' =>
                Http::response([], 500),
        ]);

        $response = $this->getJson('/api/v1/books/isbn/9781234567890');

        $response->assertStatus(500)
            ->assertJson([
                'message' => '書籍情報の取得に失敗しました。',
            ]);
    }

    public function test_isbn_book_not_found_returns_not_found(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' =>
                Http::response([
                    'totalItems' => 0,
                ], 200),
        ]);

        $response = $this->getJson('/api/v1/books/isbn/9781234567890');

        $response->assertNotFound()
            ->assertJson([
                'message' => '書籍情報が見つかりませんでした。',
            ]);
    }
}