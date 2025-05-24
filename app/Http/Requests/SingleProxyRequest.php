<?php
// app/Http/Requests/SingleProxyRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SingleProxyRequest extends FormRequest
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
            'ip_address' => [
                'required',
                'ip',
                Rule::unique('proxies')->where(function ($query) {
                    return $query->where('port', $this->port)
                        ->where('id', '!=', $this->route('proxy')?->id ?? 0);
                }),
            ],
            'port' => 'required|integer|between:1,65535',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
        ];
    }
}