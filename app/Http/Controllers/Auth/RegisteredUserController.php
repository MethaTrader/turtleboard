<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // First validate the SSO code before proceeding with other validations
        $this->validateSsoCode($request);

        // Then validate other registration fields
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:administrator,account_manager'],
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Set session flag for onboarding
        $request->session()->put('just_registered', true);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Validate the SSO code.
     *
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateSsoCode(Request $request): void
    {
        $request->validate([
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
        ], [
            'sso_code.required' => 'The SSO code is required.',
            'sso_code.size' => 'The SSO code must be exactly 5 characters.',
            'sso_code.regex' => 'The SSO code must contain only letters and numbers.',
        ]);
    }
}