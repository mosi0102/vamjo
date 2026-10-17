<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Tests\TestCase;

/**
 * چک‌لیست صفحات ادمین: داشبورد، لیست کاربران، جزئیات کاربر و امور مالی — با دیتای واقعی.
 * (به داده‌های DemoDataSeeder تکیه دارد؛ سطرهای ساخته‌شده در تست پاک‌سازی می‌شوند.)
 */
class AdminPanelPagesTest extends TestCase
{
    private function admin(): User
    {
        return User::query()->where('role', User::ROLE_ADMIN)->firstOrFail();
    }

    public function test_dashboard_shows_real_kpis_and_queues(): void
    {
        $admin = $this->admin();

        $html = $this->actingAs($admin)->get(route('admin.dashboard'))->getContent();

        // KPI واقعی به‌جای شماره‌های ثابت نمونه
        $totalUsers = \App\Models\User::query()->role(User::ROLE_USER)->count();
        $this->assertStringContainsString(\App\Support\Fa::number($totalUsers), $html);
        $this->assertStringNotContainsString('۱۳۲۴', $html);

        // صف تیکت‌های بی‌پاسخ به لیست واقعی لینک می‌دهد
        // صف داشبورد قدیمی‌ترین‌ها را نشان می‌دهد؛ همان تیکت باید در صفحه باشد.
        $pendingTicket = \App\Models\Ticket::query()->pendingAnswer()->oldest('created_at')->first();
        if ($pendingTicket) {
            $this->assertStringContainsString(route('admin.tickets.show', $pendingTicket->id), $html);
        }
    }

    public function test_users_index_shows_real_users_and_summary(): void
    {
        $admin = $this->admin();
        $user = User::query()->role(User::ROLE_USER)->firstOrFail();

        $html = $this->actingAs($admin)->get(route('admin.users.index'))->getContent();

        $this->assertStringContainsString($user->display_name, $html);
        $this->assertStringContainsString(route('admin.users.show', $user->id), $html);
        $this->assertStringContainsString($user->mobile, $html);
        // آمار بالای صفحه از دیتای واقعی (شماره ثابت ۱۳۲۴ حذف شد)
        $this->assertStringNotContainsString('۱۳۲۴', $html);
    }

    public function test_user_details_renders_real_profile_and_tabs(): void
    {
        $admin = $this->admin();
        $user = User::query()->role(User::ROLE_USER)->firstOrFail();

        $html = $this->actingAs($admin)->get(route('admin.users.show', $user))->getContent();

        $this->assertStringContainsString($user->display_name, $html);
        $this->assertStringContainsString(route('admin.tickets.index', ['user' => $user->id]), $html);
        $this->assertStringContainsString(route('admin.users.note', $user->id), $html); // فرم یادداشت
        $this->assertStringContainsString('data-tab-pane="note"', $html);
    }

    public function test_finance_page_shows_real_transactions_with_correct_actions(): void
    {
        $admin = $this->admin();

        $html = $this->actingAs($admin)->get(route('admin.finance'))->getContent();

        // هر تراکنش واقعی با شناسه خودش در جدول است
        $transaction = Transaction::query()->latest('id')->firstOrFail();
        $this->assertStringContainsString(\App\Support\Fa::digits($transaction->code), $html);

        // دکمه‌های واریز/برگشت به شناسه تراکنش (نه کد) لینک می‌دهند
        $duePayout = Transaction::query()
            ->where('type', Transaction::TYPE_PAYOUT)
            ->where('status', Transaction::STATUS_PENDING)
            ->first();
        if ($duePayout) {
            $this->assertStringContainsString(route('admin.finance.payout', $duePayout->id), $html);
        }

        $escrow = Transaction::query()->escrow()->first();
        if ($escrow) {
            $this->assertStringContainsString(route('admin.finance.refund', $escrow->id), $html);
        }
    }

    public function test_finance_row_links_to_user_profile(): void
    {
        $admin = $this->admin();

        // ردیف‌های تسویه‌شده/برگشتی/ناموفق دکمه «مشاهده کاربر» به پروفایل واقعی دارند
        $transaction = Transaction::query()->whereNotNull('user_id')
            ->whereIn('status', [Transaction::STATUS_RELEASED, Transaction::STATUS_REFUNDED, Transaction::STATUS_FAILED])
            ->latest('id')->firstOrFail();

        $row = \App\Support\Presenter::adminPaymentRow($transaction->load(['user', 'ad.bank']));

        $this->assertSame($transaction->id, $row[10]);
        $this->assertSame($transaction->user_id, $row[11]);

        $html = $this->actingAs($admin)->get(route('admin.finance'))->getContent();
        $this->assertStringContainsString(route('admin.users.show', $transaction->user_id), $html);
    }
}
