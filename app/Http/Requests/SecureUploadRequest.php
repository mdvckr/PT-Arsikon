<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use App\Rules\NoExecutableCode;

class SecureUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // Validasi standar untuk field 'photo' / 'image' (Maksimal 2MB)
            'photo' => [
                'nullable',
                'file',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(2 * 1024),
                'mimetypes:image/jpeg,image/png,image/webp',
                new NoExecutableCode(),
            ],
            'image' => [
                'nullable',
                'file',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(2 * 1024),
                'mimetypes:image/jpeg,image/png,image/webp',
                new NoExecutableCode(),
            ],

            // Validasi standar untuk field 'attachment' / 'evidence' / 'evidence_file' (Maksimal 5MB)
            'attachment' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'mimetypes:application/pdf,image/jpeg,image/png,image/webp',
                'max:5120',
                new NoExecutableCode(),
            ],
            'evidence_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'mimetypes:application/pdf,image/jpeg,image/png,image/webp',
                'max:5120',
                new NoExecutableCode(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.image'          => 'File yang diunggah harus berupa gambar.',
            'photo.mimetypes'      => 'Format gambar hanya boleh JPG, PNG, atau WEBP.',
            'photo.max'            => 'Ukuran foto maksimal 2MB.',
            'image.image'          => 'File yang diunggah harus berupa gambar.',
            'image.mimetypes'      => 'Format gambar hanya boleh JPG, PNG, atau WEBP.',
            'image.max'            => 'Ukuran gambar maksimal 2MB.',
            'attachment.mimes'     => 'Lampiran dokumen hanya boleh berformat PDF, JPG, PNG, atau WEBP.',
            'attachment.max'       => 'Ukuran dokumen maksimal 5MB.',
            'evidence_file.mimes'  => 'Berkas bukti hanya boleh berformat PDF, JPG, PNG, atau WEBP.',
            'evidence_file.max'    => 'Ukuran berkas bukti maksimal 5MB.',
        ];
    }
}

