<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartFromRequest;
use App\Models\Color;
use App\Models\Price;
use App\Models\Tshirt_image;

class CartController extends Controller
{
    /**
     * Build a standardized cart item array with price/discount logic applied.
     */
    private function buildCartItems(array $cart, Price $priceConfig): array
    {
        $items = [];
        $total = 0;

        $tshirtImageIds = collect($cart)->pluck('tshirt_image_id')->unique()->toArray();
        $colorCodes = collect($cart)->pluck('color_code')->unique()->toArray();

        $tshirtImages = Tshirt_image::findMany($tshirtImageIds)->keyBy('id');
        $colors = Color::findMany($colorCodes)->keyBy('code');

        foreach ($cart as $key => $details) {
            $tshirtImage = $tshirtImages->get($details['tshirt_image_id']);
            if (! $tshirtImage) {
                continue;
            }

            $color = $colors->get($details['color_code']);
            $qty = $details['quantity'];

            // Determine base price (catalog vs. own image)
            $isOwn = ! is_null($tshirtImage->customer_id);
            $basePrice = $isOwn ? $priceConfig->unit_price_own : $priceConfig->unit_price_catalog;
            $discountPrice = $isOwn ? $priceConfig->unit_price_own_discount : $priceConfig->unit_price_catalog_discount;

            // Apply volume discount if quantity >= threshold
            $isDiscounted = ($qty >= $priceConfig->qty_discount);
            $unitPrice = $isDiscounted ? $discountPrice : $basePrice;
            $subtotal = $unitPrice * $qty;

            $items[] = [
                'key' => $key,
                'tshirtImage' => $tshirtImage,
                'color' => $color,
                'size' => $details['size'],
                'quantity' => $qty,
                'base_price' => $basePrice,
                'unit_price' => $unitPrice,
                'is_discounted' => $isDiscounted,
                'subtotal' => $subtotal,
                'custom' => $details['custom'] ?? [
                    'top' => 47.5, 'left' => 50.0, 'scale' => 45.0, 'rotate' => 0, 'opacity' => 1.0,
                ],
            ];

            $total += $subtotal;
        }

        return [$items, $total];
    }

    public function index()
    {
        $cart = session('cart', []);
        $priceConfig = Price::first();

        if (! $priceConfig) {
            return view('cart.index', ['items' => [], 'total' => 0, 'priceConfig' => null, 'colors' => collect()]);
        }

        [$items, $total] = $this->buildCartItems($cart, $priceConfig);
        $colors = Color::orderBy('name')->get();

        return view('cart.index', compact('items', 'total', 'priceConfig', 'colors'));
    }

    public function add(CartFromRequest $request, Tshirt_image $tshirtImage)
    {
        $validated = $request->validated();

        $cart = session('cart', []);

        // Load custom options if present, otherwise default template settings
        $custom = [
            'top' => isset($validated['custom_top']) ? (float) $validated['custom_top'] : 47.5,
            'left' => isset($validated['custom_left']) ? (float) $validated['custom_left'] : 50.0,
            'scale' => isset($validated['custom_scale']) ? (float) $validated['custom_scale'] : 45.0,
            'rotate' => isset($validated['custom_rotate']) ? (int) $validated['custom_rotate'] : 0,
            'opacity' => isset($validated['custom_opacity']) ? (float) $validated['custom_opacity'] : 1.0,
        ];

        // Unique key based on: tshirt_image_id + color_code + size + md5(custom_json)
        $customHash = md5(json_encode($custom));
        $key = $tshirtImage->id.'_'.$validated['color'].'_'.$validated['size'].'_'.$customHash;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += (int) $validated['quantity'];
        } else {
            $cart[$key] = [
                'tshirt_image_id' => $tshirtImage->id,
                'color_code' => $validated['color'],
                'size' => $validated['size'],
                'quantity' => (int) $validated['quantity'],
                'custom' => $custom,
            ];
        }

        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'T-Shirt adicionada ao carrinho!');
    }

    public function update(CartFromRequest $request, $key)
    {
        $validated = $request->validated();
        $cart = session('cart', []);

        if (! isset($cart[$key])) {
            return redirect()->route('cart.index');
        }

        $qty = (int) $validated['quantity'];

        // Remove automatically if quantity is set to 0
        if ($qty === 0) {
            unset($cart[$key]);
            session(['cart' => $cart]);

            return redirect()->route('cart.index')->with('success', 'Item removido do carrinho.');
        }

        $item = $cart[$key];

        // Keep existing custom properties or update them if passed
        $custom = $item['custom'] ?? [
            'top' => 47.5, 'left' => 50.0, 'scale' => 45.0, 'rotate' => 0, 'opacity' => 1.0,
        ];
        if (isset($validated['custom_top'])) {
            $custom['top'] = (float) $validated['custom_top'];
        }
        if (isset($validated['custom_left'])) {
            $custom['left'] = (float) $validated['custom_left'];
        }
        if (isset($validated['custom_scale'])) {
            $custom['scale'] = (float) $validated['custom_scale'];
        }
        if (isset($validated['custom_rotate'])) {
            $custom['rotate'] = (int) $validated['custom_rotate'];
        }
        if (isset($validated['custom_opacity'])) {
            $custom['opacity'] = (float) $validated['custom_opacity'];
        }

        $newColor = ! empty($validated['color']) ? $validated['color'] : $item['color_code'];
        $newSize = ! empty($validated['size']) ? $validated['size'] : $item['size'];

        // Unique key based on: tshirt_image_id + color_code + size + md5(custom_json)
        $customHash = md5(json_encode($custom));
        $newKey = $item['tshirt_image_id'].'_'.$newColor.'_'.$newSize.'_'.$customHash;

        if ($newKey !== $key) {
            unset($cart[$key]);
            // Merge if the new key already exists
            if (isset($cart[$newKey])) {
                $cart[$newKey]['quantity'] += $qty;
            } else {
                $cart[$newKey] = [
                    'tshirt_image_id' => $item['tshirt_image_id'],
                    'color_code' => $newColor,
                    'size' => $newSize,
                    'quantity' => $qty,
                    'custom' => $custom,
                ];
            }
        } else {
            $cart[$key]['quantity'] = $qty;
            $cart[$key]['color_code'] = $newColor;
            $cart[$key]['size'] = $newSize;
            $cart[$key]['custom'] = $custom;
        }

        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'Carrinho atualizado.');
    }

    public function remove($key)
    {
        $cart = session('cart', []);
        unset($cart[$key]);
        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'Item removido do carrinho.');
    }

    public function destroy()
    {
        session()->forget('cart');

        return redirect()->route('home')->with('success', 'Carrinho esvaziado.');
    }
}
