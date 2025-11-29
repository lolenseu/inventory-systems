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
        // You can add logic here to fetch officer-specific data
        // For now, we'll just return the view with empty data
        return view('officer.dashboard', [
            'totalEquipment' => 0,
            'availableCount' => 0,
            'requestedCount' => 0,
            'approvedCount' => 0,
            'equipment' => collect()
        ]);
    }

    /**
     * Show the user dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function userDashboard()
    {
        // You can add logic here to fetch user-specific data
        // For now, we'll just return the view with empty data
        return view('user.dashboard', [
            'totalRequests' => 0,
            'availableCount' => 0,
            'requestedCount' => 0,
            'approvedCount' => 0,
            'equipment' => collect()
        ]);
    }
}