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
            'slug' => 'required',
            'status' => 'required',
            'type' => 'required',
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'A name is required',
            'slug.required' => 'A slug is required',
            'status.required' => 'A status is required',
            'type.required' => 'A type is required',

        ];
    }
}
