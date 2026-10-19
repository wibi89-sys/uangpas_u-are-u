@extends('layouts.admin')

@section('title', 'Manajemen Petugas & Hak Akses')
@section('page_title', 'Manajemen Akun Petugas & Hak Akses (RBAC)')
@section('page_desc', 'Pengaturan Hak Akses Administrator UPT, Loket, Kasir, Pengelola Gudang & Pengawas')

@section('content')
<!-- Role Summary Cards -->
<div class="grid-4" style="margin-bottom: 24px;">
    <div class="card stat-card">
        <div>
            <div class="stat-label">Admin Utama UPT & Kanwil</div>
            <div class="stat-value">{{ $users->whereIn('role', ['kanwil_admin', 'kalapas', 'admin_koperasi'])->count() }}</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Pengelola Sistem & Otorisator</div>
        </div>
        <div class="stat-icon" style="background: rgba(2, 132, 199, 0.12); color: #0284c7;">🛡️</div>
    </div>
    <div class="card stat-card">
        <div>
            <div class="stat-label">Petugas Kasir POS</div>
            <div class="stat-value">{{ $users->where('role', 'kasir')->count() }}</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Kasir Belanja Toko Koperasi</div>
        </div>
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">🛒</div>
    </div>
    <div class="card stat-card">
        <div>
            <div class="stat-label">Loket & Registrasi WBP</div>
            <div class="stat-value">{{ $users->where('role', 'petugas_loket')->count() }}</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Top-Up & Perekaman Biometrik</div>
        </div>
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">👆</div>
    </div>
    <div class="card stat-card">
        <div>
            <div class="stat-label">Auditor & Pengawas</div>
            <div class="stat-value">{{ $users->where('role', 'auditor')->count() }}</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Pemeriksa Transaksi & Arus Kas</div>
        </div>
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">📑</div>
    </div>
</div>

<!-- Filter & Add Button -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <form method="GET" action="{{ route('admin.users') }}" style="display: flex; gap: 10px; flex-grow: 1; max-width: 650px;">
        <input type="text" name="search" placeholder="Cari Nama Petugas, NIP, atau Email..." value="{{ request('search') }}" style="flex-grow: 1;">
        <select name="role" style="width: 220px;" onchange="this.form.submit()">
            <option value="">Semua Level Peran</option>
            <option value="kanwil_admin" {{ request('role') == 'kanwil_admin' ? 'selected' : '' }}>Superadmin Kanwil</option>
            <option value="kalapas" {{ request('role') == 'kalapas' ? 'selected' : '' }}>Kalapas / Ka. UPT</option>
            <option value="admin_koperasi" {{ request('role') == 'admin_koperasi' ? 'selected' : '' }}>Admin Utama UPT & Koperasi</option>
            <option value="petugas_loket" {{ request('role') == 'petugas_loket' ? 'selected' : '' }}>Petugas Loket & Registrasi</option>
            <option value="kasir" {{ request('role') == 'kasir' ? 'selected' : '' }}>Kasir POS Koperasi</option>
            <option value="auditor" {{ request('role') == 'auditor' ? 'selected' : '' }}>Auditor / Pengawas</option>
        </select>
        @if(request()->anyFilled(['search', 'role']))
            <a href="{{ route('admin.users') }}" class="btn btn-outline" style="padding: 9px 14px;">Reset</a>
        @endif
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
                    <th>Peran Utama</th>
                    <th>Centang Hak Akses Aktif</th>
                    <th>UPT Satker</th>
                    <th>Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <strong style="color: #0f172a; font-size: 13.5px;">{{ $user->name }}</strong>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                            NIP: <code>{{ $user->nip ?? '-' }}</code>
                        </div>
                    </td>
                    <td>
                        <span style="font-family: monospace; font-size: 12.5px; color: #0284c7;">{{ $user->email }}</span>
                        @if($user->phone)
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">📞 {{ $user->phone }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="pill {{ $user->role_badge_class }}">
                            {{ $user->role_label }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; flex-wrap: wrap; gap: 4px; max-width: 280px;">
                            @php
                                $permDefs = [
                                    'wbp_manage' => ['label' => 'WBP', 'color' => '#0284c7', 'bg' => '#e0f2fe'],
                                    'topup_manage' => ['label' => 'TopUp', 'color' => '#059669', 'bg' => '#ecfdf5'],
                                    'pos_checkout' => ['label' => 'Kasir', 'color' => '#10b981', 'bg' => '#d1fae5'],
                                    'inventory_manage' => ['label' => 'Stok', 'color' => '#d97706', 'bg' => '#fef3c7'],
                                    'reports_finance' => ['label' => 'Audit', 'color' => '#7c3aed', 'bg' => '#ede9fe'],
                                    'settings_app' => ['label' => 'Pengaturan', 'color' => '#ea580c', 'bg' => '#ffedd5'],
                                    'users_manage' => ['label' => 'Kelola Akun', 'color' => '#e11d48', 'bg' => '#ffe4e6'],
                                ];
                            @endphp
                            @foreach($permDefs as $permKey => $pInfo)
                                @if($user->hasPermission($permKey))
                                    <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; color: {{ $pInfo['color'] }}; background: {{ $pInfo['bg'] }};">
                                        {{ $pInfo['label'] }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <span class="pill pill-primary" style="font-family: monospace;">{{ $user->upt_code }}</span>
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="pill pill-success">● Aktif</span>
                        @else
                            <span class="pill pill-danger">● Non-Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <button class="btn btn-outline" style="padding: 6px 10px; font-size: 11.5px;" onclick="openEditModal({{ json_encode($user) }})">
                                Edit
                            </button>
                            <form action="{{ route('admin.users.toggle_status', $user->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Ubah status aktif petugas {{ $user->name }}?')">
                                @csrf
                                <button type="submit" class="btn btn-outline" style="padding: 6px 10px; font-size: 11.5px; color: {{ $user->is_active ? '#ef4444' : '#10b981' }};" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    {{ $user->is_active ? 'Off' : 'On' }}
                                </button>
                            </form>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS PERMANEN akun petugas {{ $user->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline" style="padding: 6px 8px; font-size: 11.5px; color: #dc2626;" title="Hapus Akun">
                                    🗑️
                                </button>
                            </form>
                            @endif
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

    @if($users->hasPages())
    <div style="margin-top: 20px; border-top: 1px solid var(--card-border); padding-top: 14px;">
        {{ $users->links() }}
    </div>
    @endif
</div>

<!-- Modal Tambah Petugas Baru -->
<div id="modalAddUser" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 100; align-items: center; justify-content: center; backdrop-filter: blur(3px); padding: 20px;">
    <div style="background: #fff; width: 100%; max-width: 620px; border-radius: 16px; padding: 26px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 14px;">
            <div>
                <h3 style="font-size: 17px; font-weight: 800; color: #0f172a;">➕ Tambah Akun Petugas Baru</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Tentukan peran utama dan centang hak akses yang diizinkan</span>
            </div>
            <button type="button" onclick="closeModal('modalAddUser')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b;">✕</button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nama Lengkap Petugas *</label>
                <input type="text" name="name" required placeholder="Contoh: Budi Prasetyo, S.H.">
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
                    <label>Level Peran Utama *</label>
                    <select name="role" id="add_role" required onchange="applyRoleDefaultPermissions('add', this.value)">
                        <option value="kasir">Kasir POS Koperasi</option>
                        <option value="petugas_loket">Petugas Loket & Registrasi</option>
                        <option value="admin_koperasi">Admin Utama UPT & Koperasi</option>
                        <option value="kalapas">Kalapas / Kepala UPT</option>
                        <option value="auditor">Auditor & Pengawas</option>
                        <option value="kanwil_admin">Superadmin Kantor Wilayah</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Penempatan Satker UPT *</label>
                    <select name="upt_code" required>
                        @foreach($upts as $u)
                            <option value="{{ $u->upt_code }}">{{ $u->upt_name }} ({{ $u->upt_code }})</option>
                        @endforeach
                        <option value="LPS-01">Lapas Kelas I Tangerang (LPS-01)</option>
                    </select>
                </div>
            </div>

            <!-- Granular Permissions Checkboxes -->
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px; margin: 16px 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <strong style="font-size: 13px; color: #0f172a;">Centang Hak Akses Khusus Petugas:</strong>
                    <span style="font-size: 11px; color: #0284c7; cursor: pointer; font-weight: 700;" onclick="toggleAllPermissions('add', true)">Pilih Semua</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    @foreach($availablePermissions as $pKey => $pMeta)
                    <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-size: 12.5px; margin-bottom: 0; padding: 6px 8px; border-radius: 6px; background: #fff; border: 1px solid #e2e8f0;">
                        <input type="checkbox" name="permissions[{{ $pKey }}]" id="add_perm_{{ $pKey }}" value="1" style="width: auto; margin-top: 3px;">
                        <div>
                            <strong style="display: block; color: #1e293b; font-size: 12px;">{{ $pMeta['label'] }}</strong>
                            <span style="font-size: 10.5px; color: var(--text-muted); line-height: 1.1; display: block;">{{ $pMeta['desc'] }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label>Password Akun *</label>
                <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalAddUser')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Akun Petugas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Petugas -->
<div id="modalEditUser" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 100; align-items: center; justify-content: center; backdrop-filter: blur(3px); padding: 20px;">
    <div style="background: #fff; width: 100%; max-width: 620px; border-radius: 16px; padding: 26px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 14px;">
            <div>
                <h3 style="font-size: 17px; font-weight: 800; color: #0f172a;">✏️ Edit Data & Hak Akses Petugas</h3>
                <span style="font-size: 12px; color: var(--text-muted);" id="editUserSubTitle"></span>
            </div>
            <button type="button" onclick="closeModal('modalEditUser')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b;">✕</button>
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
                    <label>Level Peran Utama *</label>
                    <select id="edit_role" name="role" required onchange="applyRoleDefaultPermissions('edit', this.value)">
                        <option value="kasir">Kasir POS Koperasi</option>
                        <option value="petugas_loket">Petugas Loket & Registrasi</option>
                        <option value="admin_koperasi">Admin Utama UPT & Koperasi</option>
                        <option value="kalapas">Kalapas / Kepala UPT</option>
                        <option value="auditor">Auditor & Pengawas</option>
                        <option value="kanwil_admin">Superadmin Kantor Wilayah</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Penempatan Satker UPT *</label>
                    <select id="edit_upt_code" name="upt_code" required>
                        @foreach($upts as $u)
                            <option value="{{ $u->upt_code }}">{{ $u->upt_name }} ({{ $u->upt_code }})</option>
                        @endforeach
                        <option value="LPS-01">Lapas Kelas I Tangerang (LPS-01)</option>
                    </select>
                </div>
            </div>

            <!-- Granular Permissions Checkboxes -->
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px; margin: 16px 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <strong style="font-size: 13px; color: #0f172a;">Centang Hak Akses Khusus Petugas:</strong>
                    <span style="font-size: 11px; color: #0284c7; cursor: pointer; font-weight: 700;" onclick="toggleAllPermissions('edit', true)">Pilih Semua</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    @foreach($availablePermissions as $pKey => $pMeta)
                    <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-size: 12.5px; margin-bottom: 0; padding: 6px 8px; border-radius: 6px; background: #fff; border: 1px solid #e2e8f0;">
                        <input type="checkbox" name="permissions[{{ $pKey }}]" id="edit_perm_{{ $pKey }}" value="1" style="width: auto; margin-top: 3px;">
                        <div>
                            <strong style="display: block; color: #1e293b; font-size: 12px;">{{ $pMeta['label'] }}</strong>
                            <span style="font-size: 10.5px; color: var(--text-muted); line-height: 1.1; display: block;">{{ $pMeta['desc'] }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label>Ganti Password (Kosongkan jika tidak ingin diubah)</label>
                <input type="password" name="password" minlength="6" placeholder="Isi hanya jika ingin ubah password">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEditUser')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const roleDefaultPerms = {
        kanwil_admin: { wbp_manage: true, topup_manage: true, pos_checkout: true, inventory_manage: true, reports_finance: true, settings_app: true, users_manage: true },
        kalapas: { wbp_manage: true, topup_manage: true, pos_checkout: true, inventory_manage: true, reports_finance: true, settings_app: true, users_manage: true },
        admin_koperasi: { wbp_manage: true, topup_manage: true, pos_checkout: true, inventory_manage: true, reports_finance: true, settings_app: true, users_manage: true },
        petugas_loket: { wbp_manage: true, topup_manage: true, pos_checkout: false, inventory_manage: false, reports_finance: false, settings_app: false, users_manage: false },
        kasir: { wbp_manage: false, topup_manage: true, pos_checkout: true, inventory_manage: false, reports_finance: false, settings_app: false, users_manage: false },
        auditor: { wbp_manage: false, topup_manage: false, pos_checkout: false, inventory_manage: false, reports_finance: true, settings_app: false, users_manage: false }
    };

    function openModal(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'flex';
        if (id === 'modalAddUser') {
            applyRoleDefaultPermissions('add', document.getElementById('add_role').value);
        }
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    }

    function applyRoleDefaultPermissions(prefix, role) {
        const perms = roleDefaultPerms[role] || {};
        for (const [key, val] of Object.entries(perms)) {
            const chk = document.getElementById(prefix + '_perm_' + key);
            if (chk) chk.checked = !!val;
        }
    }

    function toggleAllPermissions(prefix, checkAll) {
        document.querySelectorAll(`input[id^="${prefix}_perm_"]`).forEach(el => el.checked = checkAll);
    }

    function openEditModal(user) {
        document.getElementById('formEditUser').action = "/users/" + user.id;
        document.getElementById('editUserSubTitle').innerText = user.name + " (" + user.email + ")";
        document.getElementById('edit_name').value = user.name || '';
        document.getElementById('edit_nip').value = user.nip || '';
        document.getElementById('edit_phone').value = user.phone || '';
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_role').value = user.role || 'kasir';
        document.getElementById('edit_upt_code').value = user.upt_code || 'LPS-01';

        // Set checkboxes
        const userPerms = user.permissions || roleDefaultPerms[user.role] || {};
        for (const key of Object.keys(roleDefaultPerms['kanwil_admin'])) {
            const chk = document.getElementById('edit_perm_' + key);
            if (chk) {
                chk.checked = !!userPerms[key];
            }
        }

        openModal('modalEditUser');
    }
</script>
@endsection
