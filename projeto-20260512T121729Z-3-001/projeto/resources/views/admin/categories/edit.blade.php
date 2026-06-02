@extends('layouts.app')

@section('content')
<div style="max-width: 600px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
    
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('admin.categories.index') }}" style="display: inline-flex; align-items: center; text-decoration: none; color: var(--secondary); font-weight: 600; margin-bottom: 1rem;">
            ← Voltar para a lista
        </a>
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--primary);">Editar Categoria</h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Altere os dados ou substitua a imagem da categoria.</p>
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

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Nome -->
        <div style="margin-bottom: 1.5rem;">
            <label for="name" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Nome da Categoria</label>
            <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required
                style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white; font-size: 0.95rem;">
        </div>

        <!-- Imagem Atual -->
        @if($category->image_url)
            <div style="margin-bottom: 1.5rem;">
                <span style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Imagem Atual</span>
                <img src="{{ asset('storage/categories/' . $category->image_url) }}" alt="{{ $category->name }}" 
                    style="width: 120px; height: 120px; border-radius: var(--radius); object-fit: cover; border: 1px solid var(--border); display: block;">
            </div>
        @endif

        <!-- Substituir Imagem -->
        <div style="margin-bottom: 2rem;">
            <label for="image_file" style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">{{ $category->image_url ? 'Substituir Imagem' : 'Adicionar Imagem (Opcional)' }}</label>
            <input type="file" id="image_file" name="image_file" accept="image/*"
                style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); font-size: 0.875rem;">
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">Carregue uma imagem PNG ou JPG até 2MB se pretender alterar a imagem atual.</p>
        </div>

        <!-- Botões -->
        <div style="display: flex; gap: 1rem; border-top: 1px solid var(--border); padding-top: 1.5rem; justify-content: flex-end;">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding-left: 2rem; padding-right: 2rem;">Guardar Alterações</button>
        </div>
    </form>

</div>
@endsection
