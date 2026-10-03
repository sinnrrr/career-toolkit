<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Comment extends FormRequest
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
           'owner_id' => 'required|integer',
           'marker_id' => 'required|integer',
           'body' => 'required|string|between:3,255|unique:comments,body,owner_id,marker_id'
        ];
    }
}
