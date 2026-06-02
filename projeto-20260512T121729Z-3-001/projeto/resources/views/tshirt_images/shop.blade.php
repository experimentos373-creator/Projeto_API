@extends('layouts.app')

@section('content')
<div class="hero" style="padding: 2rem 0;">
    <h1 style="font-size: 2.5rem;">Explorar Catálogo</h1>
    <p>Escolha a sua estampa favorita para personalizar a sua T-Shirt.</p>
</div>

<div style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem; margin-top: 2rem;">
    <!-- Sidebar Filtros -->
    <aside style="background: white; padding: 1.5rem; border-radius: var(--radius); border: 1px solid var(--border); height: fit-content;">
        <h3 style="font-weight: 700; margin-bottom: 1.5rem;">Filtros</h3>
        
        <form action="{{ route('home') }}" method="GET">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Pesquisar</label>
                <input type="text" name="name" value="{{ $filterByName }}" placeholder="Nome ou descrição..." 
                    style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border);">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Categoria</label>
                <select name="category" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border);">
                    <option value="">Todas</option>
                    @foreach($categories as $id => $name)
                        <option value="{{ $id }}" {{ $filterByCategory == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Filtrar</button>
            <a href="{{ route('home') }}" class="btn btn-outline" style="width: 100%; margin-top: 0.5rem;">Limpar</a>
        </form>
    </aside>

    <!-- Grelha de Produtos -->
    <div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem;">
            @foreach ($tshirt_images as $tshirt_image)
                <a href="{{ route('tshirt-images.show', $tshirt_image) }}" style="text-decoration: none; color: inherit; display: block;">
                    <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="aspect-ratio: 1; background: #f1f5f9; position: relative;">
                        @if($tshirt_image->image_url)
                            <img src="{{ asset('storage/tshirt_images/' . $tshirt_image->image_url) }}" alt="{{ $tshirt_image->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #cbd5e1;">Sem imagem</div>
                        @endif
                    </div>
                    <div style="padding: 1.25rem;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--secondary); text-transform: uppercase;">{{ $tshirt_image->category->name ?? 'Sem Categoria' }}</span>
                        <h3 style="font-size: 1.125rem; font-weight: 700; margin: 0.25rem 0 0.5rem;">{{ $tshirt_image->name }}</h3>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 800; font-size: 1.25rem;">{{ number_format($tshirt_image->customer_id ? $price->unit_price_own : $price->unit_price_catalog, 2) }}€</span>
                            <button class="btn btn-primary" style="padding: 0.5rem; border-radius: 50%; width: 32px; height: 32px;">+</button>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <div style="margin-top: 3rem;">
            {{ $tshirt_images->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
