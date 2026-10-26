<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengingat Batas Waktu Temuan Audit</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        @php
            $isOverdue = ($urgencyType === 'OVERDUE');
            $headerColor = $isOverdue ? 'linear-gradient(135deg, #b91c1c, #dc2626)' : 'linear-gradient(135deg, #b45309, #f59e0b)';
        @endphp
        <div style="background: {{ $headerColor }}; padding: 24px 32px; color: #ffffff;">
            <div style="font-size: 13px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9;">
                PT Arsikon Cipta Karya &bull; Peringatan Audit
            </div>
            <h1 style="margin: 8px 0 0; font-size: 20px; font-weight: 700;">
                {{ $isOverdue ? '⚠️ PERINGATAN: Temuan Audit Melewati Batas Waktu (Overdue)' : '⏱️ Pengingat Batas Waktu Tindak Lanjut Audit (H-3)' }}
            </h1>
        </div>

        <div style="padding: 32px;">
            <p style="font-size: 15px; line-height: 1.6; margin-top: 0;">
                Yth. <strong>{{ $finding->pic->name ?? 'PIC Terkait' }}</strong>,
            </p>
            <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                @if($isOverdue)
                    Tiket temuan audit berikut telah <strong>melewati batas waktu (overdue)</strong> dan saat ini masih berstatus <strong>{{ $finding->status }}</strong>. Mohon segera mengunggah bukti penyelesaian atau berikan klarifikasi tertulis.
                @else
                    Batas waktu penyelesaian untuk tiket temuan audit berikut tersisa kurang dari <strong>3 hari kerja</strong>. Harap segera melengkapi rencana tindak lanjut perbaikan sebelum jatuh tempo.
                @endif
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
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #64748b;">Gudang / Lokasi</td>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0;">{{ $finding->warehouse->name ?? 'Pusat / Operasional' }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #64748b;">Tingkat Risiko</td>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: {{ in_array($finding->risk_level, ['Critical', 'High']) ? '#dc2626' : '#d97706' }};">
                        {{ $finding->risk_level }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #64748b;">Dampak Finansial</td>
                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #dc2626;">
                        Rp {{ number_format($finding->financial_impact, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; color: #64748b;">Batas Waktu (Due Date)</td>
                    <td style="padding: 12px 16px; font-weight: 700; color: {{ $isOverdue ? '#dc2626' : '#d97706' }};">
                        {{ $finding->due_date ? $finding->due_date->format('d M Y') : '-' }}
                        @if($isOverdue)
                            <span style="font-size: 11px; font-weight: normal; background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px; margin-left: 6px;">LEWAT JATUH TEMPO</span>
                        @endif
                    </td>
                </tr>
            </table>

            <div style="margin-top: 28px; text-align: center;">
                <a href="{{ url('/reports') }}" style="display: inline-block; background: {{ $isOverdue ? '#dc2626' : '#2563eb' }}; color: #ffffff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;">
                    Unggah Bukti Tindak Lanjut
                </a>
            </div>
        </div>

        <div style="background: #f1f5f9; padding: 16px 32px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0;">
            Sistem Otomatisasi Notifikasi Audit &bull; PT Arsikon Cipta Karya
        </div>
    </div>
</body>
</html>
