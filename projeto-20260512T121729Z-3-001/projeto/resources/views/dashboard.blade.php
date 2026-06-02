@extends('layouts.app')

@section('content')
<div style="background: white; padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
    <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--primary); margin-bottom: 1rem;">Área de Cliente</h1>
    <p style="color: var(--text-muted); font-size: 1.125rem;">Bem-vindo, <strong>{{ Auth::user()->name }}</strong>!</p>
    
    <div style="margin-top: 3rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
        <div style="padding: 1.5rem; border: 1px solid var(--border); border-radius: var(--radius);">
            <h3 style="font-weight: 700; margin-bottom: 0.5rem;">Minhas Encomendas</h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">Consulte o histórico e estado das suas compras.</p>
            <a href="#" class="btn btn-outline" style="width: 100%;">Ver Histórico</a>
        </div>
        
        <div style="padding: 1.5rem; border: 1px solid var(--border); border-radius: var(--radius);">
            <h3 style="font-weight: 700; margin-bottom: 0.5rem;">Meu Perfil</h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">Gerencie seus dados e preferências de pagamento.</p>
            <a href="{{ route('profile.edit') }}" class="btn btn-outline" style="width: 100%;">Editar Perfil</a>
        </div>
        
        <div style="padding: 1.5rem; border: 1px solid var(--border); border-radius: var(--radius);">
            <h3 style="font-weight: 700; margin-bottom: 0.5rem;">Minhas Imagens</h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">Envie suas próprias estampas para usar nas T-Shirts.</p>
            <a href="#" class="btn btn-outline" style="width: 100%;">Gerir Imagens</a>
        </div>
    </div>
</div>
@endsection
