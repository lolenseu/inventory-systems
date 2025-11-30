<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the appropriate dashboard based on user role.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'officer') {
            return redirect()->route('officer.dashboard');
        } else {
            return redirect()->route('user.dashboard');
        }
    }

    /**
     * Show the officer dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function officerDashboard()
    {
        $equipment = collect([
            (object)[
                'id' => 1,
                'item_name' => 'M4 Carbine',
                'type' => 'Rifle',
                'quantity' => 10,
                'status' => 'available',
                'request_date' => null
            ],
            (object)[
                'id' => 2,
                'item_name' => 'Ballistic Helmet',
                'type' => 'Armor',
                'quantity' => 5,
                'status' => 'requested',
                'request_date' => now()->subDays(2)
            ],
            (object)[
                'id' => 3,
                'item_name' => 'Grenade Launcher',
                'type' => 'Launcher',
                'quantity' => 2,
                'status' => 'approved',
                'request_date' => now()->subDays(5)
            ],
            (object)[
                'id' => 4,
                'item_name' => 'Night Vision',
                'type' => 'Optics',
                'quantity' => 3,
                'status' => 'denied',
                'request_date' => now()->subDays(1)
            ],
        ]);

        $totalEquipment = $equipment->count();
        $availableCount = $equipment->where('status', 'available')->count();
        $requestedCount = $equipment->where('status', 'requested')->count();
        $approvedCount = $equipment->where('status', 'approved')->count();

        return view('officer.dashboard', compact('totalEquipment', 'availableCount', 'requestedCount', 'approvedCount', 'equipment'));
    }

    public function userDashboard()
    {
        $user = Auth::user();
        $equipment = collect([
            (object)[
                'id' => 1,
                'item_name' => 'AK-47',
                'type' => 'Rifle',
                'quantity' => 1,
                'status' => 'available',
                'request_date' => null
            ],
            (object)[
                'id' => 2,
                'item_name' => 'Kevlar Vest',
                'type' => 'Armor',
                'quantity' => 2,
                'status' => 'requested',
                'request_date' => now()->subDays(1)
            ],
            (object)[
                'id' => 3,
                'item_name' => 'Pistol',
                'type' => 'Handgun',
                'quantity' => 1,
                'status' => 'approved',
                'request_date' => now()->subDays(3)
            ],
        ]);

        $totalRequests = $equipment->count();
        $availableCount = $equipment->where('status', 'available')->count();
        $requestedCount = $equipment->where('status', 'requested')->count();
        $approvedCount = $equipment->where('status', 'approved')->count();

        return view('user.dashboard', compact('totalRequests', 'availableCount', 'requestedCount', 'approvedCount', 'equipment'));
    }
}