<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tshirt_image;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TshirtImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $query = Tshirt_image::query()->whereNull('customer_id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $tshirtImages = $query->orderBy('id', 'desc')->paginate(10);
        $categories = Category::pluck('name', 'id')->toArray();

        return view('admin.tshirt_images.index', compact('tshirtImages', 'categories', 'search', 'categoryId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::pluck('name', 'id')->toArray();
        return view('admin.tshirt_images.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'image_file' => 'required|image|max:2048' // Obrigatório no store
        ]);

        $tshirtImage = new Tshirt_image();
        $tshirtImage->name = $request->name;
        $tshirtImage->description = $request->description;
        $tshirtImage->category_id = $request->category_id;
        $tshirtImage->customer_id = null; // Do catálogo

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('tshirt_images', 'public');
            $tshirtImage->image_url = basename($path);
        }

        $tshirtImage->save();

        return redirect()->route('admin.tshirt-images.index')
            ->with('success', 'Estampa de T-Shirt adicionada com sucesso.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tshirt_image $tshirtImage)
    {
        // Se for uma estampa própria de cliente, bloqueamos acesso no admin
        if ($tshirtImage->customer_id !== null) {
            abort(403, 'Apenas estampas do catálogo podem ser geridas nesta área.');
        }

        $categories = Category::pluck('name', 'id')->toArray();
        return view('admin.tshirt_images.edit', compact('tshirtImage', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tshirt_image $tshirtImage)
    {
        if ($tshirtImage->customer_id !== null) {
            abort(403, 'Apenas estampas do catálogo podem ser geridas nesta área.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'image_file' => 'nullable|image|max:2048'
        ]);

        $tshirtImage->name = $request->name;
        $tshirtImage->description = $request->description;
        $tshirtImage->category_id = $request->category_id;

        if ($request->hasFile('image_file')) {
            // Remover imagem antiga do storage
            if ($tshirtImage->image_url && Storage::disk('public')->exists('tshirt_images/' . $tshirtImage->image_url)) {
                Storage::disk('public')->delete('tshirt_images/' . $tshirtImage->image_url);
            }

            $path = $request->file('image_file')->store('tshirt_images', 'public');
            $tshirtImage->image_url = basename($path);
        }

        $tshirtImage->save();

        return redirect()->route('admin.tshirt-images.index')
            ->with('success', 'Estampa de T-Shirt atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tshirt_image $tshirtImage)
    {
        if ($tshirtImage->customer_id !== null) {
            abort(403, 'Apenas estampas do catálogo podem ser removidas nesta área.');
        }

        $tshirtImage->delete(); // Soft delete

        return redirect()->route('admin.tshirt-images.index')
            ->with('success', 'Estampa de T-Shirt removida com sucesso (Soft Delete).');
    }
}
