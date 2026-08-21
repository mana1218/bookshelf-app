<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SanctumAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_protected_api(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト本',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2020-01-01',
            'description' => 'テスト説明',
            'image_url' => null,
            'genres' => [],
        ]);

        $response->assertStatus(422);
    }

    public function test_guest_cannot_access_protected_api(): void
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト本',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2020-01-01',
            'description' => 'テスト説明',
            'image_url' => null,
            'genres' => [],
        ]);

        $response->assertUnauthorized();
    }
}
