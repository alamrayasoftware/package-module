<?php

namespace __defaultNamespace__\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'errors' => $errors
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
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
            'type' => ['sometimes', Rule::in(['central', 'branch', 'supplier', 'customer'])],
            'ownership_type' => ['sometimes', Rule::in(['personal', 'corporate'])],
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
