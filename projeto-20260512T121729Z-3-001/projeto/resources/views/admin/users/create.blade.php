@extends('layouts.app')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <div style="background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
        
        <h2 style="font-size: 1.875rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--primary);">Criar Utilizador</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Adicione uma nova conta administrativa ou de funcionário.</p>

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div style="margin-bottom: 1.25rem;">
                <label for="name" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Nome Completo</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus 
                    style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none;"
                    onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                @error('name')
                    <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="email" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required 
                    style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none;"
                    onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                @error('email')
                    <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.25rem;">
                <div>
                    <label for="user_type" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Tipo de Conta</label>
                    <select id="user_type" name="user_type" required 
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white;">
                        <option value="F" {{ old('user_type') == 'F' ? 'selected' : '' }}>Funcionário</option>
                        <option value="A" {{ old('user_type') == 'A' ? 'selected' : '' }}>Administrador</option>
                    </select>
                    @error('user_type')
                        <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="gender" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Género</label>
                    <select id="gender" name="gender" required 
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white;">
                        <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Feminino</option>
                    </select>
                    @error('gender')
                        <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label for="password" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Palavra-passe</label>
                    <input type="password" id="password" name="password" required 
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none;"
                        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                    @error('password')
                        <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Confirmar Palavra-passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required 
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none;"
                        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">Criar Utilizador</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline" style="padding: 0.75rem 2rem;">Cancelar</a>
            </div>
        </form>

    </div>
</div>
@endsection
