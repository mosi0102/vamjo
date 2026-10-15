<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Support\Dashboard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('userPanel.index', [
            'stats' => Dashboard::userStats($user),
            'quick' => Dashboard::userQuickActions($user),
            'verifySteps' => Dashboard::verifySteps($user),
            'requests' => Dashboard::recentRequests($user, 3),
            'auction' => Dashboard::auction(),
            'chart' => Dashboard::userChart($user),
            'activities' => Dashboard::userActivities($user, 4),
        ]);
    }
}
