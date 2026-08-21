<?php

namespace Tests\Unit;

use App\Enums\PlanStatus;
use App\Models\Book;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
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
            'target_date' => $targetDate ?? today(),
            'status' => $status,
        ]);
    }

    public function test_plan_has_user(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $plan = $this->createPlan($user, $book);

        $this->assertTrue(
            $plan->user->is($user)
        );
    }

    public function test_plan_has_book(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);
        $plan = $this->createPlan($user, $book);

        $this->assertTrue(
            $plan->book->is($book)
        );
    }

    public function test_completed_plan_is_not_overdue(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $plan = $this->createPlan(
            $user,
            $book,
            PlanStatus::Completed,
            today()->subDay()
        );

        $this->assertFalse($plan->isOverdue());
    }

    public function test_plan_with_past_target_date_is_overdue(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $plan = $this->createPlan(
            $user,
            $book,
            PlanStatus::Reading,
            today()->subDay()
        );

        $this->assertTrue($plan->isOverdue());
    }

    public function test_plan_with_today_target_date_is_not_overdue(): void
    {
        $user = $this->createUser();
        $book = $this->createBook($user);

        $plan = $this->createPlan(
            $user,
            $book,
            PlanStatus::Reading,
            today()
        );

        $this->assertFalse($plan->isOverdue());
    }
}
