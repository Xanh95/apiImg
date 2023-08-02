<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReversionArticleRequest extends FormRequest
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
            'title' => 'required',
            'description' => 'required',
            'content' => 'required',
            'seo_content' => 'required',
            'seo_description' => 'required',
            'seo_title' => 'required',
            'type' => 'required',
            'category_ids' => 'required|array',
            'category_ids.*' => 'numeric',
            'url_id' => 'array',
            'url_id.*' => 'numeric',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The title field is required.',
            'description.required' => 'The description field is required.',
            'content.required' => 'The content field is required.',
            'seo_content.required' => 'The SEO content field is required.',
            'seo_description.required' => 'The SEO description field is required.',
            'seo_title.required' => 'The SEO title field is required.',
            'type.required' => 'The type field is required.',
            'category_ids.required' => 'The category_ids field is required.',
            'category_ids.array' => 'The category_ids must be an array.',
            'category_ids.*.numeric' => 'The values in the category_ids array must be integers.',
            'url_id.array' => 'The url_id must be an array.',
            'url_id.*.numeric' => 'The values in the url_id array must be integers.',
        ];
    }
}
