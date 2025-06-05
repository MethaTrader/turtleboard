<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Proxy;

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
        $rules = [
            'provider' => ['required', Rule::in(['Gmail', 'Outlook', 'Yahoo', 'iCloud'])],
            'email_address' => [
                'required',
                'email',
                Rule::unique('email_accounts')->ignore($this->emailAccount),
            ],
            'password' => ['required', 'string', 'min:6'],
            'proxy_id' => [
                'nullable',
                'exists:proxies,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        // Check if this proxy is already assigned to another email account
                        $isAssigned = \App\Models\EmailAccount::where('proxy_id', $value)
                            ->when($this->emailAccount, function ($query) {
                                // Exclude current email account when updating
                                return $query->where('id', '!=', $this->emailAccount->id);
                            })
                            ->exists();

                        if ($isAssigned) {
                            $proxy = Proxy::find($value);
                            $assignedTo = \App\Models\EmailAccount::where('proxy_id', $value)->first();
                            $fail("This proxy ({$proxy->ip_address}:{$proxy->port}) is already assigned to {$assignedTo->email_address}");
                        }
                    }
                }
            ],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
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
            'first_name.max' => 'First name cannot exceed 255 characters.',
            'last_name.max' => 'Last name cannot exceed 255 characters.',
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

        // Handle empty proxy_id
        if ($this->proxy_id === '') {
            $this->merge(['proxy_id' => null]);
        }
    }
}