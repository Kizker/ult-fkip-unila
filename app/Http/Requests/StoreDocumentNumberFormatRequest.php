<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentNumberFormatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('document_numbers.manage_formats') ?? false;
    }

    public function rules(): array
    {
        return [
            'unit_id' => ['required','integer','exists:units,id'],
            'format_key' => ['required','string','max:120','regex:/^[a-z0-9_-]+$/i'],
            'name' => ['required','string','max:160'],
            'template' => ['required','string','max:2000'],
            'seq_padding' => ['nullable','integer','min:1','max:10'],
            'is_active' => ['nullable','boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'format_key.regex' => 'Format key hanya boleh berisi huruf/angka/underscore/dash.',
        ];
    }
}
