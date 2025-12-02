<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ThemythDump;

class UserDashboardController extends Controller
{
    /**
     * Show the user dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Fetch equipment requests for this user
        $equipment = ThemythDump::where('user_id', $user->user_id)
            ->orderBy('request_date', 'desc')
            ->get();

        $totalRequests = $equipment->count();
        $pendingCount = $equipment->whereIn('status', ['requested'])->count();
        $approvedCount = $equipment->where('status', 'approved')->count();
        $deniedCount = $equipment->where('status', 'denied')->count();
        $deliveredCount = $equipment->where('status', 'delivered')->count();

        return view('user.dashboard', compact(
            'totalRequests',
            'pendingCount',
            'approvedCount',
            'deniedCount',
            'deliveredCount',
            'equipment'
        ));
    }
}
