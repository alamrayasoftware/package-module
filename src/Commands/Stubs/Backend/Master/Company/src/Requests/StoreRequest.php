<?php

namespace __defaultNamespace__\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|min:5',
            'company_parent_id' => 'nullable',
            'code' => 'nullable|min:5|max:15',
            'phone' => 'required|numeric|min:8|max:15',
            'email' => 'required|email:rfc,dns',
            'address' => 'required',
            'image' => 'nullable|mimes:png,jpg',
            'province_id' => 'nullable|numeric',
            'city_id' => 'nullable|numeric',
            'district_id' => 'nullable|numeric',
            'type' => ['required', Rule::in(['central', 'branch', 'supplier', 'customer'])],
            'ownership_type' => ['required', Rule::in(['personal', 'corporate'])],
            'account_first_period' => 'nullable|date_format:Y-m-d',
        ];
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            // 'email.required' => 'Email is required!',
        ];
    }
}
