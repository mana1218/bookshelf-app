<?php

namespace Tests\Feature\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_get_genre_list(): void
    {
        Genre::create([
            'name' => '小説',
        ]);

        $response = $this->getJson('/api/v1/genres');

        $response->assertOk()
            ->assertJsonFragment([
                'name' => '小説',
            ]);
    }

    public function test_guest_can_create_genre(): void
    {
        $response = $this->postJson('/api/v1/genres', [
            'name' => 'ミステリー',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('genres', [
            'name' => 'ミステリー',
        ]);
    }

    public function test_guest_can_get_genre_detail(): void
    {
        $genre = Genre::create([
            'name' => 'SF',
        ]);

        $response = $this->getJson("/api/v1/genres/{$genre->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'SF',
            ]);
    }

    public function test_guest_can_update_genre(): void
    {
        $genre = Genre::create([
            'name' => '旧ジャンル名',
        ]);

        $response = $this->putJson("/api/v1/genres/{$genre->id}", [
            'name' => '新ジャンル名',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'name' => '新ジャンル名',
            ]);

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '新ジャンル名',
        ]);
    }

    public function test_guest_can_delete_genre(): void
    {
        $genre = Genre::create([
            'name' => '削除ジャンル',
        ]);

        $response = $this->deleteJson("/api/v1/genres/{$genre->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }
}