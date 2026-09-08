<x-app-layout>
    <x-slot name="title">Detail Profil: {{ $user->name }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('users.index') }}">Data Pengguna</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $user->name }}</span>
    </div>

    <div class="grid" style="grid-template-columns:300px 1fr;gap:24px;align-items:start;">

        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-circle text-primary"></i> <span class="card-title">Profil Pengguna</span>
                @can('manage users')
                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning"><i class="fas fa-pen"></i> Edit</a>
                @endcan
            </div>
            <div class="card-body" style="text-align:center;">
                <div style="width:80px;height:80px;background:#e2e8f0;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:32px;color:#64748b;font-weight:bold;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h3 class="fw-700 m-0" style="font-size:18px;">{{ $user->name }}</h3>
                <p class="text-muted" style="margin:4px 0 16px;">{{ $user->email }}</p>

                <div class="mb-3">
                    @if($user->is_active)
                        <span class="badge badge-success">Akun Aktif</span>
                    @else
                        <span class="badge badge-danger">Akun Nonaktif</span>
                    @endif
                </div>

                <div style="border-top:1px solid #f1f5f9;padding-top:16px;text-align:left;">
                    <div class="text-muted fw-600 mb-2" style="font-size:12px;text-transform:uppercase;">Role / Peran</div>
                    <div class="flex gap-1" style="flex-wrap:wrap;">
                        @foreach($user->roles as $role)
                            <span class="badge badge-primary">{{ ucfirst($role->name) }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-shield-halved text-primary"></i> <span class="card-title">Aktivitas Terakhir (Audit Log)</span>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Aksi</th>
                            <th>Modul</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->auditLogs()->latest()->take(10)->get() as $log)
                        <tr>
                            <td class="text-muted" style="font-size:12.5px;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge badge-gray">{{ strtoupper($log->action) }}</span></td>
                            <td class="text-muted" style="font-size:12.5px;">{{ class_basename($log->subject_type ?? 'System') }}</td>
                            <td style="font-size:12.5px;">{{ $log->description }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted p-4">Belum ada aktivitas terekam.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
