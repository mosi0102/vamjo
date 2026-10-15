<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\AdOffer;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * چک‌لیست سایت: لیست آگهی‌ها با فیلتر/مرتب‌سازی/صفحه‌بندی، برگزیده‌ها (افزودن/حذف) و ثبت درخواست خرید.
 * (به داده‌های DemoDataSeeder تکیه دارد؛ سطرهای ساخته‌شده در تست پاک‌سازی می‌شوند.)
 */
class SiteAdsFlowTest extends TestCase
{
    private function verifiedUser(): User
    {
        return User::query()->where('mobile', '09301021000')->firstOrFail(); // علی رضایی – احراز‌شده
    }

    /* ------------------------- لیست آگهی‌ها + فیلترها ------------------------- */

    public function test_ads_list_renders_with_bank_filter_and_sort(): void
    {
        $this->get('/ads?bank[]=resalat&sort=rate-asc')
            ->assertOk()
            ->assertSee('رسالت', false);       // کارت آگهی بانک رسالت رندر می‌شود
    }

    public function test_ads_list_filters_exclude_other_banks(): void
    {
        $response = $this->get('/ads?bank[]=resalat')->getContent();

        // جاویدان فقط باید در ليست فیلترهای کناری باشد، نه در کارت‌های نتیجه (کلاس ad-card)
        $this->assertMatchesRegularExpression('/ad-card[^"]*".*?رسالت/s', $response);
        $this->assertDoesNotMatchRegularExpression('/class="ad-card".*?جاویدان/s', $response);
    }

    public function test_ads_list_empty_filter_state(): void
    {
        $this->get('/ads?q=نتیجه-ای-وجود-ندارد-۱۲۳۴')
            ->assertOk()
            ->assertSee('آگهی‌ای با این مشخصات پیدا نشد');
    }

    public function test_ads_list_urgent_and_pay_filters(): void
    {
        $this->get('/ads?urgent=1&pay=1')->assertOk();
    }

    public function test_ad_details_page_renders_with_bookmark_and_request(): void
    {
        $user = $this->verifiedUser();
        $ad = Ad::query()->published()->where('user_id', '!=', $user->id)->firstOrFail();

        $html = $this->actingAs($user)->get(route('site.ads.show', $ad->id))->getContent();

        $this->assertStringContainsString('id="bookmarkBtn"', $html);
        $this->assertStringContainsString(route('user.favorites.store', $ad->id), $html);
        $this->assertStringContainsString(route('user.favorites.destroy', $ad->id), $html);
        $this->assertStringContainsString('id="requestModal"', $html);
        $this->assertStringContainsString(route('site.ads.request', $ad->id), $html);
    }

    public function test_ads_list_pagination_links_render(): void
    {
        // ۱۲ آگهی دیگر منتشرشده ثبت می‌کنیم تا صفحه دوم ساخته شود، سپس پاک‌سازی می‌کنیم.
        $user = User::query()->where('mobile', '09301021000')->firstOrFail();
        $bankId = Ad::query()->published()->firstOrFail()->bank_id;
        $sheba = 'IR062960000000100324200001';
        $created = [];

        for ($i = 0; $i < 12; $i++) {
            $created[] = Ad::create([
                'code' => '99'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'bank_id' => $bankId,
                'type' => 'qarz',
                'amount' => 100_000_000,
                'months' => 24,
                'rate' => 23,
                'price' => 20_000_000,
                'sheba' => $sheba,
                'status' => Ad::STATUS_PUBLISHED,
                'published_at' => now(),
            ]);
        }

        try {
            $html = $this->get('/ads')->getContent();
            $this->assertStringContainsString('aria-label="صفحه‌بندی آگهی‌ها"', $html);
            $this->assertStringContainsString('page-item active', $html);
        } finally {
            foreach ($created as $ad) {
                $ad->forceDelete();
            }
        }
    }

    /* ------------------------- برگزیده‌ها (افزودن/حذف) ------------------------- */

    public function test_favorite_add_and_remove_via_ajax(): void
    {
        $user = $this->verifiedUser();
        $ad = Ad::query()->published()->where('user_id', '!=', $user->id)->firstOrFail();

        // افزودن به برگزیده‌ها (از دکمه bookmark در جزئیات آگهی)
        $this->actingAs($user)
            ->postJson(route('user.favorites.store', $ad->id))
            ->assertOk()
            ->assertJson(['ok' => true, 'saved' => true]);

        $this->assertTrue(Favorite::query()->where('user_id', $user->id)->where('ad_id', $ad->id)->exists());

        // صفحه برگزیده‌های پنل، آگهی ذخیره‌شده را با مسیر واقعی حذف نشان می‌دهد
        $this->actingAs($user)
            ->get(route('user.favorites'))
            ->assertOk()
            ->assertSee((string) $ad->amount, false)
            ->assertSee(route('user.favorites.destroy', $ad->id), false);

        // حذف از برگزیده‌ها (کلیک دوم روی bookmark / دکمه حذف در پنل)
        $this->actingAs($user)
            ->postJson(route('user.favorites.destroy', $ad->id), ['_method' => 'DELETE'])
            ->assertOk()
            ->assertJson(['ok' => true, 'saved' => false]);

        $this->assertFalse(Favorite::query()->where('user_id', $user->id)->where('ad_id', $ad->id)->exists());
    }

    /* ------------------------- ثبت درخواست خرید ------------------------- */

    public function test_request_for_ad_of_other_user_stores_offer(): void
    {
        $user = $this->verifiedUser();
        $ad = Ad::query()->published()->where('user_id', '!=', $user->id)->firstOrFail();
        $offerAmount = $ad->price + 1_000_000;

        $response = $this->actingAs($user)
            ->postJson(route('site.ads.request', $ad->id), ['offer' => $offerAmount]);

        $response->assertOk()
            ->assertJson(['ok' => true, 'redirect' => route('user.requests')]);

        $offer = AdOffer::query()->where('ad_id', $ad->id)->where('user_id', $user->id)->first();
        $this->assertNotNull($offer);
        $this->assertSame($offerAmount, (int) $offer->amount);
        $this->assertSame(AdOffer::STATUS_PENDING, $offer->status);

        // درخواست تکراری: پیام «قبلاً ثبت شده» و ساخت رکورد تازه صورت نمی‌گیرد
        $this->actingAs($user)
            ->postJson(route('site.ads.request', $ad->id), ['offer' => $offerAmount])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $firstStatus = $offer->status;
        $this->assertSame(AdOffer::STATUS_PENDING, $firstStatus); // هنوز یک رکورد داریم

        // پاک‌سازی: درخواست تست
        $offer->delete();
    }

    public function test_request_for_own_ad_is_rejected(): void
    {
        $user = $this->verifiedUser();
        $ad = Ad::query()->published()->where('user_id', $user->id)->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('site.ads.request', $ad->id), ['offer' => $ad->price])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);
    }

    public function test_request_offer_out_of_range_is_rejected(): void
    {
        $user = $this->verifiedUser();
        $ad = Ad::query()->published()->where('user_id', '!=', $user->id)->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('site.ads.request', $ad->id), ['offer' => 1])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);
    }
}
