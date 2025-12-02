<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.register');
    }

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'in_game_name' => 'required|unique:users,in_game_name',
            'player_id'    => 'required|unique:users,player_id',
            'password'     => 'required|min:6|confirmed',
            'guild_id'     => 'nullable|exists:guilds,guild_id',
        ]);

        $user = new User();
        $user->in_game_name = $data['in_game_name'];
        $user->player_id = $data['player_id'];
        $user->guild_id = $data['guild_id'] ?? null;
        $user->password = Hash::make($data['password']);

        $user->role = $this->determineRole($user->in_game_name);

        $user->save();

        return redirect('/login')->with('success', 'Account created successfully! Please log in.');
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'in_game_name' => 'required',
            'password'     => 'required',
        ]);

        if (Auth::attempt($data)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Ensure role is set if missing
            if (!$user->role) {
                $user->role = $this->determineRole($user->in_game_name);
                $user->save();
            }

            return redirect('/dashboard')->with('success', 'Welcome back, ' . $user->in_game_name . '!');
        }

        return back()->with('error', 'Invalid in-game name or password.');
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Determine the role based on in_game_name.
     */
    private function determineRole(string $inGameName): string
    {
        return (stripos($inGameName, '_officer') !== false || stripos($inGameName, 'officer') !== false)
            ? 'officer'
            : 'user';
    }
}
