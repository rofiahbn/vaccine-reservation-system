<?php
session_start();
include "../config.php";

// Cek koneksi database
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

date_default_timezone_set('Asia/Jakarta');

$current_page = 'staff.php';

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? trim($_GET['role']) : '';

// CEK STRUKTUR TABEL - Tampilkan struktur tabel untuk debugging
$debug_mode = false; // Set ke true untuk melihat struktur tabel

if ($debug_mode) {
    echo "<h3>Debug: Struktur Tabel</h3>";
    
    // Cek tabel staff
    $result = mysqli_query($conn, "DESCRIBE staff");
    if ($result) {
        echo "<h4>Tabel: staff</h4>";
        echo "<pre>";
        while ($row = mysqli_fetch_assoc($result)) {
            print_r($row);
        }
        echo "</pre>";
    }
    
    // Cek tabel tindakan
    $result = mysqli_query($conn, "DESCRIBE tindakan");
    if ($result) {
        echo "<h4>Tabel: tindakan</h4>";
        echo "<pre>";
        while ($row = mysqli_fetch_assoc($result)) {
            print_r($row);
        }
        echo "</pre>";
    }
    
    // Cek foreign key di tindakan
    $result = mysqli_query($conn, "SHOW CREATE TABLE tindakan");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "<h4>Create Table tindakan:</h4>";
        echo "<pre>" . $row['Create Table'] . "</pre>";
    }
    
    // Jangan lanjutkan eksekusi jika debug mode
    // exit;
}

// Asumsikan kolom yang menghubungkan staff dengan tindakan adalah 'dokter_id' atau 'petugas_id'
// Mari kita coba beberapa kemungkinan

// Query 1: Tanpa JOIN ke tindakan dulu (hanya staff)
$sql = "SELECT * FROM staff";

$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(nama_lengkap LIKE ? OR sip LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($role_filter) && in_array($role_filter, ['dokter', 'perawat', 'admin'])) {
    $where_conditions[] = "role = ?";
    $params[] = $role_filter;
    $types .= 's';
}

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY created_at DESC";

// Prepare statement dengan pengecekan error
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    die("Error preparing statement: " . mysqli_error($conn) . "<br>SQL: " . $sql);
}

if (!empty($params)) {
    // Binding parameter dengan metode yang benar untuk PHP versi lama
    $bind_params = array($stmt, $types);
    
    // Buat references untuk setiap parameter
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key]; // Pakai reference (&)
    }
    
    // Panggil dengan call_user_func_array
    call_user_func_array('mysqli_stmt_bind_param', $bind_params);
}

