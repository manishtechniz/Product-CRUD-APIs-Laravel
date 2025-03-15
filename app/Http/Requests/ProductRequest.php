<?php

namespace App\Http\Requests;

use App\Rules\ImageOrId;
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
            $rules['name'] = 'nullable|max:50|unique:products,name,'. request('id');
            $rules['id'] = 'required|integer|exists:products,id';
            $rules['description'] = 'nullable|string|min:50|max:500';
            $rules['price'] = 'nullable|numeric';
            $rules['discount'] = 'nullable|numeric';
            $rules['stock'] = 'nullable|integer';
            $rules['status'] = 'nullable|in:0,1';
            $rules['images'] = 'nullable|array';
            $rules['images.*'] = ['required', new ImageOrId];
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => request('id'),
        ]);
    }
}
