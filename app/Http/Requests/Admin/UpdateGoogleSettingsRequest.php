<?php

namespace App\Http\Requests\Admin;

use App\Services\SettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateGoogleSettingsRequest extends FormRequest
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
            'google_login_enabled' => ['required', 'boolean'],
        ];
    }

    /**
     * Prevent enabling Google login when credentials are missing.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->boolean('google_login_enabled')) {
                return;
            }

            /** @var SettingsService $settings */
            $settings = app(SettingsService::class);

            if (! $settings->googleCredentialsConfigured()) {
                $validator->errors()->add(
                    'google_login_enabled',
                    __('google.errors.credentials_missing')
                );
            }
        });
    }
}
