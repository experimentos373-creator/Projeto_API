@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 4rem;">

    {{-- Cabeçalho com navegação rápida --}}
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="{{ route('orders.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                ← Voltar à Lista
            </a>
            <h1 style="font-size: 2.5rem; font-weight: 800; margin-top: 0.5rem; color: var(--text);">
                Encomenda #{{ $order->id }}
            </h1>
            <p style="color: #64748b; margin: 0.25rem 0 0 0;">
                Submetida em {{ \Carbon\Carbon::parse($order->date)->format('d/m/Y') }}
            </p>
        </div>

        {{-- Badge de Estado Gigante --}}
        <div>
            @if($order->status === 'pending')
                <span style="background: #fef3c7; color: #92400e; font-size: 1rem; font-weight: 800; padding: 0.5rem 1.5rem; border-radius: 99px; border: 1px solid #fcd34d;">
                    ⏳ Aguarda Envio (Pendente)
                </span>
            @elseif($order->status === 'closed')
                <span style="background: #dcfce7; color: #166534; font-size: 1rem; font-weight: 800; padding: 0.5rem 1.5rem; border-radius: 99px; border: 1px solid #a7f3d0;">
                    ✅ Enviada (Closed)
                </span>
            @else
                <span style="background: #fef2f2; color: #991b1b; font-size: 1rem; font-weight: 800; padding: 0.5rem 1.5rem; border-radius: 99px; border: 1px solid #fecaca;">
                    ❌ Cancelada
                </span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.875rem; font-weight: 500;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.875rem; font-weight: 500;">
            {{ session('error') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 360px; gap: 2.5rem; align-items: start;">

        {{-- ============ DETALHES GERAIS DA ENCOMENDA ============ --}}
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            
            {{-- Itens da Encomenda --}}
            <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); padding: 2rem;">
                <h3 style="font-weight: 800; font-size: 1.25rem; margin: 0 0 1.5rem 0; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem; color: var(--text);">
                    👕 Artigos Encomendados
                </h3>

                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    @foreach($order->items as $item)
                        <div style="display: grid; grid-template-columns: 100px 1fr auto; gap: 1.5rem; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 1.5rem; last-child: { border: none; padding: 0 }">
                            
                            {{-- Preview da T-Shirt com CSS Overlay --}}
                            <div style="position: relative; width: 100px; height: 110px; border-radius: 8px; overflow: hidden; background: #f8fafc; border: 1px solid var(--border); flex-shrink: 0;">
                                @if($item->color && $item->color->tshirt_base_url)
                                    <img src="{{ $item->color->tshirt_base_url }}"
                                         alt="T-shirt"
                                         style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="position: absolute; inset: 0; background: #e2e8f0;"></div>
                                @endif

                                @if($item->tshirt_image && $item->tshirt_image->image_url)
                                    @php
                                        $customConfig = $item->custom ? json_decode($item->custom, true) : [];
                                        $top = $customConfig['top'] ?? 47.5;
                                        $left = $customConfig['left'] ?? 50.0;
                                        $scale = $customConfig['scale'] ?? 45;
                                        $rotate = $customConfig['rotate'] ?? 0;
                                        $opacity = $customConfig['opacity'] ?? 1.0;
                                        // G5: Private images use the secure route
                                        $stampSrc = $item->tshirt_image->customer_id
                                            ? route('private.tshirt-images.show', $item->tshirt_image->image_url)
                                            : asset('storage/tshirt_images/' . $item->tshirt_image->image_url);
                                    @endphp
                                    <img src="{{ $stampSrc }}"
                                         alt="Estampa"
                                         style="position: absolute; 
                                                width: {{ $scale }}%; 
                                                height: {{ $scale }}%; 
                                                object-fit: contain; 
                                                top: {{ $top }}%; 
                                                left: {{ $left }}%; 
                                                transform: translate(-50%, -50%) rotate({{ $rotate }}deg); 
                                                opacity: {{ $opacity }}; 
                                                filter: drop-shadow(0 1px 2px rgba(0,0,0,0.2));">
                                @endif
                            </div>

                            {{-- Detalhes do Artigo --}}
                            <div>
                                <h4 style="font-weight: 700; font-size: 1rem; margin: 0 0 0.25rem 0; color: var(--text);">
                                    {{ $item->tshirt_image->name ?? 'Estampa Apagada' }}
                                </h4>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; font-size: 0.825rem; color: #64748b;">
                                    <span><strong>Cor:</strong> {{ $item->color->name ?? $item->color_code }}</span>
                                    <span><strong>Tamanho:</strong> {{ $item->size }}</span>
                                    <span><strong>Quantidade:</strong> {{ $item->qty }}</span>
                                </div>
                                <div style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--primary); font-weight: 600;">
                                    Preço unitário no momento da compra: {{ number_format($item->unit_price, 2) }}€
                                </div>
                            </div>

                            {{-- Subtotal do Item --}}
                            <div style="font-weight: 800; font-size: 1.1rem; color: var(--text);">
                                {{ number_format($item->sub_total, 2) }}€
                            </td>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Justificação de Cancelamento se aplicável --}}
            @if($order->status === 'canceled')
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius); padding: 1.5rem; color: #991b1b;">
                    <h4 style="font-weight: 800; font-size: 1.05rem; margin: 0 0 0.5rem 0;">⚠️ Motivo do Cancelamento</h4>
                    <p style="margin: 0; font-size: 0.95rem;">
                        {{ $order->reason_for_cancellation ?: 'Nenhum motivo específico indicado pelo administrador.' }}
                    </p>
                </div>
            @endif

        </div>

        {{-- ============ COLUNA DIREITA: DADOS DE ENVIO / AÇÕES ============ --}}
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            
            {{-- Dados da Encomenda --}}
            <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); padding: 1.5rem;">
                <h3 style="font-weight: 800; font-size: 1.1rem; margin: 0 0 1rem 0; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; color: var(--text);">
                    Faturação e Envio
                </h3>

                <div style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.9rem;">
                    <div>
                        <strong style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; display: block; margin-bottom: 0.2rem;">NIF</strong>
                        <span style="color: var(--text); font-weight: 600;">{{ $order->nif }}</span>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; display: block; margin-bottom: 0.2rem;">Morada de Entrega</strong>
                        <span style="color: var(--text); line-height: 1.4;">{{ $order->address }}</span>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; display: block; margin-bottom: 0.2rem;">Método de Pagamento</strong>
                        <span style="color: var(--text); font-weight: 500;">{{ $order->payment_type }}</span>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; display: block; margin-bottom: 0.2rem;">Referência de Pagamento</strong>
                        <code style="background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">{{ $order->payment_ref }}</code>
                    </div>
                    @if($order->notes)
                        <div>
                            <strong style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; display: block; margin-bottom: 0.2rem;">Notas do Utilizador</strong>
                            <span style="color: var(--text); font-style: italic; font-size: 0.85rem;">"{{ $order->notes }}"</span>
                        </div>
                    @endif
                    <hr style="border: 0; border-top: 1px solid var(--border); margin: 0.5rem 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 700;">Total Pago</span>
                        <span style="font-weight: 800; font-size: 1.25rem; color: var(--primary);">{{ number_format($order->total_price, 2) }}€</span>
                    </div>
                </div>
            </div>

            {{-- Ações baseadas no Estado --}}
            @if($order->status === 'closed')
                <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); padding: 1.5rem;">
                    <h3 style="font-weight: 800; font-size: 1.1rem; margin: 0 0 1rem 0; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; color: var(--text);">
                        Documentos
                    </h3>
                    <a href="{{ route('orders.receipt', $order) }}" class="btn btn-primary" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem; width: 100%; border-radius: 8px; font-weight: 700; text-decoration: none; text-align: center;">
                        📄 Descarregar Recibo PDF
                    </a>
                </div>
            @endif

            {{-- ============ SEÇÃO ADMINISTRATIVA (ADMIN / FUNCIONÁRIO) ============ --}}
            @if(Auth::user()->user_type !== 'C' && $order->status === 'pending')
                <div style="background: #f8fafc; border-radius: var(--radius); border: 1px solid var(--border); padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                    <h3 style="font-weight: 800; font-size: 1.1rem; margin: 0; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; color: var(--text);">
                        ⚡ Ações de Gestão
                    </h3>

                    {{-- Enviar / Fechar Encomenda (Disponível para Staff e Admin) --}}
                    <form action="{{ route('orders.update-status', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="closed">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; border-radius: 8px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                            🚚 Marcar como Enviada
                        </button>
                    </form>

                    {{-- Cancelar Encomenda (Disponível apenas para Administrador) --}}
                    @if(Auth::user()->user_type === 'A')
                        <div style="border-top: 1px solid var(--border); padding-top: 1rem;">
                            <form action="{{ route('orders.update-status', $order) }}" method="POST" id="cancel-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="canceled">

                                <label for="reason_for_cancellation" style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 0.3rem;">
                                    Razão do Cancelamento (Opcional):
                                </label>
                                <textarea name="reason_for_cancellation" id="reason_for_cancellation" rows="2" placeholder="Ex: Artigo indisponível, NIF incorreto..."
                                          style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem; font-family: inherit; margin-bottom: 0.5rem; resize: vertical;"></textarea>

                                <button type="submit" class="btn btn-outline" style="width: 100%; padding: 0.75rem; border-radius: 8px; font-weight: 700; color: #ef4444; border-color: #fecaca; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                    ✕ Cancelar Encomenda
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
