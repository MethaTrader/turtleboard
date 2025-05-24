<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MexcAccountRequest extends FormRequest
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
        $rules = [
            'email_account_id' => [
                'required',
                'exists:email_accounts,id',
                Rule::unique('mexc_accounts', 'email_account_id')->ignore($this->mexcAccount),
            ],
            'password' => ['required', 'string', 'min:6'],
            'web3_wallet_id' => ['nullable', 'exists:web3_wallets,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
        ];

        // For update requests, make password optional if not provided
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['password'] = ['nullable', 'string', 'min:6'];
        }

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
            'email_account_id.required' => 'Please select an email account.',
            'email_account_id.exists' => 'The selected email account does not exist.',
            'email_account_id.unique' => 'This email account is already linked to another MEXC account.',
            'password.required' => 'Please enter a password.',
            'password.min' => 'Password must be at least 6 characters.',
            'web3_wallet_id.exists' => 'The selected Web3 wallet does not exist.',
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

        // Handle empty web3_wallet_id
        if ($this->web3_wallet_id === '') {
            $this->merge(['web3_wallet_id' => null]);
        }
    }
}