<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\AdDocument;
use App\Models\User;
use Tests\TestCase;

class RequestsPanelTest extends TestCase
{
    private function verifiedUser(): User
    {
        return User::query()->where('mobile', '09301021000')->firstOrFail(); // علی
    }

    public function test_requests_page_renders_real_rows(): void
    {
        $user = $this->verifiedUser();
        $deals = $user->purchases()->get();
        $this->assertTrue($deals->isNotEmpty());

        $html = $this->actingAs($user)->get(route('user.requests'))->getContent();

        // هر معامله کاربر روی صفحه با شناسه کد معامله دیده می‌شود
        foreach ($deals as $deal) {
            $this->assertStringContainsString((string) $deal->code, $html);
        }

        // دکمه تایید انتقال برای معامله مرحله ۶ با مسیر واقعی رندر می‌شود
        $step6 = $deals->firstWhere('step', 6);
        if ($step6) {
            $this->assertStringContainsString(route('user.requests.confirm', $step6->id), $html);
        }
    }

    public function test_step6_request_shows_transfer_receipt_document(): void
    {
        $user = $this->verifiedUser();
        $deal = $user->purchases()->where('step', 6)->firstOrFail();
        $ad = $deal->ad;

        // مدرک انتقال برای آگهی معامله ثبت می‌شود
        $doc = AdDocument::create([
            'ad_id' => $ad->id,
            'kind' => 'transfer_receipt',
            'path' => 'ads/demo/transfer.jpg',
            'original_name' => 'transfer.jpg',
            'size' => 100_000,
        ]);

        try {
            $html = $this->actingAs($user)->get(route('user.requests'))->getContent();

            // مودال تایید انتقال لینک مدرک را از data-doc می‌گیرد
            $this->assertStringContainsString($doc->url, $html);
            $this->assertStringContainsString('id="transferModal"', $html);
        } finally {
            $doc->delete();
        }
    }

    public function test_seeded_transfer_receipt_renders_document_url(): void
    {
        $user = $this->verifiedUser();
        $deal = $user->purchases()->where('step', 6)->with('ad.documents')->firstOrFail();

        // مدرک رسید انتقال برای آگهی معامله ثبت می‌شود و Presenter لینک نسبی /uploads می‌دهد
        $doc = AdDocument::create([
            'ad_id' => $deal->ad_id,
            'kind' => 'transfer_receipt',
            'path' => 'ads/demo/transfer.jpg',
            'original_name' => 'transfer.jpg',
            'size' => 100_000,
        ]);

        try {
            $row = \App\Support\Presenter::requestRow($deal->load('ad.documents'));
            $this->assertSame($doc->url, $row['doc']);
            $this->assertStringStartsWith('/uploads/ads/', $row['doc']);
        } finally {
            $doc->delete();
        }
    }

    public function test_pay_redirects_to_gateway_for_owned_request(): void
    {
        $deal = \App\Models\LoanRequest::query()->with('buyer')->active()->where('step', 3)->firstOrFail();
        $buyer = $deal->buyer;

        $this->actingAs($buyer)
            ->post(route('user.requests.pay', $deal->id))
            ->assertRedirect(route('site.payment.start', $deal));
    }

    public function test_pay_for_foreign_request_is_forbidden(): void
    {
        $user = $this->verifiedUser();
        $foreign = \App\Models\LoanRequest::where('buyer_id', '!=', $user->id)->firstOrFail();

        $this->actingAs($user)
            ->post(route('user.requests.pay', $foreign->id))
            ->assertForbidden();
    }
}
