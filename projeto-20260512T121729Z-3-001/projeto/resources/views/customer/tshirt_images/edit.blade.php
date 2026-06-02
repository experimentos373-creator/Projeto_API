@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 4rem; max-width: 700px; margin: 0 auto;">
    <a href="{{ route('customer.tshirt-images.index') }}" style="display: inline-flex; align-items: center; text-decoration: none; color: var(--secondary); font-weight: 600; margin-bottom: 2rem; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--secondary)'">
        <svg style="width: 20px; height: 20px; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Voltar às Minhas Imagens
    </a>

    <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem;">
        ✏️ Editar Imagem
    </h1>
    <p style="color: #64748b; font-size: 1.05rem; margin-bottom: 2.5rem;">
        Atualize os dados da sua estampa personalizada.
    </p>

    {{-- Erros de validação --}}
    @if($errors->any())
        <div style="background: #fef2f2; color: #dc2626; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #fecaca;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li style="margin-bottom: 0.25rem;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('customer.tshirt-images.update', $tshirtImage) }}" enctype="multipart/form-data" style="background: white; padding: 2.5rem; border-radius: 20px; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
        @csrf
        @method('PUT')

        {{-- Nome --}}
        <div style="margin-bottom: 1.75rem;">
            <label for="name" style="display: block; font-weight: 700; color: var(--text); margin-bottom: 0.5rem;">Nome da Imagem *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $tshirtImage->name) }}" required
                   style="width: 100%; padding: 0.85rem 1rem; border: 2px solid var(--border); border-radius: 12px; font-size: 1rem; transition: border-color 0.2s; outline: none; box-sizing: border-box;"
                   onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
        </div>

        {{-- Descrição --}}
        <div style="margin-bottom: 1.75rem;">
            <label for="description" style="display: block; font-weight: 700; color: var(--text); margin-bottom: 0.5rem;">Descrição <span style="color: #94a3b8; font-weight: 400;">(opcional)</span></label>
            <textarea name="description" id="description" rows="3"
                      style="width: 100%; padding: 0.85rem 1rem; border: 2px solid var(--border); border-radius: 12px; font-size: 1rem; resize: vertical; transition: border-color 0.2s; outline: none; font-family: inherit; box-sizing: border-box;"
                      onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">{{ old('description', $tshirtImage->description) }}</textarea>
        </div>

        {{-- Imagem Atual --}}
        <div style="margin-bottom: 1.75rem;">
            <label style="display: block; font-weight: 700; color: var(--text); margin-bottom: 0.75rem;">Imagem Atual</label>
            <div style="background: #f8fafc; border-radius: 16px; padding: 1.5rem; border: 1px solid var(--border); display: flex; align-items: center; gap: 1.5rem;">
                <img src="{{ route('private.tshirt-images.show', $tshirtImage->image_url) }}"
                     alt="{{ $tshirtImage->name }}"
                     style="width: 120px; height: 120px; object-fit: contain; border-radius: 12px; background: white; border: 1px solid var(--border);">
                <div>
                    <p style="font-weight: 600; color: var(--text); margin-bottom: 0.25rem;">{{ $tshirtImage->image_url }}</p>
                    <p style="font-size: 0.8rem; color: #94a3b8;">Carregada {{ $tshirtImage->created_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>

        {{-- Substituir Imagem --}}
        <div style="margin-bottom: 2rem;">
            <label for="image" style="display: block; font-weight: 700; color: var(--text); margin-bottom: 0.5rem;">Substituir Imagem <span style="color: #94a3b8; font-weight: 400;">(opcional)</span></label>
            <div id="drop-zone" style="border: 2px dashed var(--border); border-radius: 16px; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.3s; background: #f8fafc;"
                 onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#f0f7ff'" onmouseout="this.style.borderColor='var(--border)'; this.style.background='#f8fafc'"
                 onclick="document.getElementById('image').click()">
                <div id="upload-icon" style="font-size: 2rem; margin-bottom: 0.5rem;">🔄</div>
                <p id="upload-text" style="font-weight: 600; color: var(--text); margin-bottom: 0.25rem; font-size: 0.9rem;">Clique para selecionar uma nova imagem</p>
                <p style="font-size: 0.75rem; color: #94a3b8;">Formatos: JPG, JPEG, PNG, WEBP — Máx. 4 MB</p>
                <img id="image-preview" src="" alt="" style="display: none; max-width: 150px; max-height: 150px; margin: 1rem auto 0; border-radius: 12px; object-fit: contain;">
            </div>
            <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp" style="display: none;">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; border-radius: 14px; font-weight: 700;">
            Guardar Alterações
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('image');
    const preview = document.getElementById('image-preview');
    const icon = document.getElementById('upload-icon');
    const text = document.getElementById('upload-text');

    input.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                icon.style.display = 'none';
                text.textContent = input.files[0].name;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>
@endsection
