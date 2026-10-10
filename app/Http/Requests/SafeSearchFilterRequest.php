<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SafeSearchFilterRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Dapatkan aturan validasi untuk filtering dan sorting umum.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Batasi panjang pencarian maksimal 100 karakter untuk mencegah DoS
            'search' => ['nullable', 'string', 'max:100'],

            // Validasi arah pengurutan
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc', 'ASC', 'DESC'])],

            // Batasi nilai pagination agar tidak membebani memori server
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],

            // Format tanggal yang valid jika melakukan filtering rentang waktu
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * Sanitasi input sebelum validasi dijalankan.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('search')) {
            $this->merge([
                'search' => trim(strip_tags((string) $this->search)),
            ]);
        }

        if ($this->has('sort_direction')) {
            $this->merge([
                'sort_direction' => strtolower((string) $this->sort_direction),
            ]);
        }
    }
}
