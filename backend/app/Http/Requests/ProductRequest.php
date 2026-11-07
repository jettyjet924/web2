<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);

        return [
            'name' => $isUpdate ? 'sometimes|required|string|max:200' : 'required|string|max:200',
            'slug' => 'nullable|string|max:220',
            'sku' => $isUpdate ? 'sometimes|required|string|max:100' : 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'stock' => $isUpdate ? 'sometimes|required|integer|min:0' : 'required|integer|min:0',
            'category_id' => $isUpdate ? 'sometimes|required|string' : 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'string|url',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Handle a failed validation attempt for API requests.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422));
    }
}

