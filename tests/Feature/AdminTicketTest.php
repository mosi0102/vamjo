<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * چک‌لیست پنل پشتیبانی: لیست تیکت‌ها، پاسخ با فایل، یادداشت داخلی و بستن تیکت.
 * (به داده‌های DemoDataSeeder تکیه دارد؛ سطرهای ساخته‌شده در تست پاک‌سازی می‌شوند.)
 */
class AdminTicketTest extends TestCase
{
    private function admin(): User
    {
        return User::query()->where('role', User::ROLE_ADMIN)->firstOrFail();
    }

    /** ساخت تیکت موقت برای یک کاربر + پاک‌سازی خودکار */
    private function makeTicket(int $userId, string $subject = 'تیکت تستی ادمین'): Ticket
    {
        $ticket = Ticket::create([
            'code' => 'TKT'.random_int(10000, 99999),
            'user_id' => $userId,
            'subject' => $subject,
            'department' => 'سایر',
            'priority' => 'normal',
            'status' => Ticket::STATUS_PENDING,
            'last_reply_at' => now(),
        ]);

        $ticket->messages()->create([
            'user_id' => $userId,
            'author_role' => 'user',
            'body' => 'متن اولیه تیکت',
        ]);

        return $ticket;
    }

    public function test_index_renders_tickets_table(): void
    {
        $admin = $this->admin();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $ticket = $this->makeTicket($user->id);

        try {
            $html = $this->actingAs($admin)->get(route('admin.tickets.index'))->getContent();

            $this->assertStringContainsString('تیکت های پشتیبانی', $html);
            $this->assertStringContainsString($ticket->subject, $html);
            $this->assertStringContainsString(route('admin.tickets.show', $ticket->id), $html);
        } finally {
            $ticket->delete();
        }
    }

    public function test_show_renders_conversation_and_reply_form(): void
    {
        $admin = $this->admin();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $ticket = $this->makeTicket($user->id);

        try {
            $html = $this->actingAs($admin)->get(route('admin.tickets.show', $ticket))->getContent();

            $this->assertStringContainsString($ticket->subject, $html);
            $this->assertStringContainsString('id="tkReply"', $html);
            $this->assertStringContainsString(route('admin.tickets.reply', $ticket->id), $html);
            $this->assertStringContainsString('پاسخ آماده', $html); // چیپ پاسخ آماده
            $this->assertStringContainsString('name="internal"', $html);
            $this->assertStringContainsString(route('admin.users.show', $user->id), $html); // کارت اطلاعات کاربر
        } finally {
            $ticket->delete();
        }
    }

    public function test_reply_stores_support_message_and_marks_answered(): void
    {
        $admin = $this->admin();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $ticket = $this->makeTicket($user->id);

        try {
            $this->actingAs($admin)
                ->post(route('admin.tickets.reply', $ticket), ['message' => 'پاسخ پشتیبانی برای تست'])
                ->assertRedirect()
                ->assertSessionHas('success');

            $this->assertDatabaseHas('ticket_messages', [
                'ticket_id' => $ticket->id,
                'author_role' => 'support',
                'body' => 'پاسخ پشتیبانی برای تست',
            ]);

            $this->assertSame(Ticket::STATUS_ANSWERED, $ticket->fresh()->status);
            $this->assertSame($admin->id, $ticket->fresh()->assignee_id);
        } finally {
            $ticket->delete();
        }
    }

    public function test_reply_with_attachment_stores_file(): void
    {
        Storage::fake(config('vamjou.uploads.disk'));

        $admin = $this->admin();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $ticket = $this->makeTicket($user->id);

        try {
            $this->actingAs($admin)
                ->post(route('admin.tickets.reply', $ticket), [
                    'message' => 'مدرک بررسی شد؛ فایل راهنما ضمیمه است.',
                    'attachment' => UploadedFile::fake()->create('guide.pdf', 100, 'application/pdf'),
                ])
                ->assertRedirect()
                ->assertSessionHas('success');

            $message = $ticket->messages()->where('author_role', 'support')->latest('id')->first();
            $this->assertNotNull($message);
            $this->assertNotNull($message->attachment_path);
            Storage::disk(config('vamjou.uploads.disk'))->assertExists($message->attachment_path);
            $this->assertSame('guide.pdf', $message->attachment_name);
        } finally {
            // پیام ضمیمه‌دار حذف فایل از دیسک را انجام نمی‌دهد؛ خودش پاک می‌کنیم
            if (isset($message) && $message->attachment_path) {
                Storage::disk(config('vamjou.uploads.disk'))->deleteDirectory(dirname($message->attachment_path));
            }
            $ticket->delete();
        }
    }

    public function test_internal_note_keeps_status_pending_and_hidden_from_user(): void
    {
        $admin = $this->admin();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $ticket = $this->makeTicket($user->id);

        try {
            $this->actingAs($admin)
                ->post(route('admin.tickets.reply', $ticket), [
                    'message' => 'یادداشت داخلی برای همکاران',
                    'internal' => '1',
                ])
                ->assertRedirect()
                ->assertSessionHas('success');

            $this->assertDatabaseHas('ticket_messages', [
                'ticket_id' => $ticket->id,
                'author_role' => 'internal',
                'body' => 'یادداشت داخلی برای همکاران',
            ]);

            // وضعیت تیکت باید در انتظار پاسخ بماند
            $this->assertSame(Ticket::STATUS_PENDING, $ticket->fresh()->status);

            // صفحه کاربر: یادداشت داخلی نشان داده نمی‌شود و صفحه تیکت باز است
            $userHtml = $this->actingAs($user)->get(route('user.tickets.show', $ticket))->getContent();
            $this->assertStringNotContainsString('یادداشت داخلی برای همکاران', $userHtml);
            $this->assertStringContainsString('id="tkReply"', $userHtml);
        } finally {
            $ticket->delete();
        }
    }

    public function test_close_sets_closed_and_reopen_returns_pending(): void
    {
        $admin = $this->admin();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $ticket = $this->makeTicket($user->id);

        try {
            // بستن از مودال confirm: POST ساده
            $this->actingAs($admin)
                ->post(route('admin.tickets.close', $ticket))
                ->assertRedirect()
                ->assertSessionHas('success');

            $this->assertSame(Ticket::STATUS_CLOSED, $ticket->fresh()->status);
            $this->assertNotNull($ticket->fresh()->closed_at);

            // کاربر دیگر نمی‌تواند پاسخ بدهد
            $this->actingAs($user)
                ->post(route('user.tickets.reply', $ticket), ['message' => 'پاسخ بعد از بستن'])
                ->assertSessionHasErrors();

            // بازگشایی از فرم مدیریت
            $this->actingAs($admin)
                ->post(route('admin.tickets.update', $ticket), ['status' => Ticket::STATUS_PENDING])
                ->assertRedirect()
                ->assertSessionHas('success');

            $this->assertSame(Ticket::STATUS_PENDING, $ticket->fresh()->status);
        } finally {
            $ticket->delete();
        }
    }
}
