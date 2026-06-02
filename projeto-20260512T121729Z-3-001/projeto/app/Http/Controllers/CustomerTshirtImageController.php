<?php

namespace App\Http\Controllers;

use App\Models\Tshirt_image;
use App\Models\Color;
use App\Models\Price;
use App\Http\Requests\StoreCustomerTshirtImageRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class CustomerTshirtImageController extends Controller
{
    /**
     * List all private images belonging to the authenticated customer.
     */
    public function index()
    {
        $images = Tshirt_image::where('customer_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('customer.tshirt_images.index', compact('images'));
    }

    /**
     * Show the create form.
     */
    public function create()
    {
        return view('customer.tshirt_images.create');
    }

    /**
     * Store a new private image. Forces category_id = null.
     */
    public function store(StoreCustomerTshirtImageRequest $request)
    {
        $validated = $request->validated();

        // Upload to private disk
        $path = $request->file('image')->store('tshirt_images_private', 'private');
        $filename = basename($path);

        Tshirt_image::create([
            'customer_id' => Auth::id(),
            'category_id' => null, // G5: always null for private images
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image_url'   => $filename,
        ]);

        return redirect()
            ->route('customer.tshirt-images.index')
            ->with('success', 'Imagem adicionada com sucesso!');
    }

    /**
     * Show image detail with interactive editor + add-to-cart.
     */
    public function show(Tshirt_image $tshirt_image)
    {
        Gate::authorize('view', $tshirt_image);

        $colors = Color::all();
        $price  = Price::first();
        $sizes  = ['XS', 'S', 'M', 'L', 'XL'];

        return view('customer.tshirt_images.show', [
            'tshirtImage' => $tshirt_image,
            'colors'      => $colors,
            'price'       => $price,
            'sizes'       => $sizes,
        ]);
    }

    /**
     * Show the edit form.
     */
    public function edit(Tshirt_image $tshirt_image)
    {
        Gate::authorize('update', $tshirt_image);

        return view('customer.tshirt_images.edit', [
            'tshirtImage' => $tshirt_image,
        ]);
    }

    /**
     * Update the private image. Forces category_id = null.
     */
    public function update(StoreCustomerTshirtImageRequest $request, Tshirt_image $tshirt_image)
    {
        Gate::authorize('update', $tshirt_image);

        $validated = $request->validated();

        $data = [
            'category_id' => null, // G5: always null for private images
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];

        // Replace image file if a new one was uploaded
        if ($request->hasFile('image')) {
            // Delete old file
            Storage::disk('private')->delete('tshirt_images_private/' . $tshirt_image->image_url);

            // Store new file
            $path = $request->file('image')->store('tshirt_images_private', 'private');
            $data['image_url'] = basename($path);
        }

        $tshirt_image->update($data);

        return redirect()
            ->route('customer.tshirt-images.index')
            ->with('success', 'Imagem atualizada com sucesso!');
    }

    /**
     * Soft delete the private image. Always uses SoftDeletes trait.
     */
    public function destroy(Tshirt_image $tshirt_image)
    {
        Gate::authorize('delete', $tshirt_image);

        $tshirt_image->delete(); // Eloquent SoftDeletes — sets deleted_at

        return redirect()
            ->route('customer.tshirt-images.index')
            ->with('success', 'Imagem removida com sucesso.');
    }
}
