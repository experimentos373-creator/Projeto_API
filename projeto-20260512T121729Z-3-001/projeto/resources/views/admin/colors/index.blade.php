@extends('layouts.app')

@section('content')
<div style="background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; color: var(--primary);">Gestão de Cores</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Consulte, crie, altere ou remova cores de t-shirts disponíveis para venda.</p>
        </div>
        <a href="{{ route('admin.colors.create') }}" class="btn btn-primary">+ Nova Cor</a>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.875rem; font-weight: 500;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filtros -->
    <form method="GET" action="{{ route('admin.colors.index') }}" style="display: flex; gap: 1rem; margin-bottom: 2rem; background: var(--bg-main); padding: 1.25rem; border-radius: var(--radius); border: 1px solid var(--border); flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label for="search" style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Pesquisar Cor</label>
            <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Nome ou código hexadecimal..."
                style="width: 100%; padding: 0.625rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white;">
        </div>

        <div style="display: flex; gap: 0.5rem; min-width: 200px;">
            <button type="submit" class="btn btn-primary" style="padding: 0.625rem 1.5rem;">Filtrar</button>
            <a href="{{ route('admin.colors.index') }}" class="btn btn-outline" style="padding: 0.625rem 1.5rem; text-align: center;">Limpar</a>
        </div>
    </form>

    <!-- Tabela de Cores -->
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted); font-size: 0.875rem;">
                    <th style="padding: 1rem 0.5rem; width: 60px;">Cor</th>
                    <th style="padding: 1rem 0.5rem; width: 100px;">Código</th>
                    <th style="padding: 1rem 0.5rem;">Nome</th>
                    <th style="padding: 1rem 0.5rem; width: 120px;">T-Shirt Base</th>
                    <th style="padding: 1rem 0.5rem; text-align: right; width: 180px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($colors as $color)
                    <tr style="border-bottom: 1px solid var(--border); font-size: 0.9375rem; vertical-align: middle;">
                        <td style="padding: 0.75rem 0.5rem;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background-color: #{{ $color->code }}; border: 1px solid var(--border); box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);"></div>
                        </td>
                        <td style="padding: 0.75rem 0.5rem; font-family: monospace; font-weight: 700; color: #475569;">
                            #{{ $color->code }}
                        </td>
                        <td style="padding: 0.75rem 0.5rem; font-weight: 600; color: var(--primary);">
                            {{ $color->name }}
                        </td>
                        <td style="padding: 0.75rem 0.5rem;">
                            <!-- Usa o accessor tshirt_base_url que resolve JPG, PNG ou plain_white -->
                            <img src="{{ $color->tshirt_base_url }}" alt="T-shirt {{ $color->name }}" 
                                style="width: 50px; height: 50px; object-fit: contain; background: #f1f5f9; border-radius: 6px; border: 1px solid var(--border);">
                        </td>
                        <td style="padding: 0.75rem 0.5rem; text-align: right;">
                            <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                <a href="{{ route('admin.colors.edit', $color) }}" class="btn btn-outline" style="padding: 0.375rem 0.75rem; font-size: 0.8125rem;">Editar</a>
                                
                                <form method="POST" action="{{ route('admin.colors.destroy', $color) }}" style="display: inline;" onsubmit="return confirm('Tem a certeza que deseja remover esta cor?');">
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
                            Nenhuma cor encontrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div style="margin-top: 1.5rem;">
        {{ $colors->appends(request()->query())->links() }}
    </div>

</div>
@endsection
