<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerTshirtImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'C';
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image'       => ($isUpdate ? 'nullable' : 'required') . '|image|mimes:jpg,jpeg,png,webp|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'O nome da imagem é obrigatório.',
            'name.max'       => 'O nome não pode exceder 255 caracteres.',
            'image.required' => 'É necessário fazer upload de uma imagem.',
            'image.image'    => 'O ficheiro deve ser uma imagem válida.',
            'image.mimes'    => 'Formatos aceites: JPG, JPEG, PNG, WEBP.',
            'image.max'      => 'A imagem não pode exceder 4 MB.',
        ];
    }
}
