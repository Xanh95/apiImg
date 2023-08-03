<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopPageRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'area' => 'required|regex:/^[a-zA-Z0-9]+\/[a-zA-Z0-9]+$/',
            'about' => 'required|string|max:200',
            'summary' => 'required|string|max:1000',
            'name' => 'required',
            'facebook' => 'url|starts_with:https://www.facebook.com/',
            'instagram' => 'url|starts_with:https://www.instagram.com/',
            'website' => 'url',
            'status' => 'required|in:published,unpublished'
        ];
    }
    public function messages()
    {
        return [
            'area.required' => 'The area field is required.',
            'area.regex' => 'The area format is district/city',
            'about.required' => 'The about field is required.',
            'about.string' => 'The about field must be a string.',
            'about.max' => 'The about field must not exceed 200 characters.',
            'summary.required' => 'The summary field is required.',
            'summary.string' => 'The summary field must be a string.',
            'summary.max' => 'The summary field must not exceed 1000 characters.',
            'name.required' => 'The name field is required.',
            'facebook.url' => 'The Facebook URL format is invalid. It should start with "https://www.facebook.com/".',
            'instagram.url' => 'The Instagram URL format is invalid. It should start with "https://www.instagram.com/".',
            'website.url' => 'The website URL format is invalid.',
            'status.in' => 'The selected status is invalid. It should be either "published" or "unpublished".',
        ];
    }
}
