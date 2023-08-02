<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
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
            'ids' => 'required',
            'type' => 'required|in:delete,force_delete',
        ];
    }
    public function messages(): array
    {
        return [
            'ids.required' => 'A ids is required',
            'type.required' => 'A type is required',
            'type.in' => 'A type must be deleted or force-deleted',

        ];
    }
}
