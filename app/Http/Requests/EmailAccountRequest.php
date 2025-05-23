<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmailAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Different rules based on the step in the wizard
        $step = $this->input('step', 'provider');

        // Basic rules that apply to all steps for submitted fields
        $rules = [
            'provider' => ['required', Rule::in(['Gmail', 'Outlook', 'Yahoo', 'Rambler'])],
            'email_address' => [
                'required',
                'email',
                Rule::unique('email_accounts')->ignore($this->emailAccount),
            ],
            'password' => ['required', 'string', 'min:6'],
            'proxy_id' => ['nullable', 'exists:proxies,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
        ];

        // Return only the rules needed for the current step or all rules for final submission
        if ($step === 'provider') {
            return ['provider' => $rules['provider']];
        } elseif ($step === 'email') {
            return [
                'provider' => $rules['provider'],
                'email_address' => $rules['email_address'],
            ];
        } elseif ($step === 'password') {
            return [
                'provider' => $rules['provider'],
                'email_address' => $rules['email_address'],
                'password' => $rules['password'],
            ];
        }

        // For edit form or final submission
        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'provider.required' => 'Please select an email provider.',
            'provider.in' => 'Please select a valid email provider.',
            'email_address.required' => 'Please enter an email address.',
            'email_address.email' => 'Please enter a valid email address.',
            'email_address.unique' => 'This email address is already in use.',
            'password.required' => 'Please enter a password.',
            'password.min' => 'Password must be at least 6 characters.',
            'proxy_id.exists' => 'The selected proxy does not exist.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Please select a valid status.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default status if not provided
        if (!$this->has('status')) {
            $this->merge(['status' => 'active']);
        }
    }
}