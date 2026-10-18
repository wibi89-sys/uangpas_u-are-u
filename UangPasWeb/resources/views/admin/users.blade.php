@extends('layouts.admin')

@section('title', 'Manajemen Petugas & Hak Akses')
@section('page_title', 'Manajemen Petugas & Level Pengguna (RBAC)')
@section('page_desc', 'Pengaturan Level Hak Akses Kantor Wilayah, Kepala Lapas, Gudang, Loket, Kasir & Auditor')

@section('content')
<!-- Role Summary Cards -->
<div class="grid-4" style="margin-bottom: 24px;">
    <div class="card stat-card">
        <div>
            <div class="stat-label">Superadmin Kanwil</div>
            <div class="stat-value">{{ $users->where('role', 'kanwil_admin')->count() }}</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Akses Seluruh UPT Se-Wilayah</div>
        </div>
        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">🏛️</div>
    </div>
    <div class="card stat-card">
        <div>
            <div class="stat-label">Kepala UPT / Kalapas</div>
            <div class="stat-value">{{ $users->where('role', 'kalapas')->count() }}</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Pimpinan & Otorisator UPT</div>
        </div>
        <div class="stat-icon" style="background: rgba(2, 132, 199, 0.12); color: #0284c7;">⭐</div>
    </div>
    <div class="card stat-card">
        <div>
            <div class="stat-label">Admin Koperasi & Gudang</div>
            <div class="stat-value">{{ $users->where('role', 'admin_koperasi')->count() }}</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Katalog, Stok & Opname</div>
        </div>
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">📦</div>
    </div>
    <div class="card stat-card">
        <div>
            <div class="stat-label">Kasir & Loket Registrasi</div>
            <div class="stat-value">{{ $users->whereIn('role', ['kasir', 'petugas_loket'])->count() }}</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Front-Office POS & Biometrik</div>
        </div>
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">🛒</div>
    </div>
</div>

<!-- Filter & Add Button -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <form method="GET" action="{{ route('admin.users') }}" style="display: flex; gap: 10px; flex-grow: 1; max-width: 650px;">
        <input type="text" name="search" placeholder="Cari Nama Petugas, NIP, atau Email..." value="{{ request('search') }}" style="flex-grow: 1;">
        <select name="role" style="width: 200px;">
            <option value="">Semua Level Peran</option>
            <option value="kanwil_admin" {{ request('role') == 'kanwil_admin' ? 'selected' : '' }}>Superadmin Kanwil</option>
            <option value="kalapas" {{ request('role') == 'kalapas' ? 'selected' : '' }}>Kalapas / Ka. UPT</option>
            <option value="admin_koperasi" {{ request('role') == 'admin_koperasi' ? 'selected' : '' }}>Admin Koperasi / Gudang</option>
            <option value="petugas_loket" {{ request('role') == 'petugas_loket' ? 'selected' : '' }}>Petugas Loket & Registrasi</option>
            <option value="kasir" {{ request('role') == 'kasir' ? 'selected' : '' }}>Kasir POS</option>
            <option value="auditor" {{ request('role') == 'auditor' ? 'selected' : '' }}>Auditor / Pengawas</option>
        </select>
        <button type="submit" class="btn btn-outline">Cari</button>
    </form>

    <button class="btn btn-primary" onclick="openModal('modalAddUser')">
        <span>➕</span> Tambah Petugas Baru
    </button>
</div>

<!-- Table Card -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nama Petugas / NIP</th>
                    <th>Email Login</th>
                    <th>Level Hak Akses</th>
                    <th>Penempatan UPT</th>
                    <th>Kontak HP</th>
                    <th>Status Akun</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <strong style="color: #0f172a; font-size: 14px;">{{ $user->name }}</strong>
                        <div style="font-size: 11.5px; color: var(--text-muted);">
                            NIP: {{ $user->nip ?? '-' }}
                        </div>
                    </td>
                    <td>
                        <span style="font-family: monospace; font-size: 13px; color: #0284c7;">{{ $user->email }}</span>
                    </td>
                    <td>
                        <span class="pill {{ $user->role_badge_class }}">
                            {{ $user->role_label }}
                        </span>
                    </td>
                    <td>
                        <span class="pill pill-primary">{{ $user->upt_code }}</span>
                    </td>
                    <td>
                        <span style="font-size: 12.5px;">{{ $user->phone ?? '-' }}</span>
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="pill pill-success">● Aktif</span>
                        @else
                            <span class="pill pill-danger">● Non-Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <button class="btn btn-outline" style="padding: 6px 10px; font-size: 11.5px;" onclick="openEditModal({{ json_encode($user) }})">
                                Edit
                            </button>
                            <form action="{{ route('admin.users.toggle_status', $user->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Ubah status aktif akun petugas ini?')">
                                @csrf
                                <button type="submit" class="btn btn-outline" style="padding: 6px 10px; font-size: 11.5px; color: {{ $user->is_active ? '#ef4444' : '#10b981' }};">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">
                        Tidak ada data petugas yang sesuai filter.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $users->links() }}
    </div>
