@extends('layouts.app')

@section('content')
<div style="max-width: 450px; margin: 4rem auto;">
    <div style="background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
        <h2 style="font-size: 1.875rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--primary);">Verifique o seu email</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem; line-height: 1.6;">
            Obrigado por se registar na FunShirt! Antes de começar, por favor confirme a sua conta clicando no link que acabámos de enviar para o seu email. Se não recebeu o e-mail, teremos todo o gosto em enviar outro.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.875rem;">
                Um novo link de verificação foi enviado para o endereço de email fornecido durante o registo.
            </div>
        @endif

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem;">Reenviar Email de Verificação</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline" style="width: 100%; padding: 0.875rem;">Sair da Conta</button>
            </form>
        </div>
    </div>
</div>
@endsection
