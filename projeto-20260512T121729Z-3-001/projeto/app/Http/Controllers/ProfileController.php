<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        $user = Auth::user();
        $customer = $user->customer; // Pode ser null para Funcionários/Admins
        
        return view('profile.edit', compact('user', 'customer'));
    }

    /**
     * Update the profile data.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'gender' => ['required', 'in:M,F'],
            'photo' => ['nullable', 'image', 'max:1024'], // max 1MB
        ];

        // Se for cliente, valida campos de faturação adicionais
        if ($user->user_type === 'C') {
            $rules += [
                'nif' => ['nullable', 'digits:9'],
                'address' => ['nullable', 'string', 'max:500'],
                'default_payment_type' => ['nullable', 'in:Visa,PayPal,MB WAY'],
                'default_payment_ref' => ['nullable', 'string', 'max:255'],
            ];

            // Validação condicional da referência de pagamento baseada no tipo
            if ($request->filled('default_payment_type')) {
                if ($request->default_payment_type === 'Visa') {
                    $rules['default_payment_ref'][] = 'regex:/^4[0-9]{15}$/'; // 16 digitos iniciando por 4
                } elseif ($request->default_payment_type === 'PayPal') {
                    $rules['default_payment_ref'][] = 'email';
                } elseif ($request->default_payment_type === 'MB WAY') {
                    $rules['default_payment_ref'][] = 'regex:/^9[0-9]{8}$/'; // 9 digitos iniciando por 9
                }
            }
        }

        $validated = $request->validate($rules);

        try {
            DB::transaction(function () use ($request, $user, $validated) {
                // Update User
                $user->name = $validated['name'];
                $user->email = $validated['email'];
                $user->gender = $validated['gender'];

                // Upload Photo
                if ($request->hasFile('photo')) {
                    // Remover foto antiga se existir
                    if ($user->photo_url) {
                        Storage::disk('public')->delete($user->photo_url);
                    }
                    $path = $request->file('photo')->store('photos', 'public');
                    $user->photo_url = $path;
                }

                $user->save();

                // Update Customer if User is a Customer
                if ($user->user_type === 'C') {
                    $customer = $user->customer;
                    if (!$customer) {
                        $customer = new \App\Models\Customer();
                        $customer->id = $user->id;
                    }
                    $customer->nif = $validated['nif'] ?? null;
                    $customer->address = $validated['address'] ?? null;
                    $customer->default_payment_type = $validated['default_payment_type'] ?? null;
                    $customer->default_payment_ref = $validated['default_payment_ref'] ?? null;
                    $customer->save();
                }
            });

            return back()->with('success', 'Perfil atualizado com sucesso!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ocorreu um erro ao atualizar o seu perfil.'])->withInput();
        }
    }

    /**
     * Update the password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Palavra-passe atualizada com sucesso!');
    }
}
