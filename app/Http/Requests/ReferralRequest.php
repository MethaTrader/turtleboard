<?php
// app/Http/Requests/ReferralRequest.php

namespace App\Http\Requests;

use App\Models\MexcAccount;
use App\Models\MexcReferral;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferralRequest extends FormRequest
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
            'inviter_account_id' => [
                'required',
                'integer',
                'exists:mexc_accounts,id',
                'different:invitee_account_id',
                function ($attribute, $value, $fail) {
                    // Check if inviter has reached the limit of 5 invitations
                    if (MexcReferral::hasReachedInviteLimit($value)) {
                        $fail('This account has already reached the maximum limit of 5 invitations.');
                    }

                    // Check if the inviter account is active
                    $account = MexcAccount::find($value);
                    if (!$account || !$account->isActive()) {
                        $fail('The inviter account must be active.');
                    }
                },
            ],
            'invitee_account_id' => [
                'required',
                'integer',
                'exists:mexc_accounts,id',
                'different:inviter_account_id',
                function ($attribute, $value, $fail) {
                    // Check if invitee is already invited by someone else
                    if (MexcReferral::isAlreadyInvited($value)) {
                        $fail('This account has already been invited by another account.');
                    }

                    // Check if the invitee account is active
                    $account = MexcAccount::find($value);
                    if (!$account || !$account->isActive()) {
                        $fail('The invitee account must be active.');
                    }

                    // Check if a referral already exists between these accounts
                    $existingReferral = MexcReferral::where('inviter_account_id', $this->input('inviter_account_id'))
                        ->where('invitee_account_id', $value)
                        ->exists();

                    if ($existingReferral) {
                        $fail('A referral connection already exists between these accounts.');
                    }
                },
            ],
            'status' => [
                'sometimes',
                Rule::in([MexcReferral::STATUS_PENDING, MexcReferral::STATUS_COMPLETED, MexcReferral::STATUS_CANCELLED])
            ],
        ];

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
            'inviter_account_id.required' => 'Please select an inviter account.',
            'inviter_account_id.exists' => 'The selected inviter account does not exist.',
            'inviter_account_id.different' => 'The inviter and invitee accounts must be different.',
            'invitee_account_id.required' => 'Please select an invitee account.',
            'invitee_account_id.exists' => 'The selected invitee account does not exist.',
            'invitee_account_id.different' => 'The inviter and invitee accounts must be different.',
            'status.in' => 'Please select a valid status.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default status to pending for new referrals
        if (!$this->has('status')) {
            $this->merge(['status' => MexcReferral::STATUS_PENDING]);
        }

        // Convert string IDs to integers if they exist
        if ($this->has('inviter_account_id')) {
            $this->merge(['inviter_account_id' => (int) $this->input('inviter_account_id')]);
        }

        if ($this->has('invitee_account_id')) {
            $this->merge(['invitee_account_id' => (int) $this->input('invitee_account_id')]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'inviter_account_id' => 'inviter account',
            'invitee_account_id' => 'invitee account',
        ];
    }
}