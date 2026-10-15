<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Dashboard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('adminPanel.index', [
            'kpis' => Dashboard::adminKpis($request->user()),
            'chart' => Dashboard::adminChart(),
            'kyc' => Dashboard::adminKycQueue(4),
            'ads' => Dashboard::adminAdQueue(3),
            'tickets' => Dashboard::adminTicketQueue(3),
            'dist' => Dashboard::adDistribution(),
            'acts' => Dashboard::adminActivities(4),
        ]);
    }
}
