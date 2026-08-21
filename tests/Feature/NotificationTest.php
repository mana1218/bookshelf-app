<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_notifications(): void
    {
        $response = $this->get('/notifications');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_notifications(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)
            ->get('/notifications');

        $response->assertOk();
    }

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $notification = DatabaseNotification::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'message' => 'テスト通知',
            ]),
        ]);

        $response = $this->actingAs($user)
            ->post("/notifications/{$notification->id}/read");

        $response->assertRedirect('/notifications');

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
        ]);
        
        $this->assertNotNull(
            \DB::table('notifications')
                ->where('id', $notification->id)
                ->value('read_at')
        );
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $otherUser = User::create([
            'name' => '別ユーザー',
            'email' => uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $notification = DatabaseNotification::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $otherUser->id,
            'data' => json_encode([
                'message' => 'テスト通知',
            ]),
        ]);

        $response = $this->actingAs($user)
            ->post("/notifications/{$notification->id}/read");

        $response->assertNotFound();
    }
}