@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 4rem;">

    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
        <div>
            <h1 style="font-size: 2.5rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem;">
                📸 As Minhas Imagens
            </h1>
            <p style="color: #64748b; font-size: 1.05rem;">Gere as suas estampas personalizadas para T-shirts.</p>
        </div>
        <a href="{{ route('customer.tshirt-images.create') }}" class="btn btn-primary" style="padding: 0.75rem 2rem; border-radius: 14px; font-weight: 700; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.5rem;">
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nova Imagem
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div style="background: #ecfdf5; color: #065f46; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; font-weight: 600; border: 1px solid #a7f3d0;">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- Grid de Imagens --}}
    @if($images->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 2rem;">
            @foreach($images as $image)
                <div style="background: white; border-radius: 20px; overflow: hidden; border: 1px solid var(--border); box-shadow: var(--shadow-sm); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform='none'; this.style.boxShadow='var(--shadow-sm)'">
                    {{-- Image Preview --}}
                    <div style="aspect-ratio: 1; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <img src="{{ route('private.tshirt-images.show', $image->image_url) }}"
                             alt="{{ $image->name }}"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </div>

                    {{-- Card Content --}}
                    <div style="padding: 1.25rem;">
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $image->name }}
                        </h3>
                        @if($image->description)
                            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $image->description }}
                            </p>
                        @endif

                        <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 1rem;">
                            Adicionada {{ $image->created_at->diffForHumans() }}
                        </div>

                        {{-- Actions --}}
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('customer.tshirt-images.show', $image) }}" class="btn btn-primary" style="flex: 1; padding: 0.5rem; border-radius: 10px; font-size: 0.85rem; text-align: center;">
                                Ver
                            </a>
                            <a href="{{ route('customer.tshirt-images.edit', $image) }}" class="btn btn-outline" style="flex: 1; padding: 0.5rem; border-radius: 10px; font-size: 0.85rem; text-align: center;">
                                Editar
                            </a>
                            <form method="POST" action="{{ route('customer.tshirt-images.destroy', $image) }}" style="flex: 1;" onsubmit="return confirm('Tem a certeza que deseja eliminar esta imagem?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="width: 100%; padding: 0.5rem; border-radius: 10px; font-size: 0.85rem; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; cursor: pointer; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#dc2626'; this.style.color='white'" onmouseout="this.style.background='#fef2f2'; this.style.color='#dc2626'">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginação --}}
        <div style="margin-top: 3rem; display: flex; justify-content: center;">
            {{ $images->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div style="text-align: center; padding: 5rem 2rem; background: #f8fafc; border-radius: 24px; border: 2px dashed var(--border);">
            <div style="font-size: 4rem; margin-bottom: 1.5rem;">🎨</div>
            <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text); margin-bottom: 0.75rem;">Nenhuma imagem personalizada</h2>
            <p style="color: #64748b; margin-bottom: 2rem; max-width: 400px; margin-left: auto; margin-right: auto;">
                Carregue as suas próprias imagens para criar T-shirts únicas e exclusivas.
            </p>
            <a href="{{ route('customer.tshirt-images.create') }}" class="btn btn-primary" style="padding: 0.75rem 2rem; border-radius: 14px; font-weight: 700;">
                Adicionar Primeira Imagem
            </a>
        </div>
    @endif
</div>
@endsection
