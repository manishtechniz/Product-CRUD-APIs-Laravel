<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name'        => 'required|max:50|unique:products,name',
            'description' => 'required|string|min:50|max:500',
            'price'       => 'required|numeric',
            'discount'    => 'required|numeric',
            'stock'       => 'required|integer',
            'status'      => 'required|in:0,1',
            'images'      => 'required|array|min:1',
            'images.*'    => 'required|image|mimes:png,jpeg,jpg|max:2048',
        ];

        if (! empty(request('id'))) {
            $rules['name'] = 'required|max:50|unique:products,name,'. request('id');
            $rules['images'] = 'nullable|array';
            $rules['images.*'] = 'nullable|image|mimes:png,jpeg,jpg|size:2048';
        }

        return $rules;
    }
}
