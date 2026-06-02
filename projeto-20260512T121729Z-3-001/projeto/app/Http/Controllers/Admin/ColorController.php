<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Color::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
        }

        $colors = $query->paginate(15);

        return view('admin.colors.index', compact('colors', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.colors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Limpar o código da cor no request antes de validar
        if ($request->has('code')) {
            $cleanedCode = strtolower(str_replace('#', '', $request->code));
            $request->merge(['code' => $cleanedCode]);
        }

        $request->validate([
            'code' => 'required|string|max:50|unique:colors,code',
            'name' => 'required|string|max:255',
            'tshirt_base_file' => 'required|image|max:2048' // Obrigatório na criação
        ]);

        $color = new Color();
        $color->code = $request->code;
        $color->name = $request->name;

        if ($request->hasFile('tshirt_base_file')) {
            $file = $request->file('tshirt_base_file');
            $extension = $file->getClientOriginalExtension();
            $filename = $color->code . '.' . $extension;

            // Guarda na pasta public/tshirt_base
            $file->storeAs('tshirt_base', $filename, 'public');
        }

        $color->save();

        return redirect()->route('admin.colors.index')
            ->with('success', 'Cor criada com sucesso.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Color $color)
    {
        return view('admin.colors.edit', compact('color'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Color $color)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tshirt_base_file' => 'nullable|image|max:2048'
        ]);

        $color->name = $request->name;

        if ($request->hasFile('tshirt_base_file')) {
            // Remover ficheiros antigos com qualquer extensão (png, jpg, jpeg) para a cor em questão
            foreach (['png', 'jpg', 'jpeg'] as $ext) {
                $oldFile = 'tshirt_base/' . $color->code . '.' . $ext;
                if (Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            $file = $request->file('tshirt_base_file');
            $extension = $file->getClientOriginalExtension();
            $filename = $color->code . '.' . $extension;

            // Guarda na pasta public/tshirt_base
            $file->storeAs('tshirt_base', $filename, 'public');
        }

        $color->save();

        return redirect()->route('admin.colors.index')
            ->with('success', 'Cor atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Color $color)
    {
        $color->delete(); // Soft delete

        return redirect()->route('admin.colors.index')
            ->with('success', 'Cor removida com sucesso (Soft Delete).');
    }
}
