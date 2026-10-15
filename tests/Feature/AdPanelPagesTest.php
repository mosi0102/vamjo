<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * چک‌لیست صفحات آگهی: ثبت آگهی در پنل کاربر و مدیریت آگهی‌ها در پنل ادمین.
 * (از داده‌های DemoDataSeeder استفاده می‌کند؛ آگهی ساخته‌شده در تست پاک‌سازی می‌شود.)
 */
class AdPanelPagesTest extends TestCase
{
    private function verifiedUser(): User
    {
        return User::query()->where('mobile', '09301021000')->firstOrFail();
    }

    private function adminUser(): User
    {
        return User::query()->where('mobile', '09120000001')->firstOrFail();
    }

    public function test_add_ad_page_renders_in_user_panel(): void
    {
        $code = $this->actingAs($this->verifiedUser())
            ->get('/userPanel/ads/create');

        $code->assertOk()
            ->assertSee('ثبت آگهی فروش امتیاز وام')
            ->assertSee('id="adForm"', false)
            ->assertSee('id="fSheba"', false)
            ->assertSee(Bank::query()->orderBy('id')->firstOrFail()->name);
    }

    public function test_add_ad_page_requires_verified_account(): void
    {
        $pending = User::query()->where('mobile', '09153334455')->firstOrFail();

        $this->actingAs($pending)
            ->get('/userPanel/ads/create')
            ->assertRedirect(route('user.verify'));
    }

    public function test_my_ads_page_renders_all_states(): void
    {
        $this->actingAs($this->verifiedUser())
            ->get('/userPanel/ads')
            ->assertOk()
            ->assertSee('آگهی های من');
    }

    public function test_admin_ads_table_renders_with_real_data(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/adminPanel/ads')
            ->assertOk()
            ->assertSee('مدیریت آگهی ها')
            ->assertSee('data-admin-panel="1"', false)
            ->assertSee('IR0629600',false);
    }

    public function test_store_creates_ad_from_user_panel_form(): void
    {
        $user = $this->verifiedUser();
        $bankName = Bank::query()->orderBy('id')->firstOrFail()->name;
        $payload = [
            'bank' => $bankName,
            'amount' => '150000000',
            'months' => '36',
            'rate' => '23',
            'price' => '20000000',
            'sheba' => 'IR062960000000100324200001',
            'note' => 'ضامن کارمند رسمی است و انتقال در سبزوار انجام می‌شود.',
            'agree' => '1',
        ];

        $response = $this->actingAs($user)
            ->post('/userPanel/ads', $payload + [
                'doc_balance' => UploadedFile::fake()->image('balance.jpg'),
                'doc_id' => UploadedFile::fake()->image('id.jpg'),
                'city' => 'سبزوار',
            ]);

        $ad = Ad::query()->where('user_id', $user->id)->latest('id')->firstOrFail();

        $response->assertRedirect(route('user.ads.index'))
            ->assertSessionHas('success');

        $this->assertSame(Ad::STATUS_PENDING, $ad->status);
        $this->assertSame($bankName, $ad->bank->name);
        $this->assertSame(150_000_000, $ad->amount);
        $this->assertTrue($ad->documents()->where('kind', 'balance')->exists());

        // پاک‌سازی فایل‌های آپلود و رکورد تست
        Storage::disk(config('vamjou.uploads.disk'))->deleteDirectory("ads/{$ad->id}");
        $ad->forceDelete();
    }

    public function test_store_rejects_invalid_sheba(): void
    {
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->post('/userPanel/ads', [
                'bank' => Bank::query()->orderBy('id')->firstOrFail()->name,
                'amount' => '150000000',
                'months' => '36',
                'rate' => '23',
                'price' => '20000000',
                'sheba' => 'IR000000000000000000000000',
                'note' => 'ضامن کارمند رسمی است و انتقال در سبزوار انجام می‌شود.',
                'agree' => '1',
            ])
            ->assertSessionHasErrors(['sheba']);
    }

    public function test_admin_feature_toggle_returns_json(): void
    {
        $ad = Ad::query()->published()->firstOrFail();

        $this->actingAs($this->adminUser())
            ->post('/adminPanel/ads/'.$ad->id.'/feature', ['_method' => 'PATCH', 'featured' => 1])
            ->assertOk()
            ->assertJson(['ok' => true, 'featured' => true]);

        $this->assertTrue((bool) $ad->fresh()->featured);
    }
}
