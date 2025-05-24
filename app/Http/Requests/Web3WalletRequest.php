<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Web3WalletRequest extends FormRequest
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
            'network' => ['required', Rule::in(['ethereum', 'binance', 'aptos'])],
            'creation_method' => ['required', Rule::in(['import', 'generate'])],
            'address' => [
                'required',
                Rule::unique('web3_wallets', 'address')->ignore($this->web3Wallet),
                function ($attribute, $value, $fail) {
                    // Validate Ethereum address format if Ethereum network is selected
                    if ($this->input('network') === 'ethereum' && !preg_match('/^0x[a-fA-F0-9]{40}$/', $value)) {
                        $fail('The address must be a valid Ethereum address (0x followed by 40 hexadecimal characters).');
                    }
                    // Validate Binance Smart Chain address format (same as Ethereum)
                    if ($this->input('network') === 'binance' && !preg_match('/^0x[a-fA-F0-9]{40}$/', $value)) {
                        $fail('The address must be a valid Binance Smart Chain address (0x followed by 40 hexadecimal characters).');
                    }
                    // Validate Aptos address format if implemented
                    if ($this->input('network') === 'aptos') {
                        $fail('Aptos network is currently not supported.');
                    }
                },
            ],
            'seed_phrase' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Basic validation for BIP39 seed phrase
                    $words = explode(' ', trim($value));
                    $wordCount = count($words);
                    if (!in_array($wordCount, [12, 15, 18, 21, 24])) {
                        $fail('The seed phrase must contain 12, 15, 18, 21, or 24 words separated by spaces.');
                    }
                },
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
            'network.required' => 'Please select a blockchain network.',
            'network.in' => 'Please select a valid blockchain network.',
            'creation_method.required' => 'Please select a wallet creation method.',
            'creation_method.in' => 'Please select a valid wallet creation method.',
            'address.required' => 'Please enter a wallet address.',
            'address.unique' => 'This wallet address is already in use.',
            'seed_phrase.required' => 'Please enter the seed phrase.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up whitespace in seed phrase if provided
        if ($this->has('seed_phrase')) {
            $seedPhrase = $this->input('seed_phrase');
            // Normalize whitespace: trim excess spaces, ensure single space between words
            $normalizedSeedPhrase = preg_replace('/\s+/', ' ', trim($seedPhrase));
            $this->merge(['seed_phrase' => $normalizedSeedPhrase]);
        }

        // Lowercase the address if provided
        if ($this->has('address')) {
            $address = $this->input('address');
            $this->merge(['address' => strtolower(trim($address))]);
        }
    }
}