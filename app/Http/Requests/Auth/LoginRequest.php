<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
        return [
            'sso_code' => [
                'required',
                'string',
                'size:5',
                'regex:/^[A-Za-z0-9]{5}$/',
                function ($attribute, $value, $fail) {
                    $expectedCode = config('auth.sso_code');
                    if (empty($expectedCode)) {
                        $fail('SSO protection is not properly configured.');
                        return;
                    }

                    if (strtoupper($value) !== strtoupper($expectedCode)) {
                        $fail('The SSO code is incorrect.');
                    }
                },
            ],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sso_code.required' => 'The SSO code is required.',
            'sso_code.size' => 'The SSO code must be exactly 5 characters.',
            'sso_code.regex' => 'The SSO code must contain only letters and numbers.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // First validate the SSO code
        $this->validateSsoCode();

        // Then attempt authentication with email and password
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Validate the SSO code before proceeding with authentication.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateSsoCode(): void
    {
        $ssoCode = $this->input('sso_code');
        $expectedCode = config('auth.sso_code');

        if (empty($expectedCode)) {
            throw ValidationException::withMessages([
                'sso_code' => 'SSO protection is not properly configured.',
            ]);
        }

        if (empty($ssoCode) || strtoupper($ssoCode) !== strtoupper($expectedCode)) {
            throw ValidationException::withMessages([
                'sso_code' => 'The SSO code is incorrect.',
            ]);
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}