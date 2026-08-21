<?php

namespace Tests\Feature;

use App\Enums\PlanStatus;
use App\Models\Book;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
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
            'isbn' => '978' . str_pad(
                (string) random_int(0, 9999999999),
                10,
                '0',
                STR_PAD_LEFT
            ),
            'published_date' => '2020-01-01',
        ]);
    }

    private function createPlan(
        User $user,
        Book $book,
        PlanStatus $status = PlanStatus::Reading,
        $targetDate = null
    ): Plan {
        return Plan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate ?? today()->addDay(),
            'status' => $status,
        ]);
    }

    public function test_guest_cannot_access_plans(): void
    {
        $response = $this->get('/reading-plans');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_plans(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/reading-plans');

        $response->assertOk();
    }

    public function test_user_can_create_plan(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $response = $this->actingAs($user)
            ->post('/reading-plans', [
                'book_id' => $book->id,
                'target_date' => today()->addWeek()->format('Y-m-d'),
                'status' => PlanStatus::Reading->value,
            ]);

        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_user_can_update_plan(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $plan = $this->createPlan($user, $book);

        $response = $this->actingAs($user)
            ->put("/reading-plans/{$plan->id}", [
                'book_id' => $book->id,
                'target_date' => today()->addWeek()->format('Y-m-d'),
                'status' => PlanStatus::Reading->value,
            ]);

        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'target_date' => today()->addWeek()->format('Y-m-d'),
        ]);
    }

    public function test_user_can_delete_plan(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $plan = $this->createPlan($user, $book);

        $response = $this->actingAs($user)
            ->delete("/reading-plans/{$plan->id}");

        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseMissing('plans', [
            'id' => $plan->id,
        ]);
    }

    public function test_user_can_complete_plan(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $plan = $this->createPlan($user, $book);

        $response = $this->actingAs($user)
            ->post("/reading-plans/{$plan->id}/complete");

        $response->assertRedirect();

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'status' => PlanStatus::Completed->value,
        ]);
    }

    public function test_other_user_cannot_update_plan(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $book = $this->createBook($owner);
        $plan = $this->createPlan($owner, $book);

        $response = $this->actingAs($otherUser)
            ->put("/reading-plans/{$plan->id}", [
                'book_id' => $book->id,
                'target_date' => today()->addWeek()->format('Y-m-d'),
                'status' => PlanStatus::Reading->value,
            ]);

        $response->assertForbidden();
    }

    public function test_other_user_cannot_delete_plan(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $book = $this->createBook($owner);
        $plan = $this->createPlan($owner, $book);

        $response = $this->actingAs($otherUser)
            ->delete("/reading-plans/{$plan->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
        ]);
    }

    public function test_other_user_cannot_complete_plan(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();

        $book = $this->createBook($owner);
        $plan = $this->createPlan($owner, $book);

        $response = $this->actingAs($otherUser)
            ->post("/reading-plans/{$plan->id}/complete");

        $response->assertForbidden();

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'status' => PlanStatus::Reading->value,
        ]);
    }
}