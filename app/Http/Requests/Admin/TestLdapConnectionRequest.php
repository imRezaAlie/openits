<?php

namespace App\Http\Requests\Admin;

use App\Rules\SafeLdapHost;
use Illuminate\Foundation\Http\FormRequest;

class TestLdapConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ldap_server' => ['sometimes', 'required', 'string', 'max:255', new SafeLdapHost],
            'ldap_port' => ['sometimes', 'required', 'integer', 'min:1', 'max:65535'],
            'ldap_base_dn' => ['sometimes', 'required', 'string', 'max:500'],
            'ldap_domain' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
}
