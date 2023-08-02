<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'name' => 'required',
            'status' => 'required|string',
            'type' => 'required',
            'description' => 'required',
            'url_id' => 'array',
            'url_id.*' => 'numeric',
            'post_ids' => 'array',
            'post_ids.*' => 'numeric'
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'A name is required',
            'slug.required' => 'A slug is required',
            'status.required' => 'A status is required',
            'type.required' => 'A type is required',
            'url_id.array' => 'A url_id must be in array format',
            'post_ids.array' => 'A post_ids must be in array format',
            'post_ids.*.numeric' => 'A post_ids must be in number format',
            'url_id.*.numeric' => 'A post_ids must be in number format',
        ];
    }
}
