<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'gender' => ['nullable', 'in:M,F'],
        ]);

        try {
            $user = DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'user_type' => 'C', // Cliente por defeito
                    'gender' => $request->gender ?? 'M',
                ]);

                Customer::create([
                    'id' => $user->id,
                ]);

                return $user;
            });

            event(new \Illuminate\Auth\Events\Registered($user));

            Auth::login($user);

            return redirect(route('home'));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ocorreu um erro ao criar a sua conta.'])->withInput();
        }
    }
}
