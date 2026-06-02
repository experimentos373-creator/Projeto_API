<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartFromRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        if ($this->isMethod('patch') || $this->isMethod('put')) {
            return [
                'quantity' => 'required|integer|min:0',
                'color'    => 'nullable|exists:colors,code',
                'size'     => 'nullable|in:XS,S,M,L,XL',
                'custom_top' => 'nullable|numeric',
                'custom_left' => 'nullable|numeric',
                'custom_scale' => 'nullable|numeric',
                'custom_rotate' => 'nullable|integer',
                'custom_opacity' => 'nullable|numeric',
            ];
        }

        return [
            'color'    => 'required|exists:colors,code',
            'size'     => 'required|in:XS,S,M,L,XL',
            'quantity' => 'required|integer|min:1',
            'custom_top' => 'nullable|numeric',
            'custom_left' => 'nullable|numeric',
            'custom_scale' => 'nullable|numeric',
            'custom_rotate' => 'nullable|integer',
            'custom_opacity' => 'nullable|numeric',
        ];
    }
}
