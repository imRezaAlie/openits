<?php

namespace App\Http\Requests\Admin;

use App\Rules\SafeLdapHost;
use App\Services\SettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateLdapSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ldap_login_enabled' => ['sometimes', 'boolean'],
            'ldap_server' => ['sometimes', 'required', 'string', 'max:255', new SafeLdapHost],
            'ldap_port' => ['sometimes', 'required', 'integer', 'min:1', 'max:65535'],
            'ldap_base_dn' => ['sometimes', 'required', 'string', 'max:500'],
            'ldap_domain' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }

    /**
     * Prevent enabling LDAP login when connection settings are missing.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->boolean('ldap_login_enabled')) {
                return;
            }

            /** @var SettingsService $settings */
            $settings = app(SettingsService::class);

            $server = $this->input('ldap_server', $settings->getLdapServer());
            $baseDn = $this->input('ldap_base_dn', $settings->getLdapBaseDn());
            $domain = $this->input('ldap_domain', $settings->getLdapDomain());
            $port = (int) $this->input('ldap_port', $settings->getLdapPort());

            if (! filled($server) || ! filled($baseDn) || ! filled($domain) || $port < 1) {
                $validator->errors()->add(
                    'ldap_login_enabled',
                    __('ldap.errors.credentials_missing')
                );
            }
        });
    }
}
