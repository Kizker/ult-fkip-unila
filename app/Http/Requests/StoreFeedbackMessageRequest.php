<?php

namespace App\Http\Requests;

use App\Models\FeedbackMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class StoreFeedbackMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'category' => ['required', Rule::in(FeedbackMessage::CATEGORIES)],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[0-9+\\-().\\s]{8,20}$/'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
            // Honeypot field, must stay empty.
            'website' => ['nullable', 'max:0'],
            // reCAPTCHA token (only required when feature is enabled).
            'g-recaptcha-response' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Format nomor HP tidak valid.',
            'message.min' => 'Pesan minimal 20 karakter.',
            'message.max' => 'Pesan maksimal 2000 karakter.',
            'website.max' => 'Validasi anti-spam gagal.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $message = $this->cleanText((string) $this->input('message', ''), true);
        $phone = $this->cleanText((string) $this->input('phone', ''));
        $name = $this->cleanText((string) $this->input('name', ''));
        $email = strtolower($this->cleanText((string) $this->input('email', '')));
        $category = strtoupper($this->cleanText((string) $this->input('category', '')));

        $this->merge([
            'name' => $name,
            'email' => $email,
            'category' => $category,
            'phone' => $phone !== '' ? $phone : null,
            'message' => $message,
            'website' => $this->cleanText((string) $this->input('website', '')),
            'g-recaptcha-response' => trim((string) $this->input('g-recaptcha-response', '')),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (!(bool) config('services.recaptcha.enabled', false)) {
                return;
            }

            $secret = trim((string) config('services.recaptcha.secret', ''));
            $token = trim((string) $this->input('g-recaptcha-response', ''));

            if ($secret === '') {
                $validator->errors()->add('g-recaptcha-response', 'Konfigurasi reCAPTCHA belum lengkap.');
                return;
            }

            if ($token === '') {
                $validator->errors()->add('g-recaptcha-response', 'Verifikasi reCAPTCHA wajib diisi.');

                return;
            }

            try {
                $response = Http::asForm()
                    ->timeout(8)
                    ->retry(1, 200)
                    ->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret' => $secret,
                        'response' => $token,
                        'remoteip' => $this->ip(),
                    ]);

                $isValid = $response->ok() && (bool) $response->json('success', false);
                if (!$isValid) {
                    $validator->errors()->add('g-recaptcha-response', 'Verifikasi reCAPTCHA gagal.');
                }
            } catch (\Throwable $e) {
                $validator->errors()->add('g-recaptcha-response', 'Verifikasi reCAPTCHA sedang bermasalah. Silakan coba lagi.');
            }
        });
    }

    private function cleanText(string $value, bool $preserveLineBreaks = false): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $value);
        if ($preserveLineBreaks) {
            $normalized = preg_replace('/[ \t]+/u', ' ', $normalized) ?? $normalized;
            $normalized = preg_replace('/\n{3,}/u', "\n\n", $normalized) ?? $normalized;

            return trim(strip_tags($normalized));
        }

        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
        return trim(strip_tags($normalized));
    }
}
