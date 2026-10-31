<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureAttachmentController extends Controller
{
    /**
     * Sajikan dokumen internal secara aman dengan autentikasi dan header nosniff.
     */
    public function show(Request $request, string $path): StreamedResponse
    {
        // Cegah Path Traversal
        if (str_contains($path, '..')) {
            abort(403, 'Akses tidak sah.');
        }

        $disk = null;
        if (Storage::disk('private')->exists($path)) {
            $disk = Storage::disk('private');
        } elseif (Storage::disk('local')->exists($path)) {
            $disk = Storage::disk('local');
        } elseif (Storage::disk('public')->exists($path)) {
            $disk = Storage::disk('public');
        }

        if (!$disk) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';

        // Paksa header keamanan agar browser tidak mengeksekusi script (X-Content-Type-Options: nosniff)
        return $disk->response($path, null, [
            'Content-Type'           => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition'    => 'inline',
        ]);
    }
}

