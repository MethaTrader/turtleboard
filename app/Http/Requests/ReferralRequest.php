<?php

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
                'exists:mexc_accounts,id',
                function ($attribute, $value, $fail) {
                    // Skip this validation for updates to avoid self-referencing issues
                    if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                        return;
                    }

                    // Check if inviter has reached the limit of 5 invitations
                    if (MexcReferral::hasReachedInviteLimit($value)) {
                        $fail('This account has already reached the maximum limit of 5 invitations.');
                    }
                },
            ],
            'invitee_account_id' => [
                'required',
                'exists:mexc_accounts,id',
                'different:inviter_account_id',
                function ($attribute, $value, $fail) {
                    // Skip this validation for updates
                    if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                        return;
                    }

                    // Check if invitee is already invited by someone else
                    $isAlreadyInvited = MexcReferral::where('invitee_account_id', $value)->exists();
                    if ($isAlreadyInvited) {
                        $fail('This account has already been invited by another account.');
                    }
                },
            ],
            'status' => ['required', Rule::in(['pending', 'completed', 'failed'])],
            'inviter_rewarded' => ['boolean'],
            'invitee_rewarded' => ['boolean'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'deposit_date' => ['nullable', 'date'],
            'withdrawal_date' => ['nullable', 'date', 'after_or_equal:deposit_date'],
            'promotion_period' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
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
            'invitee_account_id.required' => 'Please select an invitee account.',
            'invitee_account_id.exists' => 'The selected invitee account does not exist.',
            'invitee_account_id.different' => 'The inviter and invitee accounts must be different.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Please select a valid status.',
            'deposit_amount.numeric' => 'Deposit amount must be a number.',
            'deposit_amount.min' => 'Deposit amount must be at least 0.',
            'deposit_date.date' => 'Deposit date must be a valid date.',
            'withdrawal_date.date' => 'Withdrawal date must be a valid date.',
            'withdrawal_date.after_or_equal' => 'Withdrawal date must be after or equal to deposit date.',
            'promotion_period.required' => 'Please select a promotion period.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'inviter_rewarded' => $this->has('inviter_rewarded') ? true : false,
            'invitee_rewarded' => $this->has('invitee_rewarded') ? true : false,

            // Set default status to pending for new referrals
            'status' => $this->isMethod('POST') ? 'pending' : $this->get('status', 'pending'),

            // Set default promotion period to current period for new referrals
            'promotion_period' => $this->get('promotion_period', MexcReferral::getCurrentPromotionPeriod()),
        ]);
    }
}