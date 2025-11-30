<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validate = $this->validateTurnstile($value, config('app.recaptcha_secret_key'), request()->ip());

        if (!$validate) {
            $fail('The reCAPTCHA verification failed. Please try again.');
        }
    }


    function validateTurnstile($token, $secret, $remoteip = null)
    {
        try {
            $data = [
                'secret' => $secret,
                'response' => $token
            ];

            if ($remoteip) {
                $data['remoteip'] = $remoteip;
            }

            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', $data);

            if (!$response->successful()) {
                Log::error('Turnstile verification request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }

            $result = $response->json();

            // Log for debugging (remove in production)
            Log::info('Turnstile verification result', [
                'success' => $result['success'] ?? false,
                'error_codes' => $result['error-codes'] ?? []
            ]);

            return isset($result['success']) && $result['success'] === true;

        } catch (\Exception $e) {
            Log::error('Turnstile verification exception', [
                'error' => $e->getMessage(),
                'token_length' => strlen($token)
            ]);
            return false;
        }
    }
}