<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\View\View;

class PageController extends Controller
{
    public function rules(): View
    {
        return view('website.rules');
    }

    public function faq(): View
    {
        $faqs = Faq::query()->active()->ordered()->get();

        return view('website.faq', [
            'cats' => Faq::CATEGORIES,
            'faqs' => $faqs
                ->groupBy('category')
                ->map(fn ($items) => $items->map(fn (Faq $faq) => [
                    $faq->category,
                    $faq->question,
                    $faq->answer,
                ])->all())
                ->all(),
            'counts' => $faqs->groupBy('category')->map->count(),
        ]);
    }
}
