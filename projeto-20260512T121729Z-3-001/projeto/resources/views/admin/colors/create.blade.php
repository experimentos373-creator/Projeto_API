@extends('layouts.app')

@section('content')
<div style="max-width: 600px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
    
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('admin.colors.index') }}" style="display: inline-flex; align-items: center; text-decoration: none; color: var(--secondary); font-weight: 600; margin-bottom: 1rem;">
            ← Voltar para a lista
        </a>
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--primary);">Nova Cor</h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Adicione uma nova cor disponível para a compra de t-shirts.</p>
    </div>

    @if($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.875rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.colors.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Código Hexadecimal -->
        <div style="margin-bottom: 1.5rem;">
            <label for="code" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Código Hexadecimal CSS</label>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.25rem; font-weight: 700; color: var(--text-muted);">#</span>
                <input type="text" id="code" name="code" value="{{ old('code') }}" required placeholder="Ex: ffffff, 000000, 1e293b" maxlength="50"
                    style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white; font-size: 0.95rem; font-family: monospace;">
            </div>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">O código de cor CSS (Primary Key) serve para identificar o ficheiro da imagem de base correspondente.</p>
        </div>

        <!-- Nome -->
        <div style="margin-bottom: 1.5rem;">
            <label for="name" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Nome da Cor</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Ex: Branco, Preto, Azul Escuro..."
                style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white; font-size: 0.95rem;">
        </div>

        <!-- Ficheiro de T-Shirt Base -->
        <div style="margin-bottom: 2rem;">
            <label for="tshirt_base_file" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Ficheiro da T-Shirt Base (Obrigatório)</label>
            <input type="file" id="tshirt_base_file" name="tshirt_base_file" accept="image/*" required
                style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); font-size: 0.875rem;">
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">Carregue a imagem da t-shirt base lisa desta cor. Recomenda-se imagem PNG transparente.</p>
        </div>

        <!-- Botões -->
        <div style="display: flex; gap: 1rem; border-top: 1px solid var(--border); padding-top: 1.5rem; justify-content: flex-end;">
            <a href="{{ route('admin.colors.index') }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding-left: 2rem; padding-right: 2rem;">Criar Cor</button>
        </div>
    </form>

</div>
@endsection
