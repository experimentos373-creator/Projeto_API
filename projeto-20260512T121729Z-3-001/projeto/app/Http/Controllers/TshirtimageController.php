<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\Price;
use App\Models\Tshirt_image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TshirtimageController extends Controller
{
    public function shop(Request $request)
    {
        $filterByCategory = $request->input('category');
        $filterByName = $request->input('name');

        $categories = Category::pluck('name', 'id')->toArray();

        $tshirt_images_Query = Tshirt_image::query()->whereNull('customer_id');

        if ($filterByCategory) {
            $tshirt_images_Query->where('category_id', $filterByCategory);
        }

        if ($filterByName) {
            $tshirt_images_Query->where(function ($query) use ($filterByName) {
                $query->where('name', 'like', '%'.$filterByName.'%')
                    ->orWhere('description', 'like', '%'.$filterByName.'%');
            });
        }

        $tshirt_images = $tshirt_images_Query->with('category')->paginate(8);
        $price = Price::first();

        return view('tshirt_images.shop', compact(
            'tshirt_images',
            'price',
            'filterByCategory',
            'filterByName',
            'categories'
        ));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Tshirt_image $tshirtImage)
    {
        // Verificar se a imagem é pública ou do próprio utilizador
        if ($tshirtImage->customer_id && $tshirtImage->customer_id !== Auth::id()) {
            abort(403, 'Acesso negado.');
        }

        $colors = Color::all();
        $price = Price::first();

        // Tamanhos padrão (conforme enunciado)
        $sizes = ['XS', 'S', 'M', 'L', 'XL'];

        return view('tshirt_images.show', compact('tshirtImage', 'colors', 'price', 'sizes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Price $price)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Price $price)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Price $price)
    {
        //
    }
}
