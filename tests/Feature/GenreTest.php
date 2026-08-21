<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
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

    public function test_guest_can_access_genre_index(): void
    {
        $response = $this->get('/genres');

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_genre_create(): void
    {
        $response = $this->get('/genres/create');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_genre_index(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/genres');

        $response->assertStatus(200);
        $response->assertViewIs('genres.index');
    }

    public function test_authenticated_user_can_access_genre_create(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/genres/create');

        $response->assertStatus(200);
        $response->assertViewIs('genres.create');
    }

    public function test_authenticated_user_can_create_genre(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->post('/genres', [
                'name' => 'テストジャンル',
            ]);

        $response->assertRedirect('/genres');

        $this->assertDatabaseHas('genres', [
            'name' => 'テストジャンル',
        ]);
    }

    public function test_authenticated_user_can_access_genre_show(): void
    {
        $user = $this->createUser();

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $response = $this->actingAs($user)
            ->get("/genres/{$genre->id}");

        $response->assertStatus(200);
        $response->assertViewIs('genres.show');
    }

    public function test_authenticated_user_can_access_genre_edit(): void
    {
        $user = $this->createUser();

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $response = $this->actingAs($user)
            ->get("/genres/{$genre->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('genres.edit');
    }

    public function test_authenticated_user_can_update_genre(): void
    {
        $user = $this->createUser();

        $genre = Genre::create([
            'name' => '変更前',
        ]);

        $response = $this->actingAs($user)
            ->put("/genres/{$genre->id}", [
                'name' => '変更後',
            ]);

        $response->assertRedirect('/genres');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '変更後',
        ]);
    }

    public function test_authenticated_user_can_delete_genre(): void
    {
        $user = $this->createUser();

        $genre = Genre::create([
            'name' => '削除するジャンル',
        ]);

        $response = $this->actingAs($user)
            ->delete("/genres/{$genre->id}");

        $response->assertRedirect('/genres');

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }
}