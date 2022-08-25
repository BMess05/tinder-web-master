<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyPlanRequest extends FormRequest
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
            'name' => 'required|unique:company_subscription_plans,name',
            'cost' => 'required|numeric',
            'interval' => 'required|in:month,year', // day, week, month, year
            'description' => 'nullable|min:5|max:150'
        ];
    }
}
