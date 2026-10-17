<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Bank;
use App\Models\User;
use Tests\TestCase;

/**
 * ویرایش آگهی پیش از انتشار: صفحه ویرایش باید فرم را به مسیر واقعی پنل بفرستد
 * (نه ریدایرکت /panel که فقط GET را پاس می‌دهد) و PUT/DELETE باید کار کند.
 * (به داده‌های DemoDataSeeder تکیه دارد؛ سطرهای ساخته‌شده در تست پاک‌سازی می‌شوند.)
 */
class UserAdEditTest extends TestCase
{
    private function verifiedUser(): User
    {
        return User::query()->where('mobile', '09301021000')->firstOrFail(); // علی رضایی – احراز‌شده
    }

    private function pendingAd(User $user): Ad
    {
        return Ad::create([
            'code' => '88'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'user_id' => $user->id,
            'bank_id' => Bank::query()->active()->firstOrFail()->id,
            'type' => 'qarz',
            'amount' => 100_000_000,
            'months' => 24,
            'rate' => 23,
            'price' => 20_000_000,
            'sheba' => 'IR062960000000100324200001',
            'description' => 'توضیحات اولیه آگهی تستی برای ویرایش.',
            'status' => Ad::STATUS_PENDING,
        ]);
    }

    public function test_edit_page_of_pending_ad_targets_real_update_route(): void
    {
        $user = $this->verifiedUser();
        $ad = $this->pendingAd($user);

        try {
            $html = $this->actingAs($user)->get(route('user.ads.edit', $ad->id))->getContent();

            // فرم به روت واقعی استید (نه ریدایرکت /panel که فقط GET را جواب می‌دهد)
            $this->assertStringContainsString('id="adEditForm"', $html);
            $this->assertStringContainsString(route('user.ads.update', $ad->id), $html);
            $this->assertStringContainsString(route('user.ads.destroy', $ad->id), $html);
            $this->assertStringNotContainsString("action=\"".url('/panel/ads/'.$ad->id)."\"", $html);
        } finally {
            $ad->forceDelete();
        }
    }

    public function test_update_of_pending_ad_saves_and_stays_pending(): void
    {
        $user = $this->verifiedUser();
        $ad = $this->pendingAd($user);

        try {
            $this->actingAs($user)
                ->put(route('user.ads.update', $ad->id), [
                    'bank' => Bank::query()->active()->firstOrFail()->name,
                    'amount' => '120000000',
                    'months' => '30',
                    'rate' => '23',
                    'price' => '25000000',
                    'sheba' => 'IR062960000000100324200001',
                    'note' => 'توضیحات ویرایش‌شده برای آگهی تستی.',
                ])
                ->assertRedirect(route('user.ads.index'));

            $ad->refresh();
            $this->assertSame(120_000_000, (int) $ad->amount);
            $this->assertSame(30, (int) $ad->months);
            $this->assertSame(25_000_000, (int) $ad->price);
            $this->assertSame(Ad::STATUS_PENDING, $ad->status); // دوباره برای تایید می‌رود
        } finally {
            $ad->forceDelete();
        }
    }

    public function test_destroy_of_own_pending_ad_works_via_panel_route(): void
    {
        $user = $this->verifiedUser();
        $ad = $this->pendingAd($user);

        $this->actingAs($user)
            ->delete(route('user.ads.destroy', $ad->id))
            ->assertRedirect(route('user.ads.index'));

        // حذف نرم می‌شود (مطابق رفتار سیستم)
        $this->assertSoftDeleted('ads', ['id' => $ad->id]);
        $ad->forceDelete();
    }
}
