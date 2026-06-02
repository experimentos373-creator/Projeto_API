@extends('layouts.app')

@section('content')
<div style="background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; color: var(--primary);">Gestão de Estampas (Catálogo)</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Consulte, adicione, altere ou remova estampas públicas da loja.</p>
        </div>
        <a href="{{ route('admin.tshirt-images.create') }}" class="btn btn-primary">+ Nova Estampa</a>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.875rem; font-weight: 500;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filtros -->
    <form method="GET" action="{{ route('admin.tshirt-images.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; background: var(--bg-main); padding: 1.25rem; border-radius: var(--radius); border: 1px solid var(--border); align-items: flex-end;">
        <div>
            <label for="search" style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Pesquisa por texto</label>
            <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Nome ou descrição..."
                style="width: 100%; padding: 0.625rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white;">
        </div>

        <div>
            <label for="category_id" style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Categoria</label>
            <select id="category_id" name="category_id" style="width: 100%; padding: 0.625rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white;">
                <option value="">Todas</option>
                @foreach($categories as $id => $name)
                    <option value="{{ $id }}" {{ $categoryId == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.625rem;">Filtrar</button>
            <a href="{{ route('admin.tshirt-images.index') }}" class="btn btn-outline" style="width: 100%; padding: 0.625rem; text-align: center;">Limpar</a>
        </div>
    </form>

    <!-- Tabela de Estampas -->
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted); font-size: 0.875rem;">
                    <th style="padding: 1rem 0.5rem; width: 80px;">Imagem</th>
                    <th style="padding: 1rem 0.5rem;">Nome</th>
                    <th style="padding: 1rem 0.5rem;">Descrição</th>
                    <th style="padding: 1rem 0.5rem;">Categoria</th>
                    <th style="padding: 1rem 0.5rem; text-align: right; width: 180px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tshirtImages as $tshirtImage)
                    <tr style="border-bottom: 1px solid var(--border); font-size: 0.9375rem; vertical-align: middle;">
                        <td style="padding: 0.75rem 0.5rem;">
                            @if($tshirtImage->image_url)
                                <img src="{{ asset('storage/tshirt_images/' . $tshirtImage->image_url) }}" alt="{{ $tshirtImage->name }}" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border);">
                            @else
                                <div style="width: 50px; height: 50px; border-radius: 8px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #64748b; text-align: center;">
                                    Sem Foto
                                </div>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 0.5rem; font-weight: 600; color: var(--primary);">{{ $tshirtImage->name }}</td>
                        <td style="padding: 0.75rem 0.5rem; color: var(--text-muted); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $tshirtImage->description ?? 'Sem descrição' }}
                        </td>
                        <td style="padding: 0.75rem 0.5rem;">
                            @if($tshirtImage->category)
                                <span style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 99px;">
                                    {{ $tshirtImage->category->name }}
                                </span>
                            @else
                                <span style="color: var(--text-muted); font-style: italic; font-size: 0.85rem;">
                                    Geral
                                </span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 0.5rem; text-align: right;">
                            <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                <a href="{{ route('admin.tshirt-images.edit', $tshirtImage) }}" class="btn btn-outline" style="padding: 0.375rem 0.75rem; font-size: 0.8125rem;">Editar</a>
                                
                                <form method="POST" action="{{ route('admin.tshirt-images.destroy', $tshirtImage) }}" style="display: inline;" onsubmit="return confirm('Tem a certeza que deseja remover esta estampa?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline" style="padding: 0.375rem 0.75rem; font-size: 0.8125rem; color: #dc2626; border-color: #f87171;">
                                        Remover
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                            Nenhuma estampa encontrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div style="margin-top: 1.5rem;">
        {{ $tshirtImages->appends(request()->query())->links() }}
    </div>

</div>
@endsection
