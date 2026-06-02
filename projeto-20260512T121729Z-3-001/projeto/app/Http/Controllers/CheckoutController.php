<?php

namespace App\Http\Controllers;

use App\Models\Tshirt_image;
use App\Models\Price;
use App\Models\Order;
use App\Models\Order_item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    /**
     * Show the checkout page.
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login', ['redirect' => 'checkout']);
        }

        if (!Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('info', 'O seu carrinho está vazio.');
        }

        $user = Auth::user();
        $customer = $user->customer; // Relationship HasOne from User

        // Pre-fill fields
        $nif = $customer ? $customer->nif : '';
        $address = $customer ? $customer->address : '';
        $paymentType = $customer ? $customer->default_payment_type : '';
        $paymentRef = $customer ? $customer->default_payment_ref : '';

        // MB to MB WAY mapping
        if ($paymentType === 'MB') {
            $paymentType = 'MB WAY';
        }

        $priceConfig = Price::first();
        if (!$priceConfig) {
            return redirect()->route('cart.index')->with('error', 'Configurações de preços não encontradas.');
        }

        // Calculate total and details for the checkout sidebar summary
        $total = 0;
        $itemsCount = 0;
        $items = [];
        foreach ($cart as $key => $details) {
            $tshirtImage = Tshirt_image::find($details['tshirt_image_id']);
            if (!$tshirtImage) {
                continue;
            }
            $qty = (int)$details['quantity'];
            $isOwn = !is_null($tshirtImage->customer_id);
            $basePrice = $isOwn ? $priceConfig->unit_price_own : $priceConfig->unit_price_catalog;
            $discountPrice = $isOwn ? $priceConfig->unit_price_own_discount : $priceConfig->unit_price_catalog_discount;
            
            $isDiscounted = ($qty >= $priceConfig->qty_discount);
            $unitPrice = $isDiscounted ? $discountPrice : $basePrice;
            $subtotal = $unitPrice * $qty;
            
            $total += $subtotal;
            $itemsCount += $qty;
            $items[] = [
                'tshirtImage' => $tshirtImage,
                'quantity' => $qty,
                'subtotal' => $subtotal,
                'size' => $details['size'],
            ];
        }

        return view('checkout.index', compact('nif', 'address', 'paymentType', 'paymentRef', 'total', 'itemsCount', 'items'));
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login', ['redirect' => 'checkout']);
        }

        if (!Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'O seu carrinho está vazio.');
        }

        // Validate basic inputs
        $request->validate([
            'nif' => ['required', 'string', 'size:9', 'regex:/^[0-9]{9}$/'],
            'address' => ['required', 'string', 'max:1000'],
            'payment_type' => ['required', 'in:Visa,PayPal,MB WAY'],
            'payment_ref' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'nif.required' => 'O NIF é obrigatório.',
            'nif.size' => 'O NIF deve ter exatamente 9 dígitos.',
            'nif.regex' => 'O NIF deve conter apenas números.',
            'address.required' => 'A morada de entrega é obrigatória.',
            'payment_type.required' => 'Selecione um método de pagamento.',
            'payment_ref.required' => 'A referência de pagamento é obrigatória.',
        ]);

        $paymentType = $request->payment_type;
        $paymentRef = $request->payment_ref;

        // Custom validation of payment references
        if ($paymentType === 'Visa') {
            if (!preg_match('/^4[0-9]{15}$/', $paymentRef)) {
                return back()->withInput()->withErrors(['payment_ref' => 'A referência para Visa deve ter 16 dígitos e iniciar por 4.']);
            }
        } elseif ($paymentType === 'PayPal') {
            if (!filter_var($paymentRef, FILTER_VALIDATE_EMAIL)) {
                return back()->withInput()->withErrors(['payment_ref' => 'A referência para PayPal deve ser um e-mail válido.']);
            }
        } elseif ($paymentType === 'MB WAY') {
            if (!preg_match('/^9[0-9]{8}$/', $paymentRef)) {
                return back()->withInput()->withErrors(['payment_ref' => 'A referência para MB WAY deve ter 9 dígitos e iniciar por 9.']);
            }
        }

        $priceConfig = Price::first();
        if (!$priceConfig) {
            return back()->withInput()->withErrors(['payment' => 'Configurações de preços não encontradas.']);
        }

        // Calculate final total and verify prices
        $totalPrice = 0;
        $orderItemsData = [];

        foreach ($cart as $key => $details) {
            $tshirtImage = Tshirt_image::find($details['tshirt_image_id']);
            if (!$tshirtImage) {
                continue;
            }
            $qty = (int)$details['quantity'];
            $colorCode = $details['color_code'];
            $size = $details['size'];

            $isOwn = !is_null($tshirtImage->customer_id);
            $basePrice = $isOwn ? $priceConfig->unit_price_own : $priceConfig->unit_price_catalog;
            $discountPrice = $isOwn ? $priceConfig->unit_price_own_discount : $priceConfig->unit_price_catalog_discount;
            
            $isDiscounted = ($qty >= $priceConfig->qty_discount);
            $unitPrice = $isDiscounted ? $discountPrice : $basePrice;
            $subtotal = $unitPrice * $qty;

            $totalPrice += $subtotal;

            $orderItemsData[] = [
                'tshirt_image_id' => $tshirtImage->id,
                'color_code' => $colorCode,
                'size' => $size,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'sub_total' => $subtotal,
                'custom' => isset($details['custom']) ? json_encode($details['custom']) : null,
            ];
        }

        $roundedTotal = round($totalPrice, 2);

        // FunShirt verification for payment value range
        if ($roundedTotal < 0.01 || $roundedTotal > 999999.99) {
            return back()->withInput()->withErrors(['payment' => 'O valor total da encomenda deve estar entre 0.01 e 999999.99.']);
        }

        // Trigger payment simulation
        try {
            $response = Http::post('https://ainet-payments-api.vercel.app/api/payments', [
                'type' => $paymentType,
                'reference' => $paymentRef,
                'value' => $roundedTotal
            ]);

            if ($response->failed()) {
                $errorMsg = $response->json('message') ?? 'O pagamento foi recusado. Verifique os dados ou o saldo da conta.';
                return back()->withInput()->withErrors(['payment' => $errorMsg]);
            }
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['payment' => 'Erro ao comunicar com a API de pagamentos externa.']);
        }

        // Create database records within transaction
        try {
            $order = DB::transaction(function() use ($roundedTotal, $request, $orderItemsData) {
                $order = Order::create([
                    'status' => 'pending',
                    'customer_id' => Auth::id(),
                    'date' => today()->toDateString(), // Y-m-d format without hours
                    'total_price' => $roundedTotal,
                    'notes' => $request->notes,
                    'nif' => $request->nif,
                    'address' => $request->address,
                    'payment_type' => $request->payment_type,
                    'payment_ref' => $request->payment_ref,
                ]);

                foreach ($orderItemsData as $item) {
                    $item['order_id'] = $order->id;
                    Order_item::create($item);
                }

                return $order;
            });

            // Clear Cart Session
            session()->forget('cart');

            // Send pending e-mail (Integration with G6)
            try {
                if (class_exists(\App\Mail\OrderPendingMail::class)) {
                    Mail::to($request->user())->send(new \App\Mail\OrderPendingMail($order));
                }
            } catch (\Exception $e) {
                logger()->error('Falha ao enviar e-mail de encomenda pendente: ' . $e->getMessage());
            }

            return redirect()->route('orders.show', $order)->with('success', 'Encomenda efetuada com sucesso! O pagamento foi processado.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['payment' => 'Erro ao registar a encomenda na base de dados: ' . $e->getMessage()]);
        }
    }
}

