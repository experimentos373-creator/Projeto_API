@extends('layouts.app')

@section('content')
<div style="max-width: 450px; margin: 4rem auto;">
    <div style="background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
        <h2 style="font-size: 1.875rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--primary);">Definir nova palavra-passe</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Introduza a sua nova palavra-passe nos campos abaixo.</p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <!-- Token oculto necessário para redefinição -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <div style="margin-bottom: 1.25rem;">
                <label for="email" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', request()->email) }}" required readonly
                    style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: #f1f5f9;">
                @error('email')
                    <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="password" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Nova Palavra-passe</label>
                <input type="password" id="password" name="password" required autofocus
                    style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none;"
                    onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                @error('password')
                    <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 2rem;">
                <label for="password_confirmation" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Confirmar Palavra-passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required 
                    style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none;"
                    onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem;">Guardar Nova Palavra-passe</button>
        </form>
    </div>
</div>
@endsection