if (!mysqli_stmt_execute($stmt)) {
    die("Error executing statement: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);

if ($result === false) {
    die("Error getting result: " . mysqli_stmt_error($stmt));
}

$staff_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Hitung total pasien dan jam kerja per staff dengan query terpisah
$rate_per_jam = 50000;

foreach ($staff_list as $key => $staff) {
    $total_pasien = 0;
    $total_jam = 0;
    
    // CEK 1: Cari di tabel tindakan (jika ada kolom staff_id)
    $possible_columns = ['staff_id', 'dokter_id', 'petugas_id', 'user_id', 'created_by'];
    
    foreach ($possible_columns as $column) {
        // Cek apakah kolom ada di tabel tindakan
        $check_column = @mysqli_query($conn, "SHOW COLUMNS FROM tindakan LIKE '$column'");
        if ($check_column && mysqli_num_rows($check_column) > 0) {
            
            // Hitung total pasien unik dari tindakan
            $sql_pasien = "SELECT COUNT(DISTINCT patient_id) as total FROM tindakan WHERE $column = ?";
            $stmt_pasien = mysqli_prepare($conn, $sql_pasien);
            
            if ($stmt_pasien) {
                mysqli_stmt_bind_param($stmt_pasien, 'i', $staff['id']);
                mysqli_stmt_execute($stmt_pasien);
                $result_pasien = mysqli_stmt_get_result($stmt_pasien);
                if ($result_pasien) {
                    $data_pasien = mysqli_fetch_assoc($result_pasien);
                    $total_pasien = $data_pasien['total'] ?? 0;
                }
                mysqli_stmt_close($stmt_pasien);
            }
            
            // Hitung total jam kerja dari tindakan
            $sql_jam = "SELECT SUM(TIMESTAMPDIFF(HOUR, created_at, COALESCE(updated_at, created_at))) as total_jam
                       FROM tindakan 
                       WHERE $column = ?";
            $stmt_jam = mysqli_prepare($conn, $sql_jam);
            
            if ($stmt_jam) {
                mysqli_stmt_bind_param($stmt_jam, 'i', $staff['id']);
                mysqli_stmt_execute($stmt_jam);
                $result_jam = mysqli_stmt_get_result($stmt_jam);
                if ($result_jam) {
                    $data_jam = mysqli_fetch_assoc($result_jam);
                    $total_jam = $data_jam['total_jam'] ?? 0;
                }
                mysqli_stmt_close($stmt_jam);
            }
            
            break; // Keluar dari loop setelah ketemu kolom
        }
    }
    
    // CEK 2: Jika tidak ada di tindakan, cek di tabel booking_staff
    if ($total_pasien == 0) {
        // Cek apakah tabel booking_staff ada
        $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'booking_staff'");
        if (mysqli_num_rows($check_table) > 0) {
            $sql_booking = "SELECT COUNT(DISTINCT b.patient_id) as total
                           FROM booking_staff bs
                           JOIN bookings b ON bs.booking_id = b.id
                           WHERE bs.staff_id = ?";
            
            $stmt_booking = mysqli_prepare($conn, $sql_booking);
            if ($stmt_booking) {
                mysqli_stmt_bind_param($stmt_booking, 'i', $staff['id']);
                mysqli_stmt_execute($stmt_booking);
                $result_booking = mysqli_stmt_get_result($stmt_booking);
                if ($result_booking) {
                    $data_booking = mysqli_fetch_assoc($result_booking);
                    $total_pasien = $data_booking['total'] ?? 0;
                }
                mysqli_stmt_close($stmt_booking);
            }
        }
    }
    
    // CEK 3: Jika masih 0, cek di tabel bookings (hanya jika kolom ada)
    if ($total_pasien == 0) {
        // Cek apakah kolom 'created_by' benar-benar ada di tabel 'bookings'
        $check_created_by = @mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'created_by'");
        
        if ($check_created_by && mysqli_num_rows($check_created_by) > 0) {
            $sql_created = "SELECT COUNT(DISTINCT patient_id) as total
                           FROM bookings 
                           WHERE created_by = ?";
            
            $stmt_created = mysqli_prepare($conn, $sql_created);
            if ($stmt_created) {
                mysqli_stmt_bind_param($stmt_created, 'i', $staff['id']);
                mysqli_stmt_execute($stmt_created);
                $result_created = mysqli_stmt_get_result($stmt_created);
                if ($result_created) {
                    $data_created = mysqli_fetch_assoc($result_created);
                    $total_pasien = $data_created['total'] ?? 0;
                }
                mysqli_stmt_close($stmt_created);
            }
        }
    }
    
    // Simpan ke array
    $staff_list[$key]['total_pasien'] = $total_pasien;
    $staff_list[$key]['total_jam_kerja'] = $total_jam;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Staff - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/staff.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Debug info */
        .debug-panel {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 13px;
        }
        .debug-panel h4 {
            margin: 0 0 10px 0;
            color: #0369a1;
        }
        .debug-panel pre {
            background: #fff;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 5px 0;
        }
        .debug-hide {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="vaksinin-logo.png" alt="Vaksinin" class="logo-full">
            <img src="v-logo.png" alt="V" class="logo-icon">
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="javascript:void(0)" 
                class="nav-item has-submenu <?= (basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'products_pelayanan.php') ? 'active open' : '' ?>" 
                onclick="toggleSubmenu(this)">
                <i class="fas fa-capsules"></i>
                <span>Produk</span>
                <i class="fas fa-chevron-down arrow"></i>
            </a>
            
            <ul class="submenu <?= (basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'products_pelayanan.php') ? 'open' : '' ?>">
                <li>
                    <a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
                        Vaksin & Obat
                    </a>
                </li>
                <li>
                    <a href="products_jasa.php" class="<?= $current_page == 'products_jasa.php' ? 'active' : '' ?>">
                    Jasa
                    </a>
                </li>
                <li>
                    <a href="products_pelayanan.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products_pelayanan.php' ? 'active' : '' ?>">
                        Pelayanan/Paket
                    </a>
                </li>
            </ul>
            
            <a href="patients.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'patients.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Pasien</span>
            </a>

            <a href="staff.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'staff.php' ? 'active' : '' ?>">
                <i class="fas fa-user-md"></i>
                <span>Staff</span>
            </a>
            
            <a href="calendar_setting.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Kalender</span>
            </a>
            
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Debug Panel (hidden by default) -->
        <div class="debug-panel debug-hide">
            <h4>🔧 Debug Info</h4>
            <div>
                <strong>Total Staff:</strong> <?= count($staff_list) ?><br>
                <strong>Search:</strong> <?= htmlspecialchars($search) ?: '-' ?><br>
                <strong>Role Filter:</strong> <?= htmlspecialchars($role_filter) ?: '-' ?><br>
                <button onclick="this.parentElement.parentElement.classList.add('debug-hide')">Tutup</button>
                <button onclick="this.parentElement.parentElement.classList.remove('debug-hide')">Tampilkan</button>
            </div>
        </div>

        <div class="staff-container">
            <!-- Header dengan tombol tambah -->
            <div class="page-header">
                <h1 class="page-title">
                    Manajemen Staff
                </h1>
                <div class="header-actions">
                    <button class="btn-add-staff" onclick="tambahStaff()">
                        <i class="fas fa-plus"></i>
                        Tambah Staff
                    </button>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="search-filter-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           id="searchInput" 
                           placeholder="Cari nama atau SIP..." 
                           value="<?= htmlspecialchars($search) ?>" 
                           onkeyup="handleSearch()">
                </div>
                
                <select id="roleFilter" class="filter-dropdown" onchange="handleFilter()">
                    <option value="">Semua Role</option>
                    <option value="dokter" <?= $role_filter == 'dokter' ? 'selected' : '' ?>>Dokter</option>
                    <option value="perawat" <?= $role_filter == 'perawat' ? 'selected' : '' ?>>Perawat</option>
                    <option value="admin" <?= $role_filter == 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                
                <button class="btn-reset" onclick="resetFilters()">
                    <i class="fas fa-redo-alt"></i> Reset
                </button>
            </div>

            <!-- Staff Table -->
            <div class="table-container">
                <?php if (!empty($staff_list)): ?>
                    <div class="table-responsive">
                        <table class="staff-table">
                            <thead>
                                <tr>
                                    <th>Nama Staff</th>
                                    <th>SIP / NIP</th>
                                    <th>Role</th>
                                    <th>Total Pasien</th>
                                    <th>Total Jam Kerja</th>
                                    <th>Total Gaji</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staff_list as $staff): 
                                    // Hitung total gaji berdasarkan jam kerja
                                    $total_gaji = ($staff['total_jam_kerja'] ?? 0) * $rate_per_jam;
                                ?>
                                    <tr data-staff-id="<?= $staff['id'] ?>">
                                        <td>
                                            <div class="staff-name">
                                                <strong><?= htmlspecialchars($staff['nama_lengkap'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                                                <?php if (!empty($staff['gelar'])): ?>
                                                    <div class="staff-gelar">
                                                        <small><?= htmlspecialchars($staff['gelar'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($staff['sip'])): ?>
                                                <span class="sip-number"><?= htmlspecialchars($staff['sip'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php else: ?>
                                                <span class="no-data">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge-role <?= $staff['role'] ?? '' ?>">
                                                <?= ucfirst($staff['role'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="stat-number"><?= number_format($staff['total_pasien'] ?? 0) ?></span>
                                            <span class="stat-label">pasien</span>
                                        </td>
                                        <td>
                                            <?php if (!empty($staff['total_jam_kerja']) && $staff['total_jam_kerja'] > 0): ?>
                                                <span class="stat-number">
                                                    <?= floor($staff['total_jam_kerja']) ?> <small>jam</small>
                                                </span>
                                            <?php else: ?>
                                                <span class="no-data">0 jam</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="salary-amount">
                                                Rp <?= number_format($total_gaji, 0, ',', '.') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" 
                                                        class="btn-edit" 
                                                        data-id="<?= $staff['id'] ?>"
                                                        data-nama="<?= htmlspecialchars($staff['nama_lengkap'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-gelar="<?= htmlspecialchars($staff['gelar'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-sip="<?= htmlspecialchars($staff['sip'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-role="<?= $staff['role'] ?? '' ?>"
                                                        onclick="editStaff(this)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" 
                                                        class="btn-delete" 
                                                        data-id="<?= $staff['id'] ?>"
                                                        data-nama="<?= htmlspecialchars($staff['nama_lengkap'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        onclick="hapusStaff(this)">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Info jumlah data -->
                    <div class="table-footer">
                        <div class="total-count">
                            <i class="fas fa-user-md"></i>
                            <span>Total: <?= count($staff_list) ?> staff</span>
                        </div>
                        <div class="table-info">
                            <i class="fas fa-info-circle"></i>
                            <span>Rate per jam: Rp <?= number_format($rate_per_jam, 0, ',', '.') ?></span>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <i class="fas fa-user-md-slash"></i>
                        <h3>Belum ada data staff</h3>
                        <p>Tidak ditemukan staff dengan kriteria yang dipilih</p>
                        <button class="btn-refresh" onclick="resetFilters()">
                            <i class="fas fa-redo-alt"></i> Tampilkan semua staff
                        </button>
                        <button class="btn-add-staff-empty" onclick="tambahStaff()">
                            <i class="fas fa-plus"></i> Tambah Staff Baru
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Staff -->
    <div id="staffModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Tambah Staff Baru</h2>
                <button class="modal-close" onclick="tutupModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="staffForm" onsubmit="simpanStaff(event)">
                <input type="hidden" id="staffId" name="id">
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required 
                           placeholder="Masukkan nama lengkap">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="gelar">Gelar</label>
                        <input type="text" id="gelar" name="gelar" 
                               placeholder="Contoh: dr., Sp.A">
                    </div>
                    
                    <div class="form-group">
                        <label for="sip">SIP / NIP</label>
                        <input type="text" id="sip" name="sip" 
                               placeholder="Nomor SIP atau NIP">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role">Role <span class="required">*</span></label>
                    <select id="role" name="role" required>
                        <option value="">Pilih Role</option>
                        <option value="dokter">Dokter</option>
                        <option value="perawat">Perawat</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-batal" onclick="tutupModal()">
                        Batal
                    </button>
                    <button type="submit" class="btn-simpan">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/sidebar-toggle.js"></script>
    <script>
        let searchTimeout;
        
        // Search with debounce
        function handleSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(performSearch, 500);
        }
        
        function performSearch() {
            const search = document.getElementById('searchInput').value.trim();
            const role = document.getElementById('roleFilter').value;
            
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (role) params.set('role', role);
            
            window.location.href = `staff.php?${params.toString()}`;
        }
        
        function handleFilter() {
            performSearch();
        }
        
        function resetFilters() {
            window.location.href = 'staff.php';
        }
        
        // Modal functions
        function tambahStaff() {
            console.log('Tambah staff dipanggil');
            
            // Cek element
            const modalTitle = document.getElementById('modalTitle');
            const staffForm = document.getElementById('staffForm');
            const staffId = document.getElementById('staffId');
            const staffModal = document.getElementById('staffModal');
            
            if (!modalTitle || !staffForm || !staffId || !staffModal) {
                alert('Terjadi kesalahan: Form tidak ditemukan');
                return;
            }
            
            // Reset form
            modalTitle.textContent = 'Tambah Staff Baru';
            staffForm.reset();
            staffId.value = '';
            
            // Tampilkan modal
            staffModal.style.display = 'flex';
        }
        
        function editStaff(button) {
            console.log('Edit staff dipanggil', button);
            
            // Ambil data dari attribute tombol
            const id = button.getAttribute('data-id');
            const nama = button.getAttribute('data-nama');
            const gelar = button.getAttribute('data-gelar');
            const sip = button.getAttribute('data-sip');
            const role = button.getAttribute('data-role');
            
            console.log('Data staff:', {id, nama, gelar, sip, role});
            
            // Validasi data
            if (!id) {
                alert('ID staff tidak ditemukan');
                return;
            }
            
            // Cek apakah element-element form ada
            const elements = {
                modalTitle: document.getElementById('modalTitle'),
                staffId: document.getElementById('staffId'),
                nama_lengkap: document.getElementById('nama_lengkap'),
                gelar: document.getElementById('gelar'),
                sip: document.getElementById('sip'),
                role: document.getElementById('role'),
                staffModal: document.getElementById('staffModal')
            };
            
            // Validasi semua element
            for (let [key, element] of Object.entries(elements)) {
                if (!element) {
                    console.error(`Element ${key} tidak ditemukan!`);
                    alert(`Terjadi kesalahan: Form tidak lengkap (${key})`);
                    return;
                }
            }
            
            // Isi form modal
            elements.modalTitle.textContent = 'Edit Staff';
            elements.staffId.value = id;
            elements.nama_lengkap.value = nama;
            elements.gelar.value = gelar || '';
            elements.sip.value = sip || '';
            elements.role.value = role;
            
            // Tampilkan modal
            elements.staffModal.style.display = 'flex';
            
            // Notifikasi
            showNotification('Data staff siap diedit', 'success');
        }
        
        function hapusStaff(button) {
            // Ambil data dari attribute tombol
            const id = button.getAttribute('data-id');
            const nama = button.getAttribute('data-nama');
            
            console.log('Hapus staff:', {id, nama});
            
            if (!id) {
                alert('ID staff tidak ditemukan');
                return;
            }
            
            if (confirm(`Yakin ingin menghapus staff ${nama}?`)) {
                // Show loading state
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
                button.disabled = true;
                
                // Send delete request
                fetch('delete_staff.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(`Staff ${nama} berhasil dihapus`, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification('Gagal menghapus staff: ' + (data.message || ''), 'error');
                        button.innerHTML = '<i class="fas fa-trash"></i> Hapus';
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Terjadi kesalahan', 'error');
                    button.innerHTML = '<i class="fas fa-trash"></i> Hapus';
                    button.disabled = false;
                });
            }
        }
        
        function tutupModal() {
            const modal = document.getElementById('staffModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }
        
        function simpanStaff(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());
            
            // Show loading
            const submitBtn = event.target.querySelector('.btn-simpan');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            submitBtn.disabled = true;
            
            // Send data to server
            fetch('save_staff.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(
                        data.data.id ? 'Staff berhasil diperbarui' : 'Staff baru berhasil ditambahkan', 
                        'success'
                    );
                    tutupModal();
                    // Refresh halaman setelah 1.5 detik
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification('Gagal menyimpan data: ' + (data.message || ''), 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Terjadi kesalahan', 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('staffModal');
            if (event.target == modal) {
                tutupModal();
            }
        }
        
        function showNotification(message, type = 'info') {
            // Remove existing notification
            const existing = document.querySelector('.notification');
            if (existing) existing.remove();
            
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            
            let icon = 'info-circle';
            if (type === 'success') icon = 'check-circle';
            if (type === 'error') icon = 'exclamation-circle';
            
            notification.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            `;
            
            // Add to page
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
        
        // Toggle debug panel
        function toggleDebug() {
            const panel = document.querySelector('.debug-panel');
            panel.classList.toggle('debug-hide');
        }
    </script>
</body>
</html>