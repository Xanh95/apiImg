<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
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
            'title' => 'required',
            'type' => 'required',
            'description' => 'required',
            'content' => 'required',
            'seo_title' => 'required',
            'seo_description' => 'required',
            'seo_content' => 'required',
            'category_id' => 'required|array',
        ];
    }
    public function messages(): array
    {
        return [
            'title.required' => 'A title is required',
            'type.required' => 'A type is required',
            'description.required' => 'A status is required',
            'content.required' => 'A content is required',
            'seo_title.required' => 'A seo_title is required',
            'seo_description.required' => 'A seo_description is required',
            'seo_content.required' => 'A seo_content is required',
            'category_id.required' => 'A category_id is required',
            'category_id.array' => 'A category_id is array',
        ];
    }
}
