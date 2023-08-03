<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
            'status' => 'required|in:active,inactive',
            'type' => 'required',
            'description' => 'required',
            'post_metas.image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
            'category_ids' => 'required|array',
            'category_ids.*' => 'numeric',
            'url_ids' => 'array',
            'url_ids.*' => 'numeric',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The status must be either "active" or "inactive".',
            'type.required' => 'The type field is required.',
            'description.required' => 'The description field is required.',
            'post_metas.image.image' => 'The image must be a valid image (png, jpg, jpeg, or svg).',
            'post_metas.image.mimes' => 'The image must have a valid format: png, jpg, jpeg, or svg.',
            'post_metas.image.max' => 'The image size must not exceed 10MB.',
            'category_id.required' => 'The category_id field is required.',
            'category_id.array' => 'The category_id must be an array.',
            'category_id.*.numeric' => 'The values in the category_id array must be integers.',
            'url_id.array' => 'The url_id must be an array.',
            'url_id.*.numeric' => 'The values in the url_id array must be integers.',
        ];
    }
}
