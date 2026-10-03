<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Marker extends FormRequest
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
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'owner_id' => 'required|integer',
            'title' => 'required|string|between:3,64',
            'description' => 'required|string|between:3,1024'
        ];
    }
}
