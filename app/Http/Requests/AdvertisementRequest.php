<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdvertisementRequest extends FormRequest
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
        if($this->id) {
            return [
                'title' => 'required|max:30',
                'description' => 'required|max:1500',
                'url' => 'required|regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
                'image' => 'sometimes|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'preferred_location' => 'required',
                'diameter' => 'required|numeric|min:1',
                'age_from' => 'required|integer|min:0',
                'age_to' => 'required|integer|min:0',
                'gender_group' => 'required|array|min:1',
                'gender_group.*' => 'in:1,2,3'
            ];
        }   else {
            return [
                'title' => 'required|max:30',
                'description' => 'required|max:1500',
                'url' => 'required|regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
                'image' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'preferred_location' => 'required',
                'diameter' => 'required|numeric|min:1',
                'age_from' => 'required|integer|min:0',
                'age_to' => 'required|integer|min:0',
                'gender_group' => 'required|array|min:1',
                'gender_group.*' => 'in:1,2,3'
            ];
        }
    }
}
