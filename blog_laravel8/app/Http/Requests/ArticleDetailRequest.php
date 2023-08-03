<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleDetailRequest extends FormRequest
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
            'title' => 'required|string|max: 255',
            'description' => 'required|string',
            'content' => 'required|string',
            'language' => 'required',
            'seo_content' => 'required',
            'seo_description' => 'required',
            'seo_title' => 'required',
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
            'language.required' => 'A language is required.',

        ];
    }
}
