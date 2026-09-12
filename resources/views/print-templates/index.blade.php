<x-app-layout>
    <x-slot name="title">Template Cetak & Kop Surat</x-slot>

    {{-- Page Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 class="fw-700" style="font-size: 20px; color: #0f172a; margin: 0;">
                Template Kop Surat
            </h2>
            <p class="text-muted" style="font-size: 13px; margin-top: 4px;">
                Pengaturan kop surat dokumen cetak Permintaan Pengadaan (PR) dan Purchase Order (PO).
            </p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="openUploadModal()" style="font-weight: 500;">
                Upload Template
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <div style="font-weight: 600; margin-bottom: 6px;">
                Terjadi kesalahan saat menyimpan:
            </div>
            <ul style="margin: 0; padding-left: 20px; font-size: 13px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Active Templates Status Overview --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Active for PR --}}
        <div class="card" style="padding: 16px 18px; border: 1px solid #e2e8f0; background: #ffffff;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">Kop Aktif PR</span>
                    <div style="font-weight: 600; font-size: 14px; color: #0f172a; margin-top: 4px;">
                        {{ $activePR ? $activePR->name : 'Standar Sistem' }}
                    </div>
                    <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                        {{ $activePR ? ($activePR->width_px . ' × ' . $activePR->height_px . ' px • ' . $activePR->file_size_human) : 'kop-surat-a4.png' }}
                    </div>
                </div>
                @if($activePR)
                <button type="button" class="btn btn-secondary" onclick="previewImage('{{ $activePR->url }}', '{{ $activePR->name }}')" style="padding: 5px 12px; font-size: 12px;">
                    Lihat
                </button>
                @endif
            </div>
        </div>

        {{-- Active for PO --}}
        <div class="card" style="padding: 16px 18px; border: 1px solid #e2e8f0; background: #ffffff;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">Kop Aktif PO</span>
                    <div style="font-weight: 600; font-size: 14px; color: #0f172a; margin-top: 4px;">
                        {{ $activePO ? $activePO->name : 'Standar Sistem' }}
                    </div>
                    <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                        {{ $activePO ? ($activePO->width_px . ' × ' . $activePO->height_px . ' px • ' . $activePO->file_size_human) : 'kop-surat-a4.png' }}
                    </div>
                </div>
                @if($activePO)
                <button type="button" class="btn btn-secondary" onclick="previewImage('{{ $activePO->url }}', '{{ $activePO->name }}')" style="padding: 5px 12px; font-size: 12px;">
                    Lihat
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Recommendation Card --}}
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 24px;">
        <div style="font-size: 12.5px; color: #475569; line-height: 1.5;">
            <strong>Spesifikasi Rekomendasi:</strong> Ukuran halaman <strong>A4</strong> (2480 × 3508 piksel @ 300 DPI) format PNG atau file PDF.
        </div>
    </div>

    {{-- Templates Grid --}}
    <div class="template-grid">
        @forelse($templates as $template)
            <div class="card template-card">
                {{-- Preview Thumbnail --}}
                <div class="template-thumb-wrapper" onclick="previewImage('{{ $template->url }}', '{{ $template->name }}')">
                    <img src="{{ $template->url }}" alt="{{ $template->name }}" class="template-thumb-img">
                    <div class="template-thumb-overlay">
                        <span>Perbesar</span>
                    </div>

                    {{-- Badges on top of thumbnail --}}
                    <div style="position: absolute; top: 10px; left: 10px; display: flex; flex-direction: column; gap: 4px;">
                        @if($template->used_for_pr)
                            <span class="badge" style="background: #0f172a; color: #ffffff; font-weight: 500; font-size: 11px;">
                                PR Aktif
                            </span>
                        @endif
                        @if($template->used_for_po)
                            <span class="badge" style="background: #334155; color: #ffffff; font-weight: 500; font-size: 11px;">
                                PO Aktif
                            </span>
                        @endif
                    </div>

                    {{-- Resolution Pill --}}
                    <span class="template-res-pill">
                        {{ $template->width_px }} × {{ $template->height_px }} px
                    </span>
                </div>

                {{-- Template Info --}}
                <div style="padding: 14px 16px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="font-size: 14px; font-weight: 600; color: #0f172a; margin: 0 0 4px 0; line-height: 1.3;">
                            {{ $template->name }}
                        </h3>
                        @if($template->description)
                            <p style="font-size: 12px; color: #64748b; margin: 0 0 8px 0; line-height: 1.4;">
                                {{ $template->description }}
                            </p>
                        @endif

                        {{-- Metadata Specs --}}
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: #64748b; border-top: 1px solid #f1f5f9; padding-top: 8px; margin-bottom: 8px;">
                            <span>Kualitas: <strong style="color: #334155;">{{ $template->width_px >= 2000 ? '300 DPI' : ($template->width_px >= 794 ? '96 DPI' : 'Rendah') }}</strong></span>
                            <span>Ukuran: <strong style="color: #334155;">{{ $template->file_size_human }}</strong></span>
                        </div>

                        {{-- Padding Info --}}
                        <div style="font-size: 11px; color: #64748b; background: #f8fafc; padding: 6px 10px; border-radius: 6px; margin-bottom: 12px;">
                            Margin: Atas <strong>{{ $template->padding_top_mm }}mm</strong> • Kiri/Kanan <strong>{{ $template->padding_left_mm }}mm</strong> • Bawah <strong>{{ $template->padding_bottom_mm }}mm</strong>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div>
                        <div style="border-top: 1px solid #f1f5f9; padding-top: 10px; display: flex; flex-direction: column; gap: 8px;">
                            {{-- Toggle Buttons --}}
                            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                <form action="{{ route('print-templates.setActive', $template) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <input type="hidden" name="type" value="{{ $template->used_for_pr ? 'none_pr' : 'pr' }}">
                                    <button type="submit" class="btn {{ $template->used_for_pr ? 'btn-primary' : 'btn-secondary' }}" style="padding: 4px 10px; font-size: 11.5px;">
                                        {{ $template->used_for_pr ? 'PR Aktif' : 'Gunakan PR' }}
                                    </button>
                                </form>

                                <form action="{{ route('print-templates.setActive', $template) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <input type="hidden" name="type" value="{{ $template->used_for_po ? 'none_po' : 'po' }}">
                                    <button type="submit" class="btn {{ $template->used_for_po ? 'btn-primary' : 'btn-secondary' }}" style="padding: 4px 10px; font-size: 11.5px;">
                                        {{ $template->used_for_po ? 'PO Aktif' : 'Gunakan PO' }}
                                    </button>
                                </form>

                                @if(!$template->used_for_pr || !$template->used_for_po)
                                <form action="{{ route('print-templates.setActive', $template) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <input type="hidden" name="type" value="both">
                                    <button type="submit" class="btn btn-secondary" style="padding: 4px 10px; font-size: 11.5px;">
                                        Keduanya
                                    </button>
                                </form>
                                @endif
                            </div>

                            {{-- Edit & Delete --}}
                            <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f1f5f9; padding-top: 8px;">
                                <button type="button" class="btn-link" onclick="openEditModal({{ json_encode($template) }})" style="font-size: 12px; color: #475569; cursor: pointer; border: none; background: none; padding: 0; text-decoration: underline;">
                                    Atur Jarak
                                </button>

                                <form action="{{ route('print-templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Hapus template \'{{ addslashes($template->name) }}\'?')" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="font-size: 12px; color: #dc2626; cursor: pointer; border: none; background: none; padding: 0;">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; padding: 48px 24px; text-align: center; border: 1px dashed #cbd5e1;">
                <h4 style="font-size: 15px; font-weight: 600; color: #334155; margin-bottom: 4px;">Belum ada template tersimpan</h4>
                <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
                    Upload file kop surat A4 untuk mulai mencetak dokumen dengan kop resmi perusahaan.
                </p>
                <button type="button" class="btn btn-primary" onclick="openUploadModal()">
                    Upload Template Pertama
                </button>
            </div>
        @endforelse
    </div>

    {{-- ================================================================= --}}
    {{-- MODAL: Upload Template Baru (Vanilla CSS - Guaranteed on top) --}}
    {{-- ================================================================= --}}
    <div id="uploadModal" class="custom-modal-overlay">
        <div class="custom-modal-box">
            <div class="custom-modal-header">
                <h3 style="margin: 0; font-size: 15px; font-weight: 600; color: #0f172a;">
                    Upload Template Kop Surat
                </h3>
                <button type="button" onclick="closeUploadModal()" class="custom-modal-close">&times;</button>
            </div>

            <form action="{{ route('print-templates.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="custom-modal-body">
                    {{-- Dropzone Area --}}
                    <div style="margin-bottom: 16px;">
                        <label class="form-label" style="font-weight: 600;">
                            File Desain Kop Surat (PDF / PNG / JPG) <span style="color: #dc2626;">*</span>
                        </label>
                        <div id="dropzoneBox" class="dropzone-container">
                            <input type="file" name="file" id="fileInput" accept="image/png, image/jpeg, application/pdf, .pdf" style="display: none;" required onchange="handleFileChange(this)">
                            
                            <div id="dropzoneEmpty" onclick="document.getElementById('fileInput').click()">
                                <div style="font-weight: 500; font-size: 13px; color: #1e293b;">
                                    Klik atau tarik file ke sini (PDF, PNG, JPG)
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                    PDF otomatis dikonversi ke gambar resolusi tinggi (Maks 10MB)
                                </div>
                            </div>

                            <div id="dropzoneLoading" style="display: none; text-align: center; padding: 12px 0;">
                                <div style="font-size: 13px; font-weight: 600; color: #1e293b;">Mengonversi Halaman PDF ke Gambar 300 DPI...</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Mohon tunggu sebentar...</div>
                            </div>

                            <div id="dropzoneFilled" style="display: none; text-align: center;">
                                <img id="previewImageElement" src="" alt="" style="max-height: 140px; border-radius: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin: 0 auto 8px auto; display: block;">
                                <div id="previewFilename" style="font-size: 12px; font-weight: 600; color: #1e293b;"></div>
                                <div id="previewSpecs" style="font-size: 11px; color: #64748b; margin-top: 2px;"></div>
                                <div id="previewQualityBadge" style="margin-top: 6px;"></div>
                                <button type="button" onclick="event.stopPropagation(); clearFile()" class="btn btn-secondary" style="margin-top: 8px; padding: 4px 10px; font-size: 11px; color: #dc2626;">
                                    Ganti File
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Template Name --}}
                    <div style="margin-bottom: 14px;">
                        <label class="form-label" style="font-weight: 600;">
                            Nama Template <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" name="name" id="inputTemplateName" class="form-control" required placeholder="Contoh: Kop Surat Resmi PT Arsikon">
                    </div>

                    {{-- Description --}}
                    <div style="margin-bottom: 16px;">
                        <label class="form-label">Deskripsi (Opsional)</label>
                        <input type="text" name="description" class="form-control" placeholder="Contoh: Kop formal untuk purchase order">
                    </div>

                    {{-- Content Margins Box --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px;">
                        <div style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                            <span>Batas Isi Dokumen dari Tepi (mm)</span>
                            <span style="font-size: 10.5px; font-weight: 400; color: #64748b;">Default Standar A4</span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                            <div>
                                <label style="font-size: 10.5px; font-weight: 500; color: #64748b; display: block; margin-bottom: 2px;">Atas (mm)</label>
                                <input type="number" step="0.5" name="padding_top_mm" value="45.0" class="form-control" style="padding: 6px 8px; font-size: 12px;" min="0" max="150">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 500; color: #64748b; display: block; margin-bottom: 2px;">Kiri (mm)</label>
                                <input type="number" step="0.5" name="padding_left_mm" value="14.0" class="form-control" style="padding: 6px 8px; font-size: 12px;" min="0" max="100">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 500; color: #64748b; display: block; margin-bottom: 2px;">Kanan (mm)</label>
                                <input type="number" step="0.5" name="padding_right_mm" value="14.0" class="form-control" style="padding: 6px 8px; font-size: 12px;" min="0" max="100">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 500; color: #64748b; display: block; margin-bottom: 2px;">Bawah (mm)</label>
                                <input type="number" step="0.5" name="padding_bottom_mm" value="22.0" class="form-control" style="padding: 6px 8px; font-size: 12px;" min="0" max="100">
                            </div>
                        </div>
                        <p style="font-size: 10.5px; color: #94a3b8; margin: 8px 0 0 0;">
                            Padding Atas mengatur jarak agar teks dokumen tidak menabrak gambar header/logo kop surat.
                        </p>
                    </div>
                </div>

                <div class="custom-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeUploadModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        Simpan Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- MODAL: Edit Pengaturan Template                                   --}}
    {{-- ================================================================= --}}
    <div id="editModal" class="custom-modal-overlay">
        <div class="custom-modal-box">
            <div class="custom-modal-header">
                <h3 style="margin: 0; font-size: 15px; font-weight: 600; color: #0f172a;">
                    Edit Pengaturan Jarak Template
                </h3>
                <button type="button" onclick="closeEditModal()" class="custom-modal-close">&times;</button>
            </div>

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="custom-modal-body">
                    <div style="margin-bottom: 14px;">
                        <label class="form-label" style="font-weight: 600;">Nama Template <span style="color: #dc2626;">*</span></label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" id="edit_description" name="description" class="form-control">
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px;">
                        <div style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 8px;">
                            Batas Isi Dokumen (mm)
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                            <div>
                                <label style="font-size: 10.5px; font-weight: 500; color: #64748b; display: block; margin-bottom: 2px;">Atas (mm)</label>
                                <input type="number" step="0.5" id="edit_padding_top_mm" name="padding_top_mm" class="form-control" style="padding: 6px 8px; font-size: 12px;" min="0" max="150">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 500; color: #64748b; display: block; margin-bottom: 2px;">Kiri (mm)</label>
                                <input type="number" step="0.5" id="edit_padding_left_mm" name="padding_left_mm" class="form-control" style="padding: 6px 8px; font-size: 12px;" min="0" max="100">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 500; color: #64748b; display: block; margin-bottom: 2px;">Kanan (mm)</label>
                                <input type="number" step="0.5" id="edit_padding_right_mm" name="padding_right_mm" class="form-control" style="padding: 6px 8px; font-size: 12px;" min="0" max="100">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 500; color: #64748b; display: block; margin-bottom: 2px;">Bawah (mm)</label>
                                <input type="number" step="0.5" id="edit_padding_bottom_mm" name="padding_bottom_mm" class="form-control" style="padding: 6px 8px; font-size: 12px;" min="0" max="100">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="custom-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- MODAL: Full Size Preview Modal                                   --}}
    {{-- ================================================================= --}}
    <div id="previewModal" class="custom-modal-overlay" onclick="closePreviewModal()">
        <div style="position: relative; max-width: 900px; max-height: 90vh; display: flex; flex-direction: column; align-items: center;" onclick="event.stopPropagation()">
            <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; color: #fff; margin-bottom: 10px;">
                <span id="previewModalTitle" style="font-weight: 600; font-size: 14px;"></span>
                <button type="button" onclick="closePreviewModal()" style="color: #fff; font-size: 24px; background: none; border: none; cursor: pointer;">&times;</button>
            </div>
            <img id="previewModalImg" src="" alt="Preview" style="max-height: 82vh; max-width: 100%; border-radius: 6px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); background: #fff; object-fit: contain;">
        </div>
    </div>

    @push('styles')
    <style>
        /* Template Grid Layout */
        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .template-card {
            display: flex;
            flex-direction: column;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .template-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        /* Thumbnail Container with A4 proportions */
        .template-thumb-wrapper {
            position: relative;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 220px;
            cursor: pointer;
            overflow: hidden;
        }
        .template-thumb-img {
            max-height: 190px;
            width: auto;
            border-radius: 4px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.12);
            transition: transform 0.2s ease;
        }
        .template-thumb-wrapper:hover .template-thumb-img {
            transform: scale(1.03);
        }
        .template-thumb-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .template-thumb-wrapper:hover .template-thumb-overlay {
            opacity: 1;
        }
        .template-thumb-overlay span {
            background: #ffffff;
            color: #0f172a;
            font-size: 11.5px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .template-res-pill {
            position: absolute;
            bottom: 8px;
            right: 8px;
            font-size: 10px;
            font-weight: 700;
            background: rgba(0, 0, 0, 0.65);
            color: #ffffff;
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* Dropzone Component */
        .dropzone-container {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: border-color 0.2s, background-color 0.2s;
        }
        .dropzone-container:hover {
            border-color: #94a3b8;
            background: #f1f5f9;
        }

        /* ===================================================== */
        /* MODAL OVERLAY & BOX - Guaranteed Z-Index & Visibility */
        /* ===================================================== */
        .custom-modal-overlay {
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(15, 23, 42, 0.65) !important;
            backdrop-filter: blur(4px) !important;
            z-index: 99999 !important; /* Above sidebar (1000) */
            display: none !important; /* Hidden by default */
            align-items: center !important;
            justify-content: center !important;
            padding: 20px !important;
            box-sizing: border-box !important;
        }
        .custom-modal-overlay.open {
            display: flex !important; /* Display only when .open is present */
        }

        .custom-modal-box {
            background: #ffffff;
            border-radius: 12px;
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            animation: modalFadeIn 0.2s ease-out;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .custom-modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .custom-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #94a3b8;
            cursor: pointer;
            line-height: 1;
        }
        .custom-modal-close:hover {
            color: #0f172a;
        }

        .custom-modal-body {
            padding: 20px;
            overflow-y: auto;
        }

        .custom-modal-footer {
            padding: 14px 20px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>
    @endpush
    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Setup PDF.js worker
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }

        // Modal toggling functions
        function openUploadModal() {
            document.getElementById('uploadModal').classList.add('open');
        }
        function closeUploadModal() {
            document.getElementById('uploadModal').classList.remove('open');
        }

        function openEditModal(template) {
            const form = document.getElementById('editForm');
            form.action = '/print-templates/' + template.id;
            document.getElementById('edit_name').value = template.name;
            document.getElementById('edit_description').value = template.description || '';
            document.getElementById('edit_padding_top_mm').value = template.padding_top_mm;
            document.getElementById('edit_padding_left_mm').value = template.padding_left_mm;
            document.getElementById('edit_padding_right_mm').value = template.padding_right_mm;
            document.getElementById('edit_padding_bottom_mm').value = template.padding_bottom_mm;
            document.getElementById('editModal').classList.add('open');
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('open');
        }

        function previewImage(url, title) {
            document.getElementById('previewModalImg').src = url;
            document.getElementById('previewModalTitle').innerText = title;
            document.getElementById('previewModal').classList.add('open');
        }
        function closePreviewModal() {
            document.getElementById('previewModal').classList.remove('open');
            document.getElementById('previewModalImg').src = '';
        }

        // Drag & Drop handlers for dropzone
        document.addEventListener('DOMContentLoaded', function() {
            const dropzone = document.getElementById('dropzoneBox');
            if (dropzone) {
                dropzone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    dropzone.style.borderColor = '#ea580c';
                    dropzone.style.backgroundColor = '#fff7ed';
                });
                dropzone.addEventListener('dragleave', function() {
                    dropzone.style.borderColor = '#cbd5e1';
                    dropzone.style.backgroundColor = '#f8fafc';
                });
                dropzone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    dropzone.style.borderColor = '#cbd5e1';
                    dropzone.style.backgroundColor = '#f8fafc';
                    if (e.dataTransfer && e.dataTransfer.files.length) {
                        const fileInput = document.getElementById('fileInput');
                        fileInput.files = e.dataTransfer.files;
                        handleFileChange(fileInput);
                    }
                });
            }
        });

        // Dropzone & File selection handler
        function handleFileChange(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');

            // CASE 1: File is PDF -> Convert page 1 to 300 DPI PNG via PDF.js
            if (isPdf) {
                document.getElementById('dropzoneEmpty').style.display = 'none';
                document.getElementById('dropzoneFilled').style.display = 'none';
                document.getElementById('dropzoneLoading').style.display = 'block';

                const reader = new FileReader();
                reader.onload = function(e) {
                    const typedarray = new Uint8Array(e.target.result);
                    pdfjsLib.getDocument(typedarray).promise.then(function(pdfDoc) {
                        pdfDoc.getPage(1).then(function(page) {
                            // Target 2480px width (Standard A4 at 300 DPI)
                            const unscaled = page.getViewport({ scale: 1.0 });
                            const targetWidth = 2480;
                            const scale = targetWidth / unscaled.width;
                            const viewport = page.getViewport({ scale: scale });

                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');
                            canvas.width = Math.round(viewport.width);
                            canvas.height = Math.round(viewport.height);

                            const renderContext = {
                                canvasContext: ctx,
                                viewport: viewport
                            };

                            page.render(renderContext).promise.then(function() {
                                canvas.toBlob(function(blob) {
                                    if (!blob) {
                                        alert('Gagal mengonversi file PDF.');
                                        clearFile();
                                        return;
                                    }

                                    const cleanName = file.name.replace(/\.pdf$/i, '');
                                    const convertedFileName = cleanName + '.png';
                                    const convertedFile = new File([blob], convertedFileName, { type: 'image/png' });

                                    // Set converted file into file input
                                    try {
                                        const dt = new DataTransfer();
                                        dt.items.add(convertedFile);
                                        input.files = dt.files;
                                    } catch (err) {
                                        console.warn('DataTransfer fallback:', err);
                                    }

                                    // Auto-suggest template name if empty
                                    const nameInput = document.getElementById('inputTemplateName');
                                    if (nameInput && !nameInput.value.trim()) {
                                        nameInput.value = cleanName.replace(/[-_]/g, ' ');
                                    }

                                    // Display preview
                                    const previewImg = document.getElementById('previewImageElement');
                                    previewImg.src = canvas.toDataURL('image/png');

                                    const sizeMB = (convertedFile.size / (1024 * 1024)).toFixed(2);
                                    document.getElementById('previewFilename').innerText = `${file.name} ➔ ${convertedFileName}`;
                                    document.getElementById('previewSpecs').innerText = `${sizeMB} MB • ${canvas.width} × ${canvas.height} px`;
                                    document.getElementById('previewQualityBadge').innerHTML = '<span class="badge badge-success" style="font-size:11px;"><i class="fas fa-circle-check"></i> PDF Berhasil Dikonversi ke 300 DPI (Cetak Tajam)</span>';

                                    document.getElementById('dropzoneLoading').style.display = 'none';
                                    document.getElementById('dropzoneFilled').style.display = 'block';
                                }, 'image/png', 0.95);
                            });
                        });
                    }).catch(function(err) {
                        alert('Gagal memproses file PDF: ' + err.message);
                        clearFile();
                    });
                };
                reader.readAsArrayBuffer(file);
                return;
            }

            // CASE 2: File is PNG / JPG Image
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const width = img.naturalWidth;
                    const height = img.naturalHeight;
                    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);

                    const previewImg = document.getElementById('previewImageElement');
                    previewImg.src = e.target.result;

                    // Auto-suggest template name if empty
                    const cleanName = file.name.replace(/\.[^/.]+$/, '');
                    const nameInput = document.getElementById('inputTemplateName');
                    if (nameInput && !nameInput.value.trim()) {
                        nameInput.value = cleanName.replace(/[-_]/g, ' ');
                    }

                    document.getElementById('previewFilename').innerText = file.name;
                    document.getElementById('previewSpecs').innerText = `${sizeMB} MB • ${width} × ${height} px`;

                    const qualityDiv = document.getElementById('previewQualityBadge');
                    if (width >= 2000) {
                        qualityDiv.innerHTML = '<span class="badge badge-success" style="font-size:11px;"><i class="fas fa-circle-check"></i> 300 DPI Standar Cetak Tajam</span>';
                    } else if (width >= 1000) {
                        qualityDiv.innerHTML = '<span class="badge badge-warning" style="font-size:11px;"><i class="fas fa-circle-exclamation"></i> Resolusi Sedang (Disarankan ≥2000px)</span>';
                    } else {
                        qualityDiv.innerHTML = '<span class="badge badge-danger" style="font-size:11px;"><i class="fas fa-triangle-exclamation"></i> Resolusi Rendah (Bisa pecah)</span>';
                    }

                    document.getElementById('dropzoneEmpty').style.display = 'none';
                    document.getElementById('dropzoneFilled').style.display = 'block';
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function clearFile() {
            const input = document.getElementById('fileInput');
            input.value = '';
            document.getElementById('previewImageElement').src = '';
            document.getElementById('dropzoneLoading').style.display = 'none';
            document.getElementById('dropzoneFilled').style.display = 'none';
            document.getElementById('dropzoneEmpty').style.display = 'block';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeUploadModal();
                closeEditModal();
                closePreviewModal();
            }
        });
    </script>
    @endpush
</x-app-layout>
