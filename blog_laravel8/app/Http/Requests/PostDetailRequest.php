<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostDetailRequest extends FormRequest
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

            'name' => 'required|string|max:255',
            'description' => 'string',
            'language' => 'required|string',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'description.required' => 'The description field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name must not exceed 255 characters.',
            'description.string' => 'The description must be a string.',
            'language.required' => 'The language field is required.',
            'language.string' => 'The language must be a string.',
        ];
    }
}
