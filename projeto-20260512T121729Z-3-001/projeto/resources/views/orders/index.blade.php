@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 4rem;">

    {{-- Título da Página dinâmico conforme perfil --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            @if(Auth::user()->user_type === 'C')
                <h1 style="font-size: 2.5rem; font-weight: 800; margin: 0; color: var(--text);">🛍️ O meu Histórico</h1>
                <p style="color: #64748b; margin: 0.25rem 0 0 0;">Acompanhe o estado de todas as suas compras.</p>
            @elseif(Auth::user()->user_type === 'F')
                <h1 style="font-size: 2.5rem; font-weight: 800; margin: 0; color: var(--text);">📦 Encomendas Pendentes</h1>
                <p style="color: #64748b; margin: 0.25rem 0 0 0;">Lista de encomendas aguardando envio/processamento.</p>
            @else
                <h1 style="font-size: 2.5rem; font-weight: 800; margin: 0; color: var(--text);">⚙️ Painel Geral de Encomendas</h1>
                <p style="color: #64748b; margin: 0.25rem 0 0 0;">Gestão global de todas as encomendas do sistema.</p>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.875rem; font-weight: 500;">
            {{ session('success') }}
        </div>
    @endif

    {{-- ============ FILTROS DE PESQUISA (EXCLUSIVO ADMIN) ============ --}}
    @if(Auth::user()->user_type === 'A')
        <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1.05rem; font-weight: 800; margin: 0 0 1rem 0; color: var(--text);">🔍 Filtros de Pesquisa</h3>
            <form action="{{ route('orders.index') }}" method="GET" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                {{-- Estado --}}
                <div>
                    <label for="status" style="display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 0.4rem; text-transform: uppercase;">Estado</label>
                    <select name="status" id="status" style="width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px solid var(--border); background: white; font-size: 0.9rem;">
                        <option value="">Todos</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Enviada (Closed)</option>
                        <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>

                {{-- Cliente (Nome/NIF) --}}
                <div>
                    <label for="customer" style="display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 0.4rem; text-transform: uppercase;">Cliente (Nome ou NIF)</label>
                    <input type="text" name="customer" id="customer" value="{{ request('customer') }}" placeholder="Ex: João ou 123456789"
                           style="width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px solid var(--border); font-size: 0.9rem;">
                </div>

                {{-- Data --}}
                <div>
                    <label for="date" style="display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 0.4rem; text-transform: uppercase;">Data</label>
                    <input type="date" name="date" id="date" value="{{ request('date') }}"
                           style="width: 100%; padding: 0.55rem; border-radius: 8px; border: 1px solid var(--border); font-size: 0.9rem;">
                </div>

                {{-- Botões --}}
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-size: 0.9rem; font-weight: 700;">Filtrar</button>
                    @if(request()->anyFilled(['status', 'customer', 'date']))
                        <a href="{{ route('orders.index') }}" class="btn btn-outline" style="padding: 0.65rem 1rem; font-size: 0.9rem; display: flex; align-items: center; justify-content: center;">Limpar</a>
                    @endif
                </div>
            </form>
        </div>
    @endif

    {{-- ============ LISTA DE ENCOMENDAS ============ --}}
    @if($orders->count() > 0)
        <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid var(--border); color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.75rem;">
                        <th style="padding: 1rem 1.5rem;">ID</th>
                        <th style="padding: 1rem 1.5rem;">Data</th>
                        @if(Auth::user()->user_type !== 'C')
                            <th style="padding: 1rem 1.5rem;">Cliente</th>
                        @endif
                        <th style="padding: 1rem 1.5rem;">Pagamento</th>
                        <th style="padding: 1rem 1.5rem;">Preço Total</th>
                        <th style="padding: 1rem 1.5rem;">Estado</th>
                        <th style="padding: 1rem 1.5rem; text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody style="color: var(--text);">
                    @foreach($orders as $order)
                        <tr style="border-bottom: 1px solid var(--border); transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                            {{-- ID --}}
                            <td style="padding: 1rem 1.5rem; font-weight: 700;">#{{ $order->id }}</td>
                            
                            {{-- Data --}}
                            <td style="padding: 1rem 1.5rem;">{{ \Carbon\Carbon::parse($order->date)->format('d/m/Y') }}</td>
                            
                            {{-- Cliente --}}
                            @if(Auth::user()->user_type !== 'C')
                                <td style="padding: 1rem 1.5rem;">
                                    <div style="font-weight: 600;">{{ $order->customer->user->name ?? 'N/A' }}</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">NIF: {{ $order->nif }}</div>
                                </td>
                            @endif
                            
                            {{-- Pagamento --}}
                            <td style="padding: 1rem 1.5rem;">
                                <span style="font-size: 0.85rem; font-weight: 500;">
                                    {{ $order->payment_type }}
                                </span>
                            </td>
                            
                            {{-- Total --}}
                            <td style="padding: 1rem 1.5rem; font-weight: 700; color: var(--primary);">
                                {{ number_format($order->total_price, 2) }}€
                            </td>
                            
                            {{-- Estado --}}
                            <td style="padding: 1rem 1.5rem;">
                                @if($order->status === 'pending')
                                    <span style="background: #fef3c7; color: #92400e; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 99px; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        ⏳ Pendente
                                    </span>
                                @elseif($order->status === 'closed')
                                    <span style="background: #dcfce7; color: #166534; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 99px; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        ✅ Enviada
                                    </span>
                                @else
                                    <span style="background: #fef2f2; color: #991b1b; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 99px; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        ❌ Cancelada
                                    </span>
                                @endif
                            </td>
                            
                            {{-- Ações --}}
                            <td style="padding: 1rem 1.5rem; text-align: right;">
                                <a href="{{ route('orders.show', $order) }}" class="btn btn-outline" style="padding: 0.4rem 0.85rem; font-size: 0.8rem; border-radius: 6px;">
                                    Ver Detalhes
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Links de Paginação --}}
        <div style="margin-top: 1.5rem;">
            {{ $orders->appends(request()->query())->links() }}
        </div>

    @else
        {{-- Sem encomendas --}}
        <div style="text-align: center; padding: 5rem 2rem; background: white; border-radius: var(--radius); border: 1px solid var(--border);">
            <div style="font-size: 4rem; margin-bottom: 1.5rem;">📦</div>
            <h2 style="font-weight: 800; margin-bottom: 1rem; font-size: 1.5rem;">Nenhuma encomenda encontrada</h2>
            @if(Auth::user()->user_type === 'C')
                <p style="color: #64748b; margin-bottom: 2rem; font-size: 0.95rem;">Ainda não efetuou nenhuma encomenda na nossa loja.</p>
                <a href="{{ route('home') }}" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 0.95rem;">
                    Visitar Catálogo
                </a>
            @else
                <p style="color: #64748b; font-size: 0.95rem; margin: 0;">De momento não existem registos que correspondam aos filtros de pesquisa selecionados.</p>
            @endif
        </div>
    @endif

</div>
@endsection
