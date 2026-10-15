<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Bank;
use App\Support\Presenter;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $banks = Bank::query()->active()->ordered()->get();

        $ads = Ad::query()
            ->published()
            ->with('bank')
            ->withCount('offers')
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        return view('website.index', [
            'banks' => $banks->map(fn (Bank $bank) => $bank->toCardArray())->all(),
            'ads' => $ads->map(fn (Ad $ad) => Presenter::adCard($ad))->all(),
        ]);
    }
}
