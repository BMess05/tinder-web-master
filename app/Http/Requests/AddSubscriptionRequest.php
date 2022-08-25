<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddSubscriptionRequest extends FormRequest
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
            'credit_card_number' => 'required|numeric',
            'cvv' => 'required|numeric',
            'exp_month' => 'required|integer',
            'exp_year' => 'required|integer',
        ];
    }
}
