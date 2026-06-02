<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $query = Category::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = $query->orderBy('id', 'desc')->paginate(10);

        return view('admin.categories.index', compact('categories', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image_file' => 'nullable|image|max:2048' // max 2MB
        ]);

        $category = new Category();
        $category->name = $request->name;

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('categories', 'public');
            $category->image_url = basename($path);
        }

        $category->save();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoria criada com sucesso.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image_file' => 'nullable|image|max:2048'
        ]);

        $category->name = $request->name;

        if ($request->hasFile('image_file')) {
            // Remover imagem antiga do storage se existir
            if ($category->image_url && Storage::disk('public')->exists('categories/' . $category->image_url)) {
                Storage::disk('public')->delete('categories/' . $category->image_url);
            }

            $path = $request->file('image_file')->store('categories', 'public');
            $category->image_url = basename($path);
        }

        $category->save();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Se a categoria tiver imagem, não removemos o ficheiro físico de imediato no soft delete,
        // uma vez que a categoria pode ainda ser vista noutros locais (histórico) ou se mantivermos integridade.
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoria removida com sucesso (Soft Delete).');
    }
}
