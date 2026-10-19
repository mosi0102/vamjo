<?php

namespace Tests\Feature;

use App\Models\LoanRadar;
use App\Models\RadarMatch;
use App\Models\User;
use Tests\TestCase;

/**
 * چک‌لیست رادار وام در پنل کاربر (به داده‌های DemoDataSeeder تکیه دارد؛
 * سطرهای ساخته‌شده در تست پاک‌سازی می‌شوند).
 */
class RadarPanelTest extends TestCase
{
    private function verifiedUser(): User
    {
        return User::query()->where('mobile', '09301021000')->firstOrFail(); // علی – دو رادار دمو دارد
    }

    public function test_radar_page_renders_radars_and_matches(): void
    {
        $user = $this->verifiedUser();
        $radars = $user->radars()->get();

        $html = $this->actingAs($user)->get(route('user.radar'))->getContent();

        $this->assertStringContainsString('رادارهای من', $html);
        $this->assertStringContainsString('id="rdRadars"', $html);
        $this->assertStringContainsString(route('user.radar.toggle', $radars->first()->id), $html);
        $this->assertStringContainsString(route('user.radar.store'), $html); // فرم مودال جدید
    }

    public function test_store_creates_radar_and_matches(): void
    {
        $user = $this->verifiedUser();

        $response = $this->actingAs($user)->post(route('user.radar.store'), [
            'name' => 'رادار تست',
            'min_amount' => '50000000',
            'min_rate' => '15',
            'max_rate' => '30',
            'notify_panel' => '1',
            'notify_sms' => '1',
        ]);

        $response->assertSessionHas('success');

        $radar = LoanRadar::query()->where('user_id', $user->id)->where('name', 'رادار تست')->firstOrFail();
        $this->assertSame(50_000_000, (int) $radar->min_amount);
        // بازه نرخ سود از ۱۵ تا ۳۰ درصد ذخیره می‌شود
        $this->assertSame(15.0, (float) $radar->min_rate);
        $this->assertSame(30.0, (float) $radar->max_rate);
        $this->assertTrue((bool) $radar->is_active);

        // حداقل ۱۲ قست: فقط وام با اقساط >= ۱۲ منطبق می‌شود
        $matching = $radar->matchingAds()->get();
        foreach ($matching as $ad) {
            $this->assertGreaterThanOrEqual(12, (int) $ad->months);
        }

        $radar->delete();
    }

    public function test_store_requires_at_least_one_filter(): void
    {
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->post(route('user.radar.store'), ['name' => 'فقط نام'])
            ->assertSessionHasErrors();
    }

    public function test_toggle_switches_open_and_close(): void
    {
        $user = $this->verifiedUser();
        $radar = $user->radars()->where('is_active', true)->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('user.radar.toggle', $radar->id), ['_method' => 'PATCH', 'active' => 0])
            ->assertOk()
            ->assertJson(['ok' => true, 'active' => false]);

        $this->assertFalse((bool) $radar->fresh()->is_active);

        // برگرداندن به حالت فعال برای پایداری دمو
        $this->actingAs($user)
            ->postJson(route('user.radar.toggle', $radar->id), ['_method' => 'PATCH', 'active' => 1])
            ->assertOk()
            ->assertJson(['active' => true]);
    }

    public function test_destroy_deletes_radar(): void
    {
        $user = $this->verifiedUser();
        $radar = LoanRadar::create([
            'user_id' => $user->id,
            'name' => 'رادار حذف',
            'is_active' => false,
        ]);

        // حذف از confirm modal: POST با _method=DELETE (نه ajax)
        $this->actingAs($user)
            ->post(route('user.radar.destroy', $radar->id), ['_method' => 'DELETE'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('loan_radars', ['id' => $radar->id]);
    }

    public function test_destroy_match_deletes_only_owned_match(): void
    {
        $user = $this->verifiedUser();

        // نتیجه و رادار بیگانه مستقل از داده دمو، در همین تست ساخته می‌شوند
        $radar = $user->radars()->firstOrFail();
        $ad = \App\Models\Ad::query()->published()->firstOrFail();
        $match = RadarMatch::create(['radar_id' => $radar->id, 'ad_id' => $ad->id, 'is_new' => true]);

        $other = User::query()->where('mobile', '09120000001')->firstOrFail();
        $otherRadar = LoanRadar::create(['user_id' => $other->id, 'name' => 'رادار بیگانه تست', 'is_active' => false]);
        $foreignMatch = RadarMatch::create(['radar_id' => $otherRadar->id, 'ad_id' => $ad->id, 'is_new' => true]);

        try {
            // حذف نتیجه رادار خودی (فرم confirm modal)
            $this->actingAs($user)
                ->post(route('user.radar.matches.destroy', $match->id), ['_method' => 'DELETE'])
                ->assertRedirect()
                ->assertSessionHas('success');

            $this->assertDatabaseMissing('radar_matches', ['id' => $match->id]);

            // نتیجه رادار کاربر دیگر: دسترسی ممنوع
            $this->actingAs($user)
                ->delete(route('user.radar.matches.destroy', $foreignMatch->id))
                ->assertForbidden();
        } finally {
            $foreignMatch->delete();
            $otherRadar->delete();
        }
    }
}
