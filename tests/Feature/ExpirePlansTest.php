<?php

namespace Tests\Feature;

use App\Enums\PlanStatus;
use App\Models\Book;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpirePlansTest extends TestCase
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
        $targetDate,
        PlanStatus $status = PlanStatus::Reading
    ): Plan {
        return Plan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
            'status' => $status,
        ]);
    }

    public function test_expired_reading_plan_becomes_overdue(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $plan = $this->createPlan(
            $user,
            $book,
            today()->subDay()
        );

        $this->artisan('plans:expire')
            ->assertSuccessful();

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'status' => PlanStatus::Overdue->value,
        ]);
    }

    public function test_future_plan_does_not_become_overdue(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $plan = $this->createPlan(
            $user,
            $book,
            today()->addDay()
        );

        $this->artisan('plans:expire')
            ->assertSuccessful();

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'status' => PlanStatus::Reading->value,
        ]);
    }

    public function test_completed_plan_does_not_become_overdue(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $plan = $this->createPlan(
            $user,
            $book,
            today()->subDay(),
            PlanStatus::Completed
        );

        $this->artisan('plans:expire')
            ->assertSuccessful();

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'status' => PlanStatus::Completed->value,
        ]);
    }
}