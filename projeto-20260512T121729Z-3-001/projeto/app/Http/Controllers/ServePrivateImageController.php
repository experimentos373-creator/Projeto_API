<?php

namespace App\Http\Controllers;

use App\Models\Tshirt_image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServePrivateImageController extends Controller
{
    /**
     * Serve a private tshirt image file securely.
     *
     * Access rules:
     *  - Customer: only the owner (customer_id == auth()->id())
     *  - Admin / Employee: allowed (for order context rendering)
     */
    public function show(string $filename)
    {
        $path = 'tshirt_images_private/' . $filename;

        if (!Storage::disk('private')->exists($path)) {
            abort(404, 'Imagem não encontrada.');
        }

        $user = Auth::user();

        // Find the tshirt_image record by filename
        $image = Tshirt_image::withTrashed()
            ->where('image_url', $filename)
            ->whereNotNull('customer_id')
            ->first();

        if (!$image) {
            abort(404, 'Imagem não encontrada.');
        }

        // Authorization check
        if ($user->user_type === 'C' && $image->customer_id !== $user->id) {
            abort(403, 'Acesso negado.');
        }

        // Admin (A) and Employee (F) can access for order rendering context

        $fullPath = Storage::disk('private')->path($path);
        $mimeType = mime_content_type($fullPath);

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
