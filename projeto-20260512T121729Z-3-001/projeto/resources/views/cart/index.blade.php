@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 4rem;">

    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
        <h1 style="font-size: 2.5rem; font-weight: 800; margin: 0; flex: 1;">🛒 O Seu Carrinho</h1>
        @if(count($items) > 0)
            <span style="color: #64748b; font-size: 0.95rem;">{{ count($items) }} {{ count($items) == 1 ? 'artigo' : 'artigos' }}</span>
        @endif
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.875rem; font-weight: 500;">
            {{ session('success') }}
        </div>
    @endif

    @if(count($items) > 0)
        <div style="display: grid; grid-template-columns: 1fr 360px; gap: 2rem; align-items: start;">

            {{-- ============ LISTA DE ITENS ============ --}}
            <div>
                @foreach($items as $item)
                    <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); padding: 1.5rem; margin-bottom: 1rem; display: grid; grid-template-columns: 140px 1fr; gap: 1.5rem; align-items: start;">

                        {{-- Preview da T-Shirt (CSS Overlay) --}}
                        <div style="position: relative; width: 140px; height: 160px; border-radius: 12px; overflow: hidden; background: #f8fafc; border: 1px solid var(--border); flex-shrink: 0;">
                            {{-- Base: t-shirt com a cor --}}
                            @if($item['color'] && $item['color']->tshirt_base_url)
                                <img src="{{ $item['color']->tshirt_base_url }}"
                                     alt="T-shirt {{ $item['color']->name }}"
                                     style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <div style="position: absolute; inset: 0; background: #e2e8f0;"></div>
                            @endif

                            {{-- Overlay: estampa arrastável e customizável --}}
                             @if($item['tshirtImage']->image_url)
                                 @php
                                     $top = $item['custom']['top'] ?? 47.5;
                                     $left = $item['custom']['left'] ?? 50.0;
                                     $scale = $item['custom']['scale'] ?? 45;
                                     $rotate = $item['custom']['rotate'] ?? 0;
                                     $opacity = $item['custom']['opacity'] ?? 1.0;
                                     // G5: Private images use the secure route, catalog images use public storage
                                     $stampSrc = $item['tshirtImage']->customer_id
                                         ? route('private.tshirt-images.show', $item['tshirtImage']->image_url)
                                         : asset('storage/tshirt_images/' . $item['tshirtImage']->image_url);
                                 @endphp
                                 <img src="{{ $stampSrc }}"
                                      alt="{{ $item['tshirtImage']->name }}"
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

                        {{-- Detalhes + Formulário de edição --}}
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <h4 style="font-weight: 700; font-size: 1.05rem; margin: 0; color: var(--text);">
                                    {{ $item['tshirtImage']->name }}
                                </h4>
                                {{-- Botão Remover --}}
                                <form action="{{ route('cart.remove', $item['key']) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0.25rem; font-size: 1.2rem;" title="Remover">✕</button>
                                </form>
                            </div>

                            {{-- Preço unitário com desconto --}}
                            <div style="margin-bottom: 1rem;">
                                @if($item['is_discounted'])
                                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                        <span style="text-decoration: line-through; color: #94a3b8; font-size: 0.9rem;">{{ number_format($item['base_price'], 2) }}€</span>
                                        <span style="font-size: 1.1rem; font-weight: 800; color: #16a34a;">{{ number_format($item['unit_price'], 2) }}€/un.</span>
                                        <span style="background: #dcfce7; color: #166534; font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 99px;">🏷️ Desconto Qty</span>
                                    </div>
                                @else
                                    <span style="font-size: 1.1rem; font-weight: 700; color: var(--primary);">{{ number_format($item['unit_price'], 2) }}€/un.</span>
                                    @if($priceConfig && $priceConfig->qty_discount > 1)
                                        <span style="display: block; font-size: 0.75rem; color: #94a3b8; margin-top: 0.1rem;">
                                            Desconto a partir de {{ $priceConfig->qty_discount }} unidades
                                        </span>
                                    @endif
                                @endif
                            </div>

                            {{-- Formulário de edição: quantidade + cor + tamanho --}}
                            <form action="{{ route('cart.update', $item['key']) }}" method="POST" style="display: grid; grid-template-columns: auto 1fr 1fr auto; gap: 0.5rem; align-items: end;">
                                @csrf
                                @method('PATCH')

                                {{-- Quantidade --}}
                                <div>
                                    <label style="display: block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.3rem;">Qtd.</label>
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="0"
                                           style="width: 65px; padding: 0.45rem; border-radius: 8px; border: 1px solid var(--border); text-align: center; font-weight: 700; font-size: 0.95rem;">
                                </div>

                                {{-- Cor --}}
                                <div>
                                    <label style="display: block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.3rem;">Cor</label>
                                    <select name="color" style="width: 100%; padding: 0.45rem; border-radius: 8px; border: 1px solid var(--border); background: white; font-size: 0.875rem;">
                                        @foreach($colors as $color)
                                            <option value="{{ $color->code }}" {{ $item['color'] && $item['color']->code === $color->code ? 'selected' : '' }}>
                                                {{ $color->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Tamanho --}}
                                <div>
                                    <label style="display: block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.3rem;">Tamanho</label>
                                    <select name="size" style="width: 100%; padding: 0.45rem; border-radius: 8px; border: 1px solid var(--border); background: white; font-size: 0.875rem;">
                                        @foreach(['XS','S','M','L','XL'] as $s)
                                            <option value="{{ $s }}" {{ $item['size'] === $s ? 'selected' : '' }}>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Botão Atualizar --}}
                                <div>
                                    <button type="submit" class="btn btn-outline" style="padding: 0.45rem 0.75rem; font-size: 0.8rem; white-space: nowrap;">
                                        Atualizar
                                    </button>
                                </div>
                            </form>

                            {{-- Subtotal --}}
                            <div style="margin-top: 0.75rem; text-align: right;">
                                <span style="font-size: 0.8rem; color: #64748b;">Subtotal: </span>
                                <span style="font-size: 1.1rem; font-weight: 800; color: var(--text);">{{ number_format($item['subtotal'], 2) }}€</span>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Limpar carrinho --}}
                <div style="text-align: right; margin-top: 0.5rem;">
                    <form action="{{ route('cart.destroy') }}" method="POST" style="display: inline;"
                          onsubmit="return confirm('Tem a certeza que quer esvaziar o carrinho?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background: none; border: none; color: #94a3b8; font-size: 0.875rem; cursor: pointer; text-decoration: underline;">
                            🗑 Esvaziar carrinho
                        </button>
                    </form>
                </div>
            </div>

            {{-- ============ PAINEL LATERAL ============ --}}
            <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); padding: 1.5rem; position: sticky; top: 2rem;">
                <h3 style="font-weight: 800; margin-bottom: 1.5rem; font-size: 1.2rem;">Resumo da Encomenda</h3>

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; color: #64748b;">
                    <span>Subtotal ({{ count($items) }} {{ count($items) == 1 ? 'artigo' : 'artigos' }})</span>
                    <span>{{ number_format($total, 2) }}€</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; color: #64748b;">
                    <span>Envio</span>
                    <span style="color: #22c55e; font-weight: 600;">Grátis</span>
                </div>
                <hr style="border: 0; border-top: 1px solid var(--border); margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
                    <span style="font-weight: 700; font-size: 1.25rem;">Total</span>
                    <span style="font-weight: 800; font-size: 1.5rem; color: var(--primary);">{{ number_format($total, 2) }}€</span>
                </div>

                {{-- Botão de Checkout com lógica de acesso --}}
                @auth
                    @if(Auth::user()->user_type === 'C')
                        @if(Auth::user()->hasVerifiedEmail())
                            <a href="{{ route('checkout.index') }}" id="btn-checkout" class="btn btn-primary"
                               style="display: block; text-align: center; padding: 1rem; font-size: 1.1rem; border-radius: 12px;">
                                💳 Finalizar Encomenda
                            </a>
                        @else
                            <div style="background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; padding: 1rem; border-radius: 12px; text-align: center; font-size: 0.875rem;">
                                ⚠️ Confirme o seu e-mail para finalizar a encomenda.
                                <a href="{{ route('verification.notice') }}" style="display: block; margin-top: 0.5rem; font-weight: 700; color: #92400e;">Verificar E-mail</a>
                            </div>
                        @endif
                    @else
                        {{-- Admin ou Funcionário: sem acesso ao checkout --}}
                        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 1rem; border-radius: 12px; text-align: center; font-size: 0.875rem;">
                            ⚠️ Administradores e Funcionários não têm acesso ao checkout.
                        </div>
                    @endif
                @else
                    {{-- Utilizador anónimo: redirecionar para login --}}
                    <a href="{{ route('login') }}?redirect=checkout" id="btn-checkout" class="btn btn-primary"
                       style="display: block; text-align: center; padding: 1rem; font-size: 1.1rem; border-radius: 12px;">
                        🔐 Login para Finalizar
                    </a>
                    <p style="text-align: center; font-size: 0.8rem; color: #94a3b8; margin-top: 0.75rem;">O seu carrinho será preservado.</p>
                @endauth
            </div>
        </div>

    @else
        {{-- Carrinho vazio --}}
        <div style="text-align: center; padding: 5rem 2rem; background: white; border-radius: var(--radius); border: 1px solid var(--border);">
            <div style="font-size: 5rem; margin-bottom: 1.5rem;">🛒</div>
            <h2 style="font-weight: 800; margin-bottom: 1rem; font-size: 1.75rem;">O seu carrinho está vazio</h2>
            <p style="color: #64748b; margin-bottom: 2rem; font-size: 1rem;">Parece que ainda não escolheu nenhuma T-Shirt incrível!</p>
            <a href="{{ route('home') }}" class="btn btn-primary" style="padding: 0.875rem 2.5rem; font-size: 1.05rem;">
                Explorar Catálogo
            </a>
        </div>
    @endif
</div>
@endsection
