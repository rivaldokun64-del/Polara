<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="polara-breadcrumb">
                <a href="<?= site_url('dashboard') ?>">Dashboard</a>
                <span class="sep">/</span>
                <span class="current">Manajemen Pengguna</span>
            </div>
            <h1 class="page-title-text">Manajemen Pengguna</h1>
            <p class="page-subtitle-text">Kelola hak akses akun Administrator, Analis, dan Pimpinan sistem Polara.</p>
        </div>
        <button type="button" class="btn-coral" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="bi bi-person-plus"></i> Tambah Pengguna
        </button>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-coral"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-num"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">Total Akun Terdaftar</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue"><i class="bi bi-shield-check"></i></div>
            <div>
                <div class="stat-num"><?= number_format($stats['admin']) ?></div>
                <div class="stat-label">Administrator</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-navy"><i class="bi bi-graph-up-arrow"></i></div>
            <div>
                <div class="stat-num"><?= number_format($stats['analis']) ?></div>
                <div class="stat-label">Analis Data Medis</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-cream"><i class="bi bi-briefcase"></i></div>
            <div>
                <div class="stat-num"><?= number_format($stats['pimpinan']) ?></div>
                <div class="stat-label">Pimpinan / Eksekutif</div>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card-p">
    <div class="card-p-header">
        <h2 class="card-p-title" style="font-size:14.5px;">
            <i class="bi bi-person-badge"></i> Daftar Akun Pengguna Sistem
        </h2>
        <span style="font-size:12.5px; color:var(--muted);"><?= count($users) ?> pengguna terdata</span>
    </div>
    <div class="table-responsive">
        <table class="table-polara">
            <thead>
                <tr>
                    <th style="padding-left:20px;">Nama &amp; ID</th>
                    <th>Username</th>
                    <th>Peran</th>
                    <th>Status</th>
                    <th>Terdaftar Sejak</th>
                    <th style="text-align:right; padding-right:20px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td style="padding-left:20px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:34px;height:34px;border-radius:50%;background:var(--coral-light);border:1px solid var(--peach);display:flex;align-items:center;justify-content:center;font-size:15px;color:var(--coral);font-weight:700;flex-shrink:0;">
                                <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight:600; color:var(--navy); font-size:13.5px;"><?= esc($u['nama']) ?></div>
                                <div style="font-size:11.5px; color:var(--muted);">ID #<?= esc($u['id']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <code style="font-size:12.5px; background:var(--navy-06); padding:2px 7px; border-radius:4px; color:var(--navy);"><?= esc($u['username']) ?></code>
                    </td>
                    <td>
                        <span class="badge-role badge-role-<?= esc($u['role']) ?>"><?= esc($u['role']) ?></span>
                    </td>
                    <td>
                        <?php if ($u['status'] === 'aktif'): ?>
                        <span class="badge-status badge-siap">
                            <i class="bi bi-check-circle-fill" style="font-size:10px;"></i> Aktif
                        </span>
                        <?php else: ?>
                        <span class="badge-status badge-error">
                            <i class="bi bi-x-circle-fill" style="font-size:10px;"></i> Nonaktif
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="font-size:12.5px; color:var(--muted);">
                            <?= !empty($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '-' ?>
                        </span>
                    </td>
                    <td style="text-align:right; padding-right:16px;">
                        <div style="display:inline-flex; align-items:center; gap:6px;">
                            <button type="button"
                                class="btn-ghost"
                                style="font-size:12.5px; padding:6px 12px; color:var(--navy); border-color:var(--border);"
                                onclick="openEditUserModal(
                                    '<?= $u['id'] ?>',
                                    '<?= esc($u['nama'], 'js') ?>',
                                    '<?= esc($u['username'], 'js') ?>',
                                    '<?= esc($u['role'], 'js') ?>'
                                )">
                                <i class="bi bi-pencil-square" style="color:var(--coral);"></i> Edit
                            </button>

                            <?php if ($u['id'] == session()->get('user_id')): ?>
                            <span style="font-size:12px; color:var(--muted); font-weight:600; background:var(--navy-06); padding:4px 10px; border-radius:20px; border:1px solid var(--border);">
                                <i class="bi bi-person-check" style="color:var(--coral);"></i> Anda
                            </span>
                            <?php else: ?>
                            <button type="button"
                                class="btn-ghost"
                                style="font-size:12.5px; padding:6px 12px; <?= ($u['status'] === 'aktif') ? 'color:#DC2626;border-color:#FECACA;' : 'color:#166534;border-color:#BBF7D0;' ?>"
                                onclick="confirmToggleStatus(
                                    '<?= site_url('users/toggle-status/' . $u['id']) ?>',
                                    '<?= esc($u['username'], 'js') ?>',
                                    '<?= esc($u['nama'], 'js') ?>',
                                    '<?= $u['status'] === 'aktif' ? 'nonaktif' : 'aktif' ?>'
                                )">
                                <i class="bi <?= ($u['status'] === 'aktif') ? 'bi-person-slash' : 'bi-person-check' ?>"></i>
                                <?= ($u['status'] === 'aktif') ? 'Nonaktifkan' : 'Aktifkan' ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>

