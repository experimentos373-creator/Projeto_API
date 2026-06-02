@extends('layouts.app')

@section('content')
<div style="max-width: 450px; margin: 4rem auto;">
    <div style="background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
        <h2 style="font-size: 1.875rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--primary);">Bem-vindo de volta</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Introduza os seus dados para aceder à sua conta.</p>

        <form method="POST" action="{{ route('login') }}">
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

            <div style="margin-bottom: 1.5rem;">
                <label for="password" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Palavra-passe</label>
                <input type="password" id="password" name="password" required 
                    style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                @error('password')
                    <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
                <label style="display: flex; align-items: center; cursor: pointer; font-size: 0.875rem;">
                    <input type="checkbox" name="remember" style="margin-right: 0.5rem; border-radius: 4px;">
                    Lembrar-me
                </label>
                <a href="{{ route('password.request') }}" style="font-size: 0.875rem; color: var(--secondary); text-decoration: none; font-weight: 500;">Esqueceu-se?</a>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem;">Entrar</button>
        </form>

        <p style="text-align: center; margin-top: 2rem; font-size: 0.875rem; color: var(--text-muted);">
            Não tem conta? <a href="{{ route('register') }}" style="color: var(--secondary); text-decoration: none; font-weight: 600;">Registe-se agora</a>
        </p>
    </div>
</div>
@endsection
