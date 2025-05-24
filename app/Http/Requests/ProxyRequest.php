<?php
// app/Http/Requests/ProxyRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProxyRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'proxy_list' => 'required_without:proxy_file',
            'proxy_file' => 'required_without:proxy_list|nullable|file|mimes:txt',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proxy_list.required_without' => 'Please enter proxy list or upload a file.',
            'proxy_file.required_without' => 'Please enter proxy list or upload a file.',
            'proxy_file.mimes' => 'The proxy file must be a text file (.txt).',
        ];
    }
}