</div>

<!-- Modal Tambah Petugas -->
<div id="modalAddUser" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 100; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 520px; border-radius: 14px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
            <h3 style="font-size: 17px; font-weight: 800; color: #0f172a;">➕ Tambah Akun Petugas Baru</h3>
            <button type="button" onclick="closeModal('modalAddUser')" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #64748b;">✕</button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nama Lengkap Petugas *</label>
                <input type="text" name="name" required placeholder="Contoh: Budi Santoso, S.H.">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>NIP Pegawai</label>
                    <input type="text" name="nip" placeholder="Contoh: 198501012010011001">
                </div>
                <div class="form-group">
                    <label>No. WhatsApp / HP</label>
                    <input type="text" name="phone" placeholder="0812xxxxxxxx">
                </div>
            </div>

            <div class="form-group">
                <label>Email Dinas (Username Login) *</label>
                <input type="email" name="email" required placeholder="nama@lapas.go.id">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Level Hak Akses *</label>
                    <select name="role" required>
                        <option value="kasir">Kasir POS Koperasi</option>
                        <option value="petugas_loket">Petugas Loket & Registrasi</option>
                        <option value="admin_koperasi">Admin Koperasi & Gudang</option>
                        <option value="kalapas">Kalapas / Kepala UPT</option>
                        <option value="auditor">Auditor & Pengawas</option>
                        <option value="kanwil_admin">Superadmin Kantor Wilayah</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Penempatan UPT *</label>
                    <select name="upt_code" required>
                        <option value="LPS-01">Lapas Kelas I Tangerang</option>
                        <option value="KANWIL-BANTEN">Kantor Wilayah Kemenkumham</option>
                        <option value="RTN-01">Rutan Kelas I</option>
                        <option value="LPKA-01">LPKA</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Password Akun *</label>
                <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalAddUser')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Petugas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Petugas -->
<div id="modalEditUser" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 100; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 520px; border-radius: 14px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
            <h3 style="font-size: 17px; font-weight: 800; color: #0f172a;">✏️ Edit Data Petugas</h3>
            <button type="button" onclick="closeModal('modalEditUser')" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #64748b;">✕</button>
        </div>

        <form id="formEditUser" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nama Lengkap Petugas *</label>
                <input type="text" id="edit_name" name="name" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>NIP Pegawai</label>
                    <input type="text" id="edit_nip" name="nip">
                </div>
                <div class="form-group">
                    <label>No. WhatsApp / HP</label>
                    <input type="text" id="edit_phone" name="phone">
                </div>
            </div>

            <div class="form-group">
                <label>Email Dinas *</label>
                <input type="email" id="edit_email" name="email" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>Level Hak Akses *</label>
                    <select id="edit_role" name="role" required>
                        <option value="kasir">Kasir POS Koperasi</option>
                        <option value="petugas_loket">Petugas Loket & Registrasi</option>
                        <option value="admin_koperasi">Admin Koperasi & Gudang</option>
                        <option value="kalapas">Kalapas / Kepala UPT</option>
                        <option value="auditor">Auditor & Pengawas</option>
                        <option value="kanwil_admin">Superadmin Kantor Wilayah</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Penempatan UPT *</label>
                    <select id="edit_upt_code" name="upt_code" required>
                        <option value="LPS-01">Lapas Kelas I Tangerang</option>
                        <option value="KANWIL-BANTEN">Kantor Wilayah Kemenkumham</option>
                        <option value="RTN-01">Rutan Kelas I</option>
                        <option value="LPKA-01">LPKA</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Ganti Password (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" minlength="6" placeholder="Isi hanya jika ingin ganti password">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEditUser')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'flex';
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    }

    function openEditModal(user) {
        document.getElementById('formEditUser').action = "/users/" + user.id;
        document.getElementById('edit_name').value = user.name || '';
        document.getElementById('edit_nip').value = user.nip || '';
        document.getElementById('edit_phone').value = user.phone || '';
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_role').value = user.role || 'kasir';
        document.getElementById('edit_upt_code').value = user.upt_code || 'LPS-01';
        openModal('modalEditUser');
    }
</script>
@endsection
