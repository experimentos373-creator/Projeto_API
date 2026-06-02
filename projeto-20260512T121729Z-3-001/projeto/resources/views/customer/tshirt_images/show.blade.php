@extends('layouts.app')

@php
    // Load default template from tshirt_images.custom (same as catalog show)
    $customConfig = $tshirtImage->custom ? json_decode($tshirtImage->custom, true) : [];
    $top = $customConfig['top'] ?? 47.5;
    $left = $customConfig['left'] ?? 50.0;
    $scale = $customConfig['scale'] ?? 45;
    $rotate = $customConfig['rotate'] ?? 0;
    $opacity = $customConfig['opacity'] ?? 1.0;
@endphp

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 4rem;">
    <a href="{{ route('customer.tshirt-images.index') }}" style="display: inline-flex; align-items: center; text-decoration: none; color: var(--secondary); font-weight: 600; margin-bottom: 2rem; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--secondary)'">
        <svg style="width: 20px; height: 20px; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Voltar às Minhas Imagens
    </a>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: start;">
        {{-- Preview com Editor Interativo --}}
        <div style="background: #f8fafc; border-radius: 24px; overflow: hidden; border: 1px solid var(--border); box-shadow: var(--shadow-md); position: relative; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; user-select: none;">
            {{-- Base: t-shirt com a cor --}}
            <img id="preview-tshirt-base"
                 src="{{ $colors->first() ? $colors->first()->tshirt_base_url : asset('storage/tshirt_base/plain_white.png') }}"
                 alt="T-shirt Base"
                 style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: all 0.2s ease; user-select: none; -webkit-user-drag: none;">

            {{-- Overlay: estampa arrastável (servida via rota privada) --}}
            @if($tshirtImage->image_url)
                <img id="preview-stamp"
                     src="{{ route('private.tshirt-images.show', $tshirtImage->image_url) }}"
                     alt="{{ $tshirtImage->name }}"
                     style="position: absolute;
                            width: {{ $scale }}%;
                            height: {{ $scale }}%;
                            object-fit: contain;
                            top: {{ $top }}%;
                            left: {{ $left }}%;
                            transform: translate(-50%, -50%) rotate({{ $rotate }}deg);
                            opacity: {{ $opacity }};
                            cursor: move;
                            user-select: none;
                            -webkit-user-drag: none;
                            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15));">
            @endif
        </div>

        {{-- Detalhes --}}
        <div>
            <span style="display: inline-block; padding: 0.25rem 0.75rem; background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; border-radius: 99px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 1rem;">
                🔒 Imagem Privada
            </span>

            <h1 style="font-size: 3rem; font-weight: 800; color: var(--text); margin-bottom: 1rem; line-height: 1.1;">
                {{ $tshirtImage->name }}
            </h1>

            @if($tshirtImage->description)
                <p style="font-size: 1.125rem; color: #64748b; line-height: 1.6; margin-bottom: 2rem;">
                    {{ $tshirtImage->description }}
                </p>
            @endif

            <div style="margin-bottom: 2.5rem;">
                <span style="font-size: 2.5rem; font-weight: 800; color: var(--primary);">
                    {{ number_format($price->unit_price_own, 2) }}€
                </span>
                <span style="display: block; font-size: 0.875rem; color: #94a3b8; margin-top: 0.25rem;">IVA incluído · Preço imagem personalizada</span>
            </div>

            <form action="{{ route('cart.add', $tshirtImage) }}" method="POST">
                @csrf

                {{-- Inputs Escondidos para as opções Custom --}}
                <input type="hidden" name="custom_top" id="custom_top" value="{{ $top }}">
                <input type="hidden" name="custom_left" id="custom_left" value="{{ $left }}">
                <input type="hidden" name="custom_scale" id="custom_scale" value="{{ $scale }}">
                <input type="hidden" name="custom_rotate" id="custom_rotate" value="{{ $rotate }}">
                <input type="hidden" name="custom_opacity" id="custom_opacity" value="{{ $opacity }}">

                {{-- Seleção de Cor --}}
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: var(--text);">Escolha a Cor</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                        @foreach($colors as $color)
                            <label style="cursor: pointer; position: relative;">
                                <input type="radio" name="color" value="{{ $color->code }}"
                                       data-base-url="{{ $color->tshirt_base_url }}"
                                       {{ $loop->first ? 'checked' : '' }}
                                       style="position: absolute; opacity: 0; cursor: pointer;" required>
                                <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #{{ $color->code }}; border: 3px solid white; box-shadow: 0 0 0 1px #e2e8f0; transition: all 0.2s;" class="color-swatch"></div>
                                <span style="display: block; font-size: 0.75rem; text-align: center; margin-top: 0.25rem; color: #64748b;">{{ $color->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Seleção de Tamanho --}}
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: var(--text);">Escolha o Tamanho</label>
                    <div style="display: flex; gap: 1rem;">
                        @foreach($sizes as $size)
                            <label style="flex: 1; cursor: pointer;">
                                <input type="radio" name="size" value="{{ $size }}" {{ $loop->first ? 'checked' : '' }} style="position: absolute; opacity: 0;" required>
                                <div style="text-align: center; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 12px; font-weight: 700; transition: all 0.2s;" class="size-box">
                                    {{ $size }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Controles de Customização --}}
                <div style="margin-bottom: 2.5rem; background: #f8fafc; padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border);">
                    <label style="display: block; font-size: 1.05rem; font-weight: 800; margin-bottom: 1rem; color: var(--text);">🎨 Ajustar Posição e Estilo</label>
                    <p style="font-size: 0.8rem; color: #64748b; margin-top: -0.75rem; margin-bottom: 1.25rem;">
                        Arraste a estampa diretamente na T-shirt para reposicioná-la.
                    </p>

                    {{-- Slider de Escala --}}
                    <div style="margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 0.4rem;">
                            <span>Tamanho (Escala)</span>
                            <span id="val-scale" style="color: var(--primary);">{{ $scale }}%</span>
                        </div>
                        <input type="range" id="slider-scale" min="10" max="80" value="{{ $scale }}" style="width: 100%; accent-color: var(--primary);">
                    </div>

                    {{-- Slider de Rotação --}}
                    <div style="margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 0.4rem;">
                            <span>Rotação</span>
                            <span id="val-rotate" style="color: var(--primary);">{{ $rotate }}°</span>
                        </div>
                        <input type="range" id="slider-rotate" min="0" max="360" value="{{ $rotate }}" style="width: 100%; accent-color: var(--primary);">
                    </div>

                    {{-- Slider de Opacidade --}}
                    <div style="margin-bottom: 0.25rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 0.4rem;">
                            <span>Opacidade (Transparência)</span>
                            <span id="val-opacity" style="color: var(--primary);">{{ round($opacity * 100) }}%</span>
                        </div>
                        <input type="range" id="slider-opacity" min="10" max="100" value="{{ round($opacity * 100) }}" style="width: 100%; accent-color: var(--primary);">
                    </div>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <div style="display: flex; align-items: center; background: #f1f5f9; border-radius: 16px; padding: 0.5rem 1rem;">
                        <button type="button" onclick="this.nextElementSibling.stepDown()" style="background: none; border: none; font-size: 1.5rem; color: var(--secondary); cursor: pointer;">-</button>
                        <input type="number" name="quantity" value="1" min="1" style="width: 50px; text-align: center; background: none; border: none; font-weight: 800; font-size: 1.125rem; appearance: textfield;">
                        <button type="button" onclick="this.previousElementSibling.stepUp()" style="background: none; border: none; font-size: 1.25rem; color: var(--secondary); cursor: pointer;">+</button>
                    </div>
                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 1rem; font-size: 1.125rem; border-radius: 16px;">
                        Adicionar ao Carrinho
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    input[type="radio"]:checked + .color-swatch {
        box-shadow: 0 0 0 2px var(--primary);
        transform: scale(1.1);
    }
    input[type="radio"]:checked + .size-box {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        box-shadow: var(--shadow-md);
    }
    .color-swatch:hover {
        transform: translateY(-2px);
    }
    .size-box:hover {
        border-color: var(--primary);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const previewBase = document.getElementById('preview-tshirt-base');
        const colorRadios = document.querySelectorAll('input[name="color"]');

        colorRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.checked) {
                    const baseUrl = this.getAttribute('data-base-url');
                    if (previewBase && baseUrl) {
                        previewBase.src = baseUrl;
                    }
                }
            });
        });

        // Editor Interativo (G7 — reused from catalog)
        const stamp = document.getElementById('preview-stamp');
        const container = stamp ? stamp.parentElement : null;

        if (stamp && container) {
            const inputTop = document.getElementById('custom_top');
            const inputLeft = document.getElementById('custom_left');
            const inputScale = document.getElementById('custom_scale');
            const inputRotate = document.getElementById('custom_rotate');
            const inputOpacity = document.getElementById('custom_opacity');

            const sliderScale = document.getElementById('slider-scale');
            const sliderRotate = document.getElementById('slider-rotate');
            const sliderOpacity = document.getElementById('slider-opacity');

            const valScale = document.getElementById('val-scale');
            const valRotate = document.getElementById('val-rotate');
            const valOpacity = document.getElementById('val-opacity');

            let currentScale = parseFloat(inputScale.value);
            let currentRotate = parseFloat(inputRotate.value);
            let currentOpacity = parseFloat(inputOpacity.value);

            function updateStampStyles() {
                stamp.style.width = currentScale + '%';
                stamp.style.height = currentScale + '%';
                stamp.style.transform = `translate(-50%, -50%) rotate(${currentRotate}deg)`;
                stamp.style.opacity = currentOpacity;

                inputScale.value = currentScale;
                inputRotate.value = currentRotate;
                inputOpacity.value = currentOpacity.toFixed(2);
            }

            sliderScale.addEventListener('input', function() {
                currentScale = parseInt(this.value);
                valScale.textContent = currentScale + '%';
                updateStampStyles();
            });

            sliderRotate.addEventListener('input', function() {
                currentRotate = parseInt(this.value);
                valRotate.textContent = currentRotate + '°';
                updateStampStyles();
            });

            sliderOpacity.addEventListener('input', function() {
                currentOpacity = parseInt(this.value) / 100;
                valOpacity.textContent = this.value + '%';
                updateStampStyles();
            });

            // Drag & Drop (Mouse + Touch)
            stamp.addEventListener('mousedown', startDrag);
            stamp.addEventListener('touchstart', startDrag, { passive: false });

            function startDrag(e) {
                e.preventDefault();
                const rect = container.getBoundingClientRect();
                const isTouch = e.type === 'touchstart';

                function drag(evt) {
                    const clientX = isTouch ? evt.touches[0].clientX : evt.clientX;
                    const clientY = isTouch ? evt.touches[0].clientY : evt.clientY;

                    let leftPercent = ((clientX - rect.left) / rect.width) * 100;
                    let topPercent = ((clientY - rect.top) / rect.height) * 100;

                    leftPercent = Math.max(10, Math.min(leftPercent, 90));
                    topPercent = Math.max(10, Math.min(topPercent, 90));

                    stamp.style.left = leftPercent + '%';
                    stamp.style.top = topPercent + '%';

                    inputLeft.value = leftPercent.toFixed(1);
                    inputTop.value = topPercent.toFixed(1);
                }

                function endDrag() {
                    if (isTouch) {
                        window.removeEventListener('touchmove', drag);
                        window.removeEventListener('touchend', endDrag);
                    } else {
                        window.removeEventListener('mousemove', drag);
                        window.removeEventListener('mouseup', endDrag);
                    }
                }

                if (isTouch) {
                    window.addEventListener('touchmove', drag, { passive: false });
                    window.addEventListener('touchend', endDrag);
                } else {
                    window.addEventListener('mousemove', drag);
                    window.addEventListener('mouseup', endDrag);
                }
            }
        }
    });
</script>
@endsection
