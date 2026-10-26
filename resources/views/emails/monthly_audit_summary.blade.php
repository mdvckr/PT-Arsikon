<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Eksekutif Bulanan Audit & Logistik</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 24px; color: #1e293b;">
    <div style="max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background: linear-gradient(135deg, #0f172a, #1e293b); padding: 28px 32px; color: #ffffff;">
            <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; font-weight: 600;">Executive Management Report</div>
            <h1 style="margin: 8px 0 4px; font-size: 22px; font-weight: 700;">Rekapitulasi Audit Internal & Logistik</h1>
            <div style="font-size: 14px; color: #cbd5e1;">Periode: <strong>{{ $periodLabel }}</strong> &bull; PT Arsikon Cipta Karya</div>
        </div>

        <div style="padding: 32px;">
            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin-top: 0;">
                Berikut ringkasan kinerja audit berkala, kepatuhan operasional gudang, pengadaan PO, serta eksposur finansial akibat diskrepansi stok pada bulan lalu.
            </p>

            <!-- KPI Cards Grid -->
            <table style="width: 100%; border-collapse: separate; border-spacing: 8px; margin: 20px 0;">
                <tr>
                    <td style="width: 50%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; text-align: center;">
                        <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Total Temuan Bulan Lalu</div>
                        <div style="font-size: 26px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $summaryData['total_findings'] ?? 0 }}</div>
                        <div style="font-size: 12px; color: #16a34a; margin-top: 2px;">{{ $summaryData['closed_findings'] ?? 0 }} Selesai (Closed)</div>
                    </td>
                    <td style="width: 50%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; text-align: center;">
                        <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Tingkat Penyelesaian</div>
                        <div style="font-size: 26px; font-weight: 800; color: {{ ($summaryData['resolution_rate'] ?? 0) >= 80 ? '#16a34a' : '#d97706' }}; margin-top: 4px;">
                            {{ $summaryData['resolution_rate'] ?? 0 }}%
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Target Kepatuhan: &ge; 85%</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 16px; text-align: center;">
                        <div style="font-size: 12px; color: #991b1b; font-weight: 600; text-transform: uppercase;">Total Eksposur Finansial Terbuka (Unresolved)</div>
                        <div style="font-size: 24px; font-weight: 800; color: #dc2626; margin-top: 4px;">
                            Rp {{ number_format($summaryData['open_exposure'] ?? 0, 0, ',', '.') }}
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Breakdown by Source -->
            <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 24px 0 12px;">Distribusi Temuan Berdasarkan Kategori</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                <thead>
                    <tr style="background: #f1f5f9; text-align: left; color: #475569;">
                        <th style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0;">Sumber Kategori</th>
                        <th style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; text-align: center;">Jumlah Kasus</th>
                        <th style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; text-align: right;">Total Nilai Deviasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaryData['by_source'] ?? [] as $src)
                        <tr>
                            <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #334155;">
                                {{ ucwords(str_replace('_', ' ', $src->source_type)) }}
                            </td>
                            <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                {{ $src->total }}
                            </td>
                            <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; text-align: right; font-weight: 600; color: #dc2626;">
                                Rp {{ number_format($src->impact, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="padding: 12px 14px; text-align: center; color: #94a3b8;">Tidak ada data temuan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Top Critical Unresolved Findings -->
            @if(!empty($summaryData['critical_unresolved']) && count($summaryData['critical_unresolved']) > 0)
                <h3 style="font-size: 15px; font-weight: 700; color: #991b1b; margin: 28px 0 12px;">Temuan Kritis Terbuka yang Memerlukan Perhatian Direksi</h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 12.5px; border: 1px solid #fecaca; border-radius: 6px; overflow: hidden;">
                    <thead>
                        <tr style="background: #fee2e2; text-align: left; color: #991b1b;">
                            <th style="padding: 8px 12px; border-bottom: 1px solid #fecaca;">Kode</th>
                            <th style="padding: 8px 12px; border-bottom: 1px solid #fecaca;">Judul Temuan</th>
                            <th style="padding: 8px 12px; border-bottom: 1px solid #fecaca;">PIC</th>
                            <th style="padding: 8px 12px; border-bottom: 1px solid #fecaca; text-align: right;">Eksposur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summaryData['critical_unresolved'] as $crit)
                            <tr>
                                <td style="padding: 8px 12px; border-bottom: 1px solid #fee2e2; font-weight: 700; color: #0f172a;">{{ $crit->finding_code }}</td>
                                <td style="padding: 8px 12px; border-bottom: 1px solid #fee2e2; color: #334155;">{{ $crit->title }}</td>
                                <td style="padding: 8px 12px; border-bottom: 1px solid #fee2e2; color: #475569;">{{ $crit->pic->name ?? '-' }}</td>
                                <td style="padding: 8px 12px; border-bottom: 1px solid #fee2e2; text-align: right; font-weight: 700; color: #dc2626;">
                                    Rp {{ number_format($crit->financial_impact, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div style="margin-top: 32px; text-align: center;">
                <a href="{{ url('/reports') }}" style="display: inline-block; background: #0f172a; color: #ffffff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;">
                    Akses Dashboard Laporan Terpadu
                </a>
            </div>
        </div>

        <div style="background: #f8fafc; padding: 16px 32px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0;">
            Laporan Eksekutif Otomatis Bulanan &bull; Tim Audit & WMS PT Arsikon Cipta Karya
        </div>
    </div>
</body>
</html>