<!-- Toggle Status Confirm Modal -->
<div class="modal fade" id="modalToggleStatus" tabindex="-1" aria-labelledby="modalToggleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center px-4 pt-5 pb-4">
                <div class="confirm-icon-wrap" id="toggleIconWrap" style="margin:0 auto 16px;"></div>
                <h6 class="fw-bold" id="toggleModalTitle" style="color:var(--navy); font-size:15px;"></h6>
                <p class="text-muted" id="toggleModalBody" style="font-size:13px; margin:0;"></p>
            </div>
            <div class="modal-footer justify-content-center gap-2 pb-4 border-0 pt-0">
                <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="toggleConfirmBtn" class="btn-coral">Konfirmasi</a>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-labelledby="modalTambahUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px;height:40px;border-radius:10px;background:var(--coral-light);display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--coral);">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalTambahUserLabel" style="color:var(--navy);">Tambah Akun Pengguna</h5>
                        <div style="font-size:12px; color:var(--muted);">Buat akun baru untuk sistem Polara</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('users/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body" style="padding:24px;">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap <span style="color:var(--coral);">*</span></label>
                        <input type="text" name="nama" id="nama" class="form-control" placeholder="Contoh: dr. Budi Santoso" required value="<?= old('nama') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span style="color:var(--coral);">*</span></label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Contoh: budi_santoso" required value="<?= old('username') ?>">
                        <div style="font-size:12px; color:var(--muted); margin-top:4px;">Hanya huruf, angka, dan underscore.</div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Kata Sandi <span style="color:var(--coral);">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
                    </div>
                    <div class="mb-0">
                        <label for="role" class="form-label">Peran Pengguna <span style="color:var(--coral);">*</span></label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="analis" selected>Analis — Akses Dataset &amp; Data Mining</option>
                            <option value="pimpinan">Pimpinan — Akses Laporan &amp; Ringkasan Eksekutif</option>
                            <option value="admin">Administrator — Akses Penuh Sistem &amp; Pengguna</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-coral">
                        <i class="bi bi-person-check"></i> Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pengguna -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-labelledby="modalEditUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditUserLabel">
                    <i class="bi bi-person-gear"></i> Edit Akun Pengguna
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form action="" method="post" id="formEditUser">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" id="edit_username" class="form-control" disabled style="background:var(--navy-06); font-family:monospace; color:var(--navy);">
                        <div class="form-text">Username bersifat permanen dan tidak dapat diubah.</div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_nama" class="form-label">Nama Lengkap <span style="color:var(--coral);">*</span></label>
                        <input type="text" name="nama" id="edit_nama" class="form-control" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Peran Pengguna (Role) <span style="color:var(--coral);">*</span></label>
                        <select name="role" id="edit_role" class="form-select" required>
                            <option value="analis">Analis — Akses Dataset &amp; Data Mining</option>
                            <option value="pimpinan">Pimpinan — Akses Laporan &amp; Ringkasan Eksekutif</option>
                            <option value="admin">Administrator — Akses Penuh Sistem &amp; Pengguna</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label for="edit_password" class="form-label">Kata Sandi Baru (Opsional)</label>
                        <input type="password" name="password" id="edit_password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti kata sandi" minlength="6">
                        <div class="form-text">Isi minimal 6 karakter hanya jika Anda ingin mengubah kata sandi akun ini.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-coral">
                        <i class="bi bi-check-circle"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.openEditUserModal = function(id, nama, username, role) {
    const form = document.getElementById('formEditUser');
    form.action = '<?= site_url('users/update') ?>/' + id;

    document.getElementById('edit_username').value = username;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_password').value = '';

    new bootstrap.Modal(document.getElementById('modalEditUser')).show();
};

window.confirmToggleStatus = function(url, username, nama, targetStatus) {
    const isDeactivate = targetStatus === 'nonaktif';
    const iconWrap = document.getElementById('toggleIconWrap');
    const title    = document.getElementById('toggleModalTitle');
    const body     = document.getElementById('toggleModalBody');
    const btn      = document.getElementById('toggleConfirmBtn');

    iconWrap.className = 'confirm-icon-wrap ' + (isDeactivate ? 'confirm-icon-danger' : 'confirm-icon-info');
    iconWrap.innerHTML = '<i class="bi bi-person-' + (isDeactivate ? 'slash' : 'check') + '"></i>';
    iconWrap.style.cssText = 'width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px;';

    title.textContent = (isDeactivate ? 'Nonaktifkan' : 'Aktifkan') + ' akun "' + username + '"?';
    body.textContent  = 'Akun milik ' + nama + ' akan ' +
        (isDeactivate
            ? 'dinonaktifkan dan tidak dapat login hingga diaktifkan kembali.'
            : 'diaktifkan kembali dan dapat login ke sistem Polara.');
    btn.href = url;
    btn.style.background = isDeactivate ? '#DC2626' : 'var(--coral)';

    new bootstrap.Modal(document.getElementById('modalToggleStatus')).show();
};
</script>
<?= $this->endSection() ?>

