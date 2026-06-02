<?php

namespace App\Http\Controllers;

use App\Models\Price;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    /**
     * Show the form for editing the global price configuration.
     */
    public function edit()
    {
        $price = Price::first();

        // Se por algum motivo não houver dados, cria o registo com valores padrão
        if (!$price) {
            $price = Price::create([
                'unit_price_catalog' => 10.00,
                'unit_price_own' => 12.00,
                'unit_price_catalog_discount' => 8.00,
                'unit_price_own_discount' => 10.00,
                'qty_discount' => 5
            ]);
        }

        return view('prices.edit', compact('price'));
    }

    /**
     * Update the global price configuration.
     */
    public function update(Request $request)
    {
        $request->validate([
            'unit_price_catalog' => 'required|numeric|min:0',
            'unit_price_own' => 'required|numeric|min:0',
            'unit_price_catalog_discount' => 'required|numeric|min:0',
            'unit_price_own_discount' => 'required|numeric|min:0',
            'qty_discount' => 'required|integer|min:1'
        ]);

        $price = Price::first();

        if (!$price) {
            $price = new Price();
        }

        $price->fill([
            'unit_price_catalog' => $request->unit_price_catalog,
            'unit_price_own' => $request->unit_price_own,
            'unit_price_catalog_discount' => $request->unit_price_catalog_discount,
            'unit_price_own_discount' => $request->unit_price_own_discount,
            'qty_discount' => $request->qty_discount
        ]);

        $price->save();

        return redirect()->route('admin.prices.edit')
            ->with('success', 'Configurações de preços atualizadas com sucesso.');
    }
}
