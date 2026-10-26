<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penugasan Temuan Audit</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background: linear-gradient(135deg, #1e3a8a, #2563eb); padding: 24px 32px; color: #ffffff;">
            <div style="font-size: 13px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.85;">PT Arsikon Cipta Karya &bull; Sistem Audit Internal</div>
            <h1 style="margin: 8px 0 0; font-size: 20px; font-weight: 700;">Penugasan Temuan Audit Baru</h1>
        </div>

        <div style="padding: 32px;">
            <p style="font-size: 15px; line-height: 1.6; margin-top: 0;">
                Halo <strong>{{ $finding->pic->name ?? 'PIC Terkait' }}</strong>,
            </p>
            <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                Sistem mendeteksi adanya tiket temuan audit baru yang memerlukan tindak lanjut perbaikan (corrective action plan) dari Anda.
            </p>

            <table style="width: 100%; border-collapse: collapse; margin: 24px 0; font-size: 14px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                <tr>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #64748b; width: 140px;">No. Temuan</td>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #0f172a;">{{ $finding->finding_code }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #64748b;">Judul</td>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #0f172a;">{{ $finding->title }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #64748b;">Sumber / Kategori</td>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0;">
                        <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: #e0e7ff; color: #3730a3;">
                            {{ strtoupper(str_replace('_', ' ', $finding->source_type)) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #64748b;">Tingkat Risiko</td>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0;">
                        <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; 
                            background: {{ in_array($finding->risk_level, ['Critical', 'High']) ? '#fee2e2' : '#fef3c7' }}; 
                            color: {{ in_array($finding->risk_level, ['Critical', 'High']) ? '#991b1b' : '#92400e' }};">
                            {{ $finding->risk_level }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #64748b;">Dampak Finansial</td>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #dc2626;">
                        Rp {{ number_format($finding->financial_impact, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #64748b;">Batas Waktu (Due Date)</td>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #d97706;">
                        {{ $finding->due_date ? $finding->due_date->format('d M Y') : '-' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; color: #64748b; vertical-align: top;">Keterangan</td>
                    <td style="padding: 12px 16px; color: #334155; line-height: 1.5;">{{ $finding->description }}</td>
                </tr>
            </table>

            <p style="font-size: 13px; color: #64748b; line-height: 1.5;">
                Mohon segera lakukan investigasi lapangan dan submit bukti perbaikan atau berita acara melalui aplikasi sebelum batas waktu yang telah ditentukan.
            </p>

            <div style="margin-top: 28px; text-align: center;">
                <a href="{{ url('/reports') }}" style="display: inline-block; background: #2563eb; color: #ffffff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;">
                    Buka Modul Laporan & Audit
                </a>
            </div>
        </div>

        <div style="background: #f1f5f9; padding: 16px 32px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0;">
            Email ini dibuat secara otomatis oleh Sistem Manajemen Gudang & Pengadaan PT Arsikon Cipta Karya. Harap tidak membalas email ini.
        </div>
    </div>
</body>
</html>
