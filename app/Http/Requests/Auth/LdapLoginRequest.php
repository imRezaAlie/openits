<?php

namespace App\Http\Requests\Auth;

use App\Services\SettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LdapLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var SettingsService $settings */
        $settings = app(SettingsService::class);
        $allowedDomains = $settings->getAvailableLdapDomains();

        $domainRules = ['nullable', 'string', 'max:255'];

        if ($allowedDomains !== []) {
            $domainRules[] = Rule::in($allowedDomains);
        }

        return [
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'domain' => $domainRules,
        ];
    }
}
