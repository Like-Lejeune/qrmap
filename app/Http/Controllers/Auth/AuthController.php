<?php

// =============================================
// app/Http/Controllers/Auth/AuthController.php
// =============================================
namespace App\Http\Controllers\Auth;
 
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
 
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }
 
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);
 
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }
 
        return back()->withErrors(['email' => 'Identifiants invalides.'])->onlyInput('email');
    }
 
    public function showRegister()
    {
        return view('auth.register');
    }
 
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:8|confirmed',
        ]);
 
        $user = User::create([
            'name'     => $data['first_name'] . ' ' . $data['last_name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
 
        Auth::login($user);
        return redirect()->route('dashboard');
    }
 
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
