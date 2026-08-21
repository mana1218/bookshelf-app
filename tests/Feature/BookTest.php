<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
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
            'isbn' => '9784000000001',
            'published_date' => '2020-01-01',
            'description' => 'テスト説明',
            'image_url' => null,
        ]);
    }

    public function test_guest_can_access_book_index(): void
    {
        $response = $this->get('/books');

        $response->assertStatus(200);
    }

    public function test_guest_can_access_book_show(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->get("/books/{$book->id}");

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_book_create(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/books/create');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_book(): void
    {
        $user = $this->createUser();

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $response = $this->actingAs($user)->post('/books', [
            'title' => '新しい本',
            'author' => '新しい著者',
            'isbn' => '9784000000002',
            'published_date' => '2024-01-01',
            'description' => '本の説明',
            'image_url' => null,
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect('/books');

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => '新しい本',
            'author' => '新しい著者',
            'isbn' => '9784000000002',
        ]);
    }

    public function test_owner_can_access_book_edit(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->actingAs($user)
            ->get("/books/{$book->id}/edit");

        $response->assertStatus(200);
    }

    public function test_owner_can_update_book(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $response = $this->actingAs($user)
            ->put("/books/{$book->id}", [
                'title' => '変更した本',
                'author' => '変更した著者',
                'isbn' => '9784000000003',
                'published_date' => '2024-01-01',
                'description' => '変更後の説明',
                'image_url' => null,
                'genres' => [$genre->id],
            ]);

        $response->assertRedirect('/books');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '変更した本',
            'author' => '変更した著者',
            'isbn' => '9784000000003',
        ]);
    }

    public function test_owner_can_delete_book(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->actingAs($user)
            ->delete("/books/{$book->id}");

        $response->assertRedirect('/books');

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_other_user_cannot_access_book_edit(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $book = $this->createBook($owner);

        $response = $this->actingAs($otherUser)
            ->get("/books/{$book->id}/edit");

        $response->assertForbidden();
    }

    public function test_other_user_cannot_update_book(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $book = $this->createBook($owner);

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $response = $this->actingAs($otherUser)
            ->put("/books/{$book->id}", [
                'title' => '勝手に変更',
                'author' => '勝手に変更',
                'isbn' => '9784000000004',
                'published_date' => '2024-01-01',
                'description' => '勝手に変更',
                'image_url' => null,
                'genres' => [$genre->id],
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'テスト本',
        ]);
    }

    public function test_other_user_cannot_delete_book(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $book = $this->createBook($owner);

        $response = $this->actingAs($otherUser)
            ->delete("/books/{$book->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    public function test_user_can_search_books_by_title(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    
        Book::create([
            'user_id' => $user->id,
            'title' => 'Laravel入門',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2020-01-01',
        ]);
    
        Book::create([
            'user_id' => $user->id,
            'title' => 'PHP入門',
            'author' => '別の著者',
            'isbn' => '9781234567891',
            'published_date' => '2021-01-01',
        ]);
    
        $response = $this->get('/books?keyword=Laravel');
    
        $response->assertOk()
            ->assertViewHas('books', function ($books) {
                return $books->contains('title', 'Laravel入門')
                    && !$books->contains('title', 'PHP入門');
            });
    }
    
    public function test_user_can_filter_books_by_genre(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    
        $genre = Genre::create([
            'name' => '小説',
        ]);
    
        $otherGenre = Genre::create([
            'name' => 'ビジネス',
        ]);
    
        $book = Book::create([
            'user_id' => $user->id,
            'title' => '小説の本',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2020-01-01',
        ]);
    
        $otherBook = Book::create([
            'user_id' => $user->id,
            'title' => 'ビジネスの本',
            'author' => '別の著者',
            'isbn' => '9781234567891',
            'published_date' => '2021-01-01',
        ]);
    
        $book->genres()->attach($genre->id);
        $otherBook->genres()->attach($otherGenre->id);
    
        $response = $this->get("/books?genre={$genre->id}");
    
        $response->assertOk()
            ->assertViewHas('books', function ($books) use ($book, $otherBook) {
                return $books->contains('id', $book->id)
                    && !$books->contains('id', $otherBook->id);
            });
    }
    
    public function test_user_can_sort_books_by_title(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    
        $bookB = Book::create([
            'user_id' => $user->id,
            'title' => 'Bの本',
            'author' => '著者B',
            'isbn' => '9781234567890',
            'published_date' => '2020-01-01',
        ]);
    
        $bookA = Book::create([
            'user_id' => $user->id,
            'title' => 'Aの本',
            'author' => '著者A',
            'isbn' => '9781234567891',
            'published_date' => '2021-01-01',
        ]);
    
        $response = $this->get('/books?sort=title');
    
        $response->assertOk()
            ->assertViewHas('books', function ($books) use ($bookA, $bookB) {
                return $books->first()->id === $bookA->id
                    && $books->last()->id === $bookB->id;
            });
    }
    
    public function test_user_can_sort_books_by_oldest(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    
        $oldBook = Book::create([
            'user_id' => $user->id,
            'title' => '古い本',
            'author' => '著者A',
            'isbn' => '9781234567890',
            'published_date' => '2020-01-01',
        ]);
    
        $newBook = Book::create([
            'user_id' => $user->id,
            'title' => '新しい本',
            'author' => '著者B',
            'isbn' => '9781234567891',
            'published_date' => '2021-01-01',
        ]);
    
        $response = $this->get('/books?sort=oldest');
    
        $response->assertOk()
            ->assertViewHas('books', function ($books) use ($oldBook, $newBook) {
                return $books->first()->id === $oldBook->id
                    && $books->last()->id === $newBook->id;
            });
    }
}
