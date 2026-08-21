<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessTest extends TestCase
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

    public function test_home_page_can_be_accessed(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_book_index_page_can_be_accessed(): void
    {
        $response = $this->get('/books');

        $response->assertStatus(200);
    }

    public function test_ranking_page_can_be_accessed(): void
    {
        $response = $this->get('/ranking');

        $response->assertStatus(200);
    }

    public function test_book_show_page_can_be_accessed(): void
    {
        $user = $this->createUser();
    
        $book = \App\Models\Book::create([
            'user_id' => $user->id,
            'title' => 'テスト本',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2020-01-01',
        ]);
    
        $response = $this->get("/books/{$book->id}");
    
        $response->assertStatus(200);
    }

    public function test_genres_page_requires_authentication(): void
    {
        $response = $this->get('/genres');

        $response->assertRedirect('/login');
    }

    public function test_book_create_page_requires_authentication(): void
    {
        $response = $this->get('/books/create');

        $response->assertRedirect('/login');
    }

    public function test_favorites_page_requires_authentication(): void
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }

    public function test_reading_plans_page_requires_authentication(): void
    {
        $response = $this->get('/reading-plans');

        $response->assertRedirect('/login');
    }

    public function test_reports_page_requires_authentication(): void
    {
        $response = $this->get('/reports');

        $response->assertRedirect('/login');
    }

    public function test_notifications_page_requires_authentication(): void
    {
        $response = $this->get('/notifications');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_genres_page(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/genres');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_book_create_page(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/books/create');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_favorites_page(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_reading_plans_page(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/reading-plans');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_reports_page(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_notifications_page(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertStatus(200);
    }
}