@extends('layouts.app')

@section('content')
<div style="max-width: 600px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
    
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--primary);">Configuração de Preços</h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Defina os preços base e as regras de descontos de quantidade aplicadas às encomendas da loja.</p>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.875rem; font-weight: 500;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.875rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.prices.update') }}">
        @csrf
        @method('PUT')

        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Preços Base (Sem Desconto)</h3>
        
        <!-- Preço Base Catálogo -->
        <div style="margin-bottom: 1.5rem;">
            <label for="unit_price_catalog" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Preço Unitário do Catálogo (€)</label>
            <input type="number" step="0.01" min="0" id="unit_price_catalog" name="unit_price_catalog" value="{{ old('unit_price_catalog', $price->unit_price_catalog) }}" required
                style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white; font-size: 0.95rem;">
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">Preço cobrado por t-shirts personalizadas com estampas do catálogo da loja.</p>
        </div>

        <!-- Preço Base Própria -->
        <div style="margin-bottom: 2rem;">
            <label for="unit_price_own" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Preço Unitário com Estampa Própria (€)</label>
            <input type="number" step="0.01" min="0" id="unit_price_own" name="unit_price_own" value="{{ old('unit_price_own', $price->unit_price_own) }}" required
                style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white; font-size: 0.95rem;">
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">Preço cobrado por t-shirts personalizadas com imagens enviadas pelos próprios clientes.</p>
        </div>

        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Descontos por Quantidade</h3>

        <!-- Preço Catálogo Desconto -->
        <div style="margin-bottom: 1.5rem;">
            <label for="unit_price_catalog_discount" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Preço do Catálogo com Desconto (€)</label>
            <input type="number" step="0.01" min="0" id="unit_price_catalog_discount" name="unit_price_catalog_discount" value="{{ old('unit_price_catalog_discount', $price->unit_price_catalog_discount) }}" required
                style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white; font-size: 0.95rem;">
        </div>

        <!-- Preço Própria Desconto -->
        <div style="margin-bottom: 1.5rem;">
            <label for="unit_price_own_discount" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Preço com Estampa Própria e Desconto (€)</label>
            <input type="number" step="0.01" min="0" id="unit_price_own_discount" name="unit_price_own_discount" value="{{ old('unit_price_own_discount', $price->unit_price_own_discount) }}" required
                style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white; font-size: 0.95rem;">
        </div>

        <!-- Quantidade Desconto -->
        <div style="margin-bottom: 2rem;">
            <label for="qty_discount" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Quantidade Mínima para Desconto</label>
            <input type="number" min="1" id="qty_discount" name="qty_discount" value="{{ old('qty_discount', $price->qty_discount) }}" required
                style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white; font-size: 0.95rem;">
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">O preço com desconto será aplicado quando a quantidade de um mesmo item (mesmo tamanho, cor e estampa) for igual ou superior a este valor.</p>
        </div>

        <!-- Botões -->
        <div style="display: flex; gap: 1rem; border-top: 1px solid var(--border); padding-top: 1.5rem; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" style="padding-left: 2.5rem; padding-right: 2.5rem;">Guardar Definições</button>
        </div>
    </form>

</div>
@endsection
