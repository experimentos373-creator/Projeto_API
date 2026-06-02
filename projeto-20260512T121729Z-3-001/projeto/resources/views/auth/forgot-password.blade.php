@extends('layouts.app')

@section('content')
<div style="max-width: 450px; margin: 4rem auto;">
    <div style="background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
        <h2 style="font-size: 1.875rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--primary);">Recuperar palavra-passe</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Indique o seu email e enviar-lhe-emos um link para redefinir a sua palavra-passe.</p>

        @if (session('status'))
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.875rem;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div style="margin-bottom: 1.5rem;">
                <label for="email" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus 
                    style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                @error('email')
                    <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem;">Enviar Link de Recuperação</button>
        </form>

        <p style="text-align: center; margin-top: 2rem; font-size: 0.875rem; color: var(--text-muted);">
            Voltar para o <a href="{{ route('login') }}" style="color: var(--secondary); text-decoration: none; font-weight: 600;">Login</a>
        </p>
    </div>
</div>
@endsection
