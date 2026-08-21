<?php

namespace Tests\Feature;

use App\Enums\PlanStatus;
use App\Models\Book;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\PlanDeadlineNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PlanDeadlineNotificationsTest extends TestCase
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
        PlanStatus $status,
        $targetDate
    ): Plan {
        return Plan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
            'status' => $status,
        ]);
    }

    public function test_plan_deadline_three_days_before_sends_notification(): void
    {
        Notification::fake();

        $user = $this->createUser();
        $book = $this->createBook($user);

        $plan = $this->createPlan(
            $user,
            $book,
            PlanStatus::Reading,
            today()->addDays(3)
        );

        $this->artisan('plans:deadline-notifications')
            ->assertSuccessful();

        Notification::assertSentTo(
            $user,
            PlanDeadlineNotification::class
        );
    }

    public function test_plan_deadline_today_sends_notification(): void
    {
        Notification::fake();

        $user = $this->createUser();
        $book = $this->createBook($user);

        $plan = $this->createPlan(
            $user,
            $book,
            PlanStatus::Reading,
            today()
        );

        $this->artisan('plans:deadline-notifications')
            ->assertSuccessful();

        Notification::assertSentTo(
            $user,
            PlanDeadlineNotification::class
        );
    }

    public function test_expired_plan_sends_notification(): void
    {
        Notification::fake();

        $user = $this->createUser();
        $book = $this->createBook($user);

        $plan = $this->createPlan(
            $user,
            $book,
            PlanStatus::Reading,
            today()->subDay()
        );

        $this->artisan('plans:deadline-notifications')
            ->assertSuccessful();

        Notification::assertSentTo(
            $user,
            PlanDeadlineNotification::class
        );
    }

    public function test_completed_plan_does_not_send_notification(): void
    {
        Notification::fake();

        $user = $this->createUser();
        $book = $this->createBook($user);

        $this->createPlan(
            $user,
            $book,
            PlanStatus::Completed,
            today()->addDays(3)
        );

        $this->artisan('plans:deadline-notifications')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_plan_with_four_days_remaining_does_not_send_notification(): void
    {
        Notification::fake();

        $user = $this->createUser();
        $book = $this->createBook($user);

        $this->createPlan(
            $user,
            $book,
            PlanStatus::Reading,
            today()->addDays(4)
        );

        $this->artisan('plans:deadline-notifications')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }
}
