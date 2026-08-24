<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /** Show the login page */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    /** Handle login POST — username = bagian sebelum @ pada email */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $username = strtolower(trim($request->username));

        // Cari user berdasarkan prefix email (sebelum @)
        $user = User::all()->first(function ($u) use ($username) {
            return strtolower(explode('@', $u->email)[0]) === $username;
        });

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()
                ->withInput(['username' => $request->username])
                ->with('error', 'Username atau password salah.');
        }

        Auth::login($user);
        session(['active_role' => $user->role]);

        DB::table('audit_trails')->insert([
            'time'       => now()->format('d M Y H:i'),
            'actor'      => $user->name,
            'action'     => 'Login',
            'object'     => 'Role: ' . $user->role . ' | Unit: ' . $user->scope,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('home');
    }

    /** Switch active role */
    public function switchRole(Request $request)
    {
        $request->validate(['role' => 'required|string']);
        if (!Auth::check()) return redirect()->route('login');

        $user = Auth::user();
        $newRole = $request->role;
        $allowed = ($user->role === 'admin') || ($newRole === $user->role)
                   || ($newRole === 'staff' && $user->mode_individu);

        if (!$allowed) {
            return back()->with('error', 'Tidak memiliki hak untuk berpindah peran.');
        }

        session(['active_role' => $newRole]);
        DB::table('audit_trails')->insert([
            'time' => now()->format('d M Y H:i'),
            'actor' => $user->name,
            'action' => 'Switch Role',
            'object' => 'Ke: ' . $newRole,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('home')->with('success', 'Peran berhasil diubah ke ' . $newRole);
    }

    /** Logout */
    public function logout()
    {
        if (Auth::check()) {
            DB::table('audit_trails')->insert([
                'time' => now()->format('d M Y H:i'),
                'actor' => Auth::user()->name,
                'action' => 'Logout',
                'object' => 'Sesi berakhir',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Auth::logout();
        }
        session()->forget('active_role');
        return redirect()->route('login');
    }
}
