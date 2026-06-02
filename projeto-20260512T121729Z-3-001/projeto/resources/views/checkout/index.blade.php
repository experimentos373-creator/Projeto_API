@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 4rem;">

    <div style="margin-bottom: 2rem;">
        <a href="{{ route('cart.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.5rem;">
            ← Voltar ao Carrinho
        </a>
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-top: 0.5rem; color: var(--text);">💳 Finalizar Encomenda</h1>
    </div>

    {{-- Alert de erros globais ou de pagamento --}}
    @error('payment')
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.9rem; font-weight: 500;">
            ❌ {{ $message }}
        </div>
    @enderror

    <div style="display: grid; grid-template-columns: 1fr 380px; gap: 2.5rem; align-items: start;">

        {{-- ============ COLUNA ESQUERDA: FORMULÁRIO DE CHECKOUT ============ --}}
        <form action="{{ route('checkout.store') }}" method="POST" style="background: white; border-radius: var(--radius); border: 1px solid var(--border); padding: 2rem; display: flex; flex-direction: column; gap: 1.5rem;">
            @csrf

            <h3 style="font-weight: 800; font-size: 1.25rem; margin: 0 0 0.5rem 0; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem; color: var(--text);">
                Informações de Faturação e Envio
            </h3>

            {{-- NIF --}}
            <div>
                <label for="nif" style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 0.5rem;">
                    NIF (Número de Identificação Fiscal) <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="nif" id="nif" value="{{ old('nif', $nif) }}" required maxlength="9" pattern="[0-9]{9}"
                       placeholder="Ex: 123456789"
                       style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid @error('nif') #ef4444 @else var(--border) @enderror; font-size: 0.95rem;">
                @error('nif')
                    <span style="color: #ef4444; font-size: 0.8rem; display: block; margin-top: 0.25rem;">{{ $message }}</span>
                @enderror
            </div>

            {{-- Morada --}}
            <div>
                <label for="address" style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 0.5rem;">
                    Morada Completa de Entrega <span style="color: #ef4444;">*</span>
                </label>
                <textarea name="address" id="address" rows="3" required placeholder="Rua, número, andar, código postal e cidade"
                          style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid @error('address') #ef4444 @else var(--border) @enderror; font-size: 0.95rem; font-family: inherit; resize: vertical;">{{ old('address', $address) }}</textarea>
                @error('address')
                    <span style="color: #ef4444; font-size: 0.8rem; display: block; margin-top: 0.25rem;">{{ $message }}</span>
                @enderror
            </div>

            {{-- Notas --}}
            <div>
                <label for="notes" style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 0.5rem;">
                    Notas da Encomenda (Opcional)
                </label>
                <textarea name="notes" id="notes" rows="2" placeholder="Instruções de entrega ou observações adicionais..."
                          style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); font-size: 0.95rem; font-family: inherit; resize: vertical;">{{ old('notes') }}</textarea>
                @error('notes')
                    <span style="color: #ef4444; font-size: 0.8rem; display: block; margin-top: 0.25rem;">{{ $message }}</span>
                @enderror
            </div>

            <h3 style="font-weight: 800; font-size: 1.25rem; margin: 1rem 0 0.5rem 0; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem; color: var(--text);">
                Método de Pagamento
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                {{-- Visa --}}
                <label style="border: 2px solid {{ old('payment_type', $paymentType) === 'Visa' ? 'var(--primary)' : 'var(--border)' }}; border-radius: 10px; padding: 1rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; cursor: pointer; text-align: center; background: white;" id="label-Visa">
                    <input type="radio" name="payment_type" value="Visa" class="payment-radio" {{ old('payment_type', $paymentType) === 'Visa' ? 'checked' : '' }} style="margin: 0;">
                    <span style="font-size: 1.5rem;">💳</span>
                    <span style="font-size: 0.85rem; font-weight: 700;">Visa</span>
                </label>
                
                {{-- PayPal --}}
                <label style="border: 2px solid {{ old('payment_type', $paymentType) === 'PayPal' ? 'var(--primary)' : 'var(--border)' }}; border-radius: 10px; padding: 1rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; cursor: pointer; text-align: center; background: white;" id="label-PayPal">
                    <input type="radio" name="payment_type" value="PayPal" class="payment-radio" {{ old('payment_type', $paymentType) === 'PayPal' ? 'checked' : '' }} style="margin: 0;">
                    <span style="font-size: 1.5rem;">🅿️</span>
                    <span style="font-size: 0.85rem; font-weight: 700;">PayPal</span>
                </label>
                
                {{-- MB WAY --}}
                <label style="border: 2px solid {{ old('payment_type', $paymentType) === 'MB WAY' || !old('payment_type', $paymentType) ? 'var(--primary)' : 'var(--border)' }}; border-radius: 10px; padding: 1rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; cursor: pointer; text-align: center; background: white;" id="label-MBWAY">
                    <input type="radio" name="payment_type" value="MB WAY" class="payment-radio" {{ old('payment_type', $paymentType) === 'MB WAY' || !old('payment_type', $paymentType) ? 'checked' : '' }} style="margin: 0;">
                    <span style="font-size: 1.5rem;">📱</span>
                    <span style="font-size: 0.85rem; font-weight: 700;">MB WAY</span>
                </label>
            </div>
            @error('payment_type')
                <span style="color: #ef4444; font-size: 0.8rem; display: block; margin-top: -0.5rem;">{{ $message }}</span>
            @enderror

            {{-- Referência de Pagamento --}}
            <div id="payment-ref-container">
                <label for="payment_ref" id="payment-ref-label" style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 0.5rem;">
                    Referência de Pagamento <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="payment_ref" id="payment_ref" value="{{ old('payment_ref', $paymentRef) }}" required
                       style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid @error('payment_ref') #ef4444 @else var(--border) @enderror; font-size: 0.95rem;">
                @error('payment_ref')
                    <span style="color: #ef4444; font-size: 0.8rem; display: block; margin-top: 0.25rem;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-top: 1rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: 800; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    🔒 Confirmar e Pagar Encomenda
                </button>
                <p style="text-align: center; color: #94a3b8; font-size: 0.75rem; margin-top: 0.75rem;">
                    Ao clicar, o pagamento será processado na API externa simulada.
                </p>
            </div>
        </form>

        {{-- ============ COLUNA DIREITA: RESUMO DA ENCOMENDA ============ --}}
        <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); padding: 1.5rem; position: sticky; top: 2rem;">
            <h3 style="font-weight: 800; margin-bottom: 1.5rem; font-size: 1.2rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; color: var(--text);">
                Resumo do Pedido
            </h3>

            {{-- Lista Simplificada de Itens --}}
            <div style="max-height: 250px; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem; padding-right: 0.25rem;">
                @foreach($items as $item)
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        {{-- Miniatura do Design --}}
                        <div style="width: 50px; height: 50px; border-radius: 8px; overflow: hidden; background: #f8fafc; border: 1px solid var(--border); flex-shrink: 0; display: flex; align-items: center; justify-content: center; position: relative;">
                            @if($item['tshirtImage']->image_url)
                                <img src="{{ asset('storage/tshirt_images/' . $item['tshirtImage']->image_url) }}"
                                     alt="Item"
                                     style="width: 80%; height: 80%; object-fit: contain;">
                            @endif
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h4 style="font-size: 0.85rem; font-weight: 700; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text);">
                                {{ $item['tshirtImage']->name }}
                            </h4>
                            <span style="font-size: 0.8rem; color: #64748b;">
                                Qtd: {{ $item['quantity'] }} • Tam: {{ $item['size'] }}
                            </span>
                        </div>
                        <div style="font-size: 0.9rem; font-weight: 700; color: var(--text); flex-shrink: 0;">
                            {{ number_format($item['subtotal'], 2) }}€
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; color: #64748b; font-size: 0.9rem;">
                <span>Subtotal ({{ $itemsCount }} {{ $itemsCount == 1 ? 'artigo' : 'artigos' }})</span>
                <span>{{ number_format($total, 2) }}€</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">
                <span>Custos de Envio</span>
                <span style="color: #22c55e; font-weight: 600;">Grátis</span>
            </div>
            <hr style="border: 0; border-top: 1px solid var(--border); margin-bottom: 1.25rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="font-weight: 700; font-size: 1.1rem; color: var(--text);">Total a Pagar</span>
                <span style="font-weight: 800; font-size: 1.35rem; color: var(--primary);">{{ number_format($total, 2) }}€</span>
            </div>
        </div>

    </div>
