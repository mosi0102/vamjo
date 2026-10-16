<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Tests\TestCase;

class TicketPanelTest extends TestCase
{
    private function verifiedUser(): User
    {
        return User::query()->where('mobile', '09301021000')->firstOrFail(); // علی – کاربر احراز شده
    }

    public function test_user_can_view_tickets_index(): void
    {
        $user = $this->verifiedUser();
        $ticket = Ticket::create([
            'code' => 'TKT' . random_int(10000, 99999),
            'user_id' => $user->id,
            'subject' => 'تست تیکت لیست',
            'department' => 'سایر',
            'priority' => 'normal',
            'status' => Ticket::STATUS_PENDING,
            'last_reply_at' => now(),
        ]);
        $ticket->messages()->create([
            'user_id' => $user->id,
            'author_role' => 'user',
            'body' => 'متن پیام تست',
        ]);

        try {
            $this->actingAs($user)
                ->get(route('user.tickets.index'))
                ->assertOk()
                ->assertViewHas('tickets')
                ->assertSee('تست تیکت لیست');
        } finally {
            $ticket->delete();
        }
    }

    public function test_user_can_view_create_ticket_page(): void
    {
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->get(route('user.tickets.create'))
            ->assertOk();
    }

    public function test_user_can_create_ticket(): void
    {
        $user = $this->verifiedUser();
        $ticketCode = 'TKT' . random_int(10000, 99999);

        $response = $this->actingAs($user)
            ->post(route('user.tickets.store'), [
                'subject' => 'تیکت جدید ساخت',
                'department' => 'مشکل فنی',
                'priority' => 'high',
                'message' => 'این یک پیام تست طولانی است برای ایجاد تیکت جدید',
            ]);

        $response->assertRedirect();
        $ticket = Ticket::query()->where('user_id', $user->id)->where('subject', 'تیکت جدید ساخت')->first();
        $this->assertNotNull($ticket);
        
        try {
            $this->assertSame('مشکل فنی', $ticket->department);
            $this->assertSame('high', $ticket->priority);
            $this->assertSame(Ticket::STATUS_PENDING, $ticket->status);
        } finally {
            $ticket->delete();
        }
    }

    public function test_user_can_view_own_ticket(): void
    {
        $user = $this->verifiedUser();
        $ticket = Ticket::create([
            'code' => 'TKT' . random_int(10000, 99999),
            'user_id' => $user->id,
            'subject' => 'نمایش تیکت تست',
            'department' => 'آگهی ها',
            'priority' => 'normal',
            'status' => Ticket::STATUS_PENDING,
            'last_reply_at' => now(),
        ]);
        $ticket->messages()->create([
            'user_id' => $user->id,
            'author_role' => 'user',
            'body' => 'پیام تست نمایش',
        ]);

        try {
            $this->actingAs($user)
                ->get(route('user.tickets.show', $ticket))
                ->assertOk()
                ->assertViewHas('ticket')
                ->assertViewHas('messages')
                ->assertSee('نمایش تیکت تست');
        } finally {
            $ticket->delete();
        }
    }

    public function test_user_cannot_view_others_ticket(): void
    {
        $user = $this->verifiedUser();
        $other = User::query()->where('mobile', '!=', $user->mobile)->first();
        if (!$other) {
            $other = User::factory()->create(['mobile' => '09120000005']);
        }
        $ticket = Ticket::create([
            'code' => 'TKT' . random_int(10000, 99999),
            'user_id' => $other->id,
            'subject' => 'تیکت دیگران',
            'department' => 'سایر',
            'priority' => 'normal',
            'status' => Ticket::STATUS_PENDING,
            'last_reply_at' => now(),
        ]);

        try {
            $this->actingAs($user)
                ->get(route('user.tickets.show', $ticket))
                ->assertForbidden();
        } finally {
            $ticket->delete();
            if ($other->wasRecentlyCreated) {
                $other->delete();
            }
        }
    }

    public function test_user_can_reply_to_own_ticket(): void
    {
        $user = $this->verifiedUser();
        $ticket = Ticket::create([
            'code' => 'TKT' . random_int(10000, 99999),
            'user_id' => $user->id,
            'subject' => 'پاسخ تیکت تست',
            'department' => 'سایر',
            'priority' => 'normal',
            'status' => Ticket::STATUS_PENDING,
            'last_reply_at' => now(),
        ]);

        try {
            $response = $this->actingAs($user)
                ->post(route('user.tickets.reply', $ticket), [
                    'message' => 'این پاسخ تست است برای تیکت',
                ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('ticket_messages', [
                'ticket_id' => $ticket->id,
                'body' => 'این پاسخ تست است برای تیکت',
                'author_role' => 'user',
            ]);
        } finally {
            $ticket->delete();
        }
    }

    public function test_user_can_close_own_ticket(): void
    {
        $user = $this->verifiedUser();
        $ticket = Ticket::create([
            'code' => 'TKT' . random_int(10000, 99999),
            'user_id' => $user->id,
            'subject' => 'بستن تیکت تست',
            'department' => 'سایر',
            'priority' => 'normal',
            'status' => Ticket::STATUS_PENDING,
            'last_reply_at' => now(),
        ]);

        try {
            $response = $this->actingAs($user)
                ->post(route('user.tickets.close', $ticket));

            $response->assertRedirect(route('user.tickets.index'));
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => Ticket::STATUS_CLOSED,
            ]);
        } finally {
            $ticket->delete();
        }
    }

    /** لینک دانلود ضمیمه باید نسبی به ریشه باشد (نه host ثابت) و فایل باید واقعاً موجود باشد */
    public function test_attachment_download_link_is_relative_and_file_exists(): void
    {
        $user = $this->verifiedUser();
        $ticket = Ticket::create([
            'code' => 'TKT' . random_int(10000, 99999),
            'user_id' => $user->id,
            'subject' => 'تیکت ضمیمه',
            'department' => 'سایر',
            'priority' => 'normal',
            'status' => Ticket::STATUS_PENDING,
            'last_reply_at' => now(),
        ]);

        $dir = public_path('uploads/tickets/' . $ticket->id);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = 'tickets/' . $ticket->id . '/attach-test.txt';
        file_put_contents(public_path('uploads/' . $path), 'attachment-body');

        $ticket->messages()->create([
            'user_id' => $user->id,
            'author_role' => 'user',
            'body' => 'پیام با ضمیمه',
            'attachment_path' => $path,
            'attachment_name' => 'attach-test.txt',
            'attachment_size' => '14 KB',
        ]);

        try {
            $html = $this->actingAs($user)->get(route('user.tickets.show', $ticket))->getContent();

            // لینک نسبی به ریشه – با تغییر هاست/پورت نمی‌شکند
            $this->assertStringContainsString('/uploads/' . $path, $html);
            $this->assertDoesNotMatchRegularExpression('/href="https?:\/\/localhost\/uploads/', $html);
            $this->assertFileExists(public_path('uploads/' . $path));
        } finally {
            $ticket->delete();
            @unlink(public_path('uploads/' . $path));
            @rmdir($dir);
        }
    }
}
