@extends('layouts.app')

@section('content')
<div style="max-width: 600px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
    
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('admin.tshirt-images.index') }}" style="display: inline-flex; align-items: center; text-decoration: none; color: var(--secondary); font-weight: 600; margin-bottom: 1rem;">
            ← Voltar para a lista
        </a>
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--primary);">Nova Estampa</h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Adicione um novo design público ao catálogo de t-shirts da loja.</p>
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

    <form method="POST" action="{{ route('admin.tshirt-images.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Nome -->
        <div style="margin-bottom: 1.5rem;">
            <label for="name" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Nome do Design</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Ex: Gato Preto, Rock..."
                style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white; font-size: 0.95rem;">
        </div>

        <!-- Descrição -->
        <div style="margin-bottom: 1.5rem;">
            <label for="description" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Descrição</label>
            <textarea id="description" name="description" rows="3" placeholder="Insira uma descrição breve deste design..."
                style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white; font-size: 0.95rem; font-family: inherit; resize: vertical;">{{ old('description') }}</textarea>
        </div>

        <!-- Categoria -->
        <div style="margin-bottom: 1.5rem;">
            <label for="category_id" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Categoria (Opcional)</label>
            <select id="category_id" name="category_id" style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white; font-size: 0.95rem;">
                <option value="">Nenhuma (Geral)</option>
                @foreach($categories as $id => $catName)
                    <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $catName }}</option>
                @endforeach
            </select>
        </div>

        <!-- Imagem -->
        <div style="margin-bottom: 2rem;">
            <label for="image_file" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Ficheiro de Imagem (Obrigatório)</label>
            <input type="file" id="image_file" name="image_file" accept="image/*" required
                style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); font-size: 0.875rem;">
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">Esta imagem será usada como estampa sobreposta na t-shirt base. PNG transparente recomendado.</p>
        </div>

        <!-- Botões -->
        <div style="display: flex; gap: 1rem; border-top: 1px solid var(--border); padding-top: 1.5rem; justify-content: flex-end;">
            <a href="{{ route('admin.tshirt-images.index') }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding-left: 2rem; padding-right: 2rem;">Adicionar Design</button>
        </div>
    </form>

</div>
@endsection
