<?php

namespace App\Http\Requests;

use App\Models\FeedbackMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeedbackMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('feedbacks.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(FeedbackMessage::STATUSES)],
            'admin_reply' => ['nullable', 'string', 'max:4000'],
        ];
    }

    public function messages(): array
    {
        return [
            'admin_reply.max' => 'Balasan admin maksimal 4000 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $status = strtoupper(trim((string) $this->input('status', '')));
        $reply = $this->cleanText((string) $this->input('admin_reply', ''), true);

        $this->merge([
            'status' => $status,
            'admin_reply' => $reply !== '' ? $reply : null,
        ]);
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