</div>

{{-- Script Javascript para validações dinâmicas de referência conforme o método selecionado --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('.payment-radio');
        const refLabel = document.getElementById('payment-ref-label');
        const refInput = document.getElementById('payment_ref');
        const labels = {
            'Visa': document.getElementById('label-Visa'),
            'PayPal': document.getElementById('label-PayPal'),
            'MB WAY': document.getElementById('label-MBWAY')
        };

        function updatePaymentFields(type) {
            // Update labels styles
            Object.keys(labels).forEach(key => {
                if (labels[key]) {
                    if (key === type) {
                        labels[key].style.borderColor = 'var(--primary)';
                        labels[key].style.background = '#f8fafc';
                    } else {
                        labels[key].style.borderColor = 'var(--border)';
                        labels[key].style.background = 'white';
                    }
                }
            });

            // Adjust input attributes based on selection
            if (type === 'Visa') {
                refLabel.innerHTML = 'Número do Cartão (Visa) <span style="color: #ef4444;">*</span>';
                refInput.placeholder = 'Ex: 4123456789012345 (16 dígitos iniciados por 4)';
                refInput.type = 'text';
                refInput.maxLength = 16;
                refInput.pattern = '4[0-9]{15}';
                refInput.title = 'Deve conter exatamente 16 dígitos e iniciar por 4.';
            } else if (type === 'PayPal') {
                refLabel.innerHTML = 'E-mail da Conta PayPal <span style="color: #ef4444;">*</span>';
                refInput.placeholder = 'Ex: email@exemplo.com';
                refInput.type = 'email';
                refInput.removeAttribute('maxLength');
                refInput.removeAttribute('pattern');
                refInput.title = 'Deve introduzir um e-mail válido.';
            } else if (type === 'MB WAY') {
                refLabel.innerHTML = 'Número de Telemóvel (MB WAY) <span style="color: #ef4444;">*</span>';
                refInput.placeholder = 'Ex: 912345678 (9 dígitos iniciados por 9)';
                refInput.type = 'text';
                refInput.maxLength = 9;
                refInput.pattern = '9[0-9]{8}';
                refInput.title = 'Deve conter exatamente 9 dígitos e iniciar por 9.';
            }
        }

        // Initialize state on load
        const checkedRadio = document.querySelector('.payment-radio:checked');
        if (checkedRadio) {
            updatePaymentFields(checkedRadio.value);
        } else {
            // Default
            updatePaymentFields('MB WAY');
        }

        // Add listeners
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                updatePaymentFields(this.value);
            });
        });
    });
</script>
@endsection
