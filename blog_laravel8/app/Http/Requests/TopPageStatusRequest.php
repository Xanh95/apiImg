<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopPageStatusRequest extends FormRequest
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
            'status' => 'required|in:published,unpublished',
        ];
    }
    public function messages()
    {
        return [
            'status.in' => 'It should be either "published" or "unpublished"',
            'status.required' => 'A status is required'
        ];
    }
}
