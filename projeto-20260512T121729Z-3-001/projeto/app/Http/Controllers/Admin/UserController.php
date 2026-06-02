<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $type = $request->input('type');
        $blocked = $request->input('blocked');

        $query = User::query();

        // Aplicar filtros
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('user_type', $type);
        }

        if ($blocked !== null && $blocked !== '') {
            $query->where('blocked', $blocked);
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'type', 'blocked'));
    }

    /**
     * Show the form for creating a new employee or admin.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'user_type' => ['required', 'in:A,F'], // Apenas admins e funcionários
            'gender' => ['required', 'in:M,F'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'user_type' => $request->user_type,
            'gender' => $request->gender,
            'password' => Hash::make($request->password),
            'blocked' => false,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Utilizador criado com sucesso!');
    }

    /**
     * Show the form for editing an employee or admin.
     */
    public function edit(User $user)
    {
        // Administradores não podem aceder aos perfis privados dos clientes (morada, nif, etc. são privados)
        if ($user->user_type === 'C') {
            abort(403, 'Os administradores não têm permissão para editar perfis privados de clientes diretamente.');
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if ($user->user_type === 'C') {
            abort(403, 'Os administradores não têm permissão para editar perfis privados de clientes.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'user_type' => ['required', 'in:A,F'],
            'gender' => ['required', 'in:M,F'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'user_type' => $request->user_type,
            'gender' => $request->gender,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Utilizador atualizado com sucesso!');
    }

    /**
     * Toggle block/unblock status.
     */
    public function toggleBlock(User $user)
    {
        // Admin não se pode bloquear a si próprio
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Não se pode bloquear a si próprio.']);
        }

        $user->blocked = !$user->blocked;
        $user->save();

        $status = $user->blocked ? 'bloqueado' : 'desbloqueado';
        return back()->with('success', "Utilizador {$status} com sucesso!");
    }

    /**
     * Remove the specified resource from storage (Soft Delete).
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Não se pode remover a si próprio.']);
        }

        try {
            DB::transaction(function () use ($user) {
                // Se for cliente, remove/soft-deleta também o registo de customer correspondente
                if ($user->user_type === 'C') {
                    $customer = $user->customer;
                    if ($customer) {
                        $customer->delete();
                    }
                }
                $user->delete();
            });

            return redirect()->route('admin.users.index')->with('success', 'Utilizador removido com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao remover utilizador.']);
        }
    }
}
