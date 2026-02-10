<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Jakarta');

$current_page = 'patients.php';

// Get filter parameters dengan filter_input untuk keamanan
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '';
$jenis_kelamin_filter = filter_input(INPUT_GET, 'jenis_kelamin', FILTER_SANITIZE_STRING) ?? '';
$kategori_filter = filter_input(INPUT_GET, 'kategori', FILTER_SANITIZE_STRING) ?? '';

// Build query untuk get pasien dengan tindakan terbaru
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(p.nama_lengkap LIKE ? OR p.no_rekam_medis LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($jenis_kelamin_filter) && in_array($jenis_kelamin_filter, ['L', 'P'])) {
    $where_conditions[] = "p.jenis_kelamin = ?";
    $params[] = $jenis_kelamin_filter;
    $types .= 's';
}

if (!empty($kategori_filter) && in_array($kategori_filter, ['Anak', 'Dewasa'])) {
    $where_conditions[] = "p.kategori_usia = ?";
    $params[] = $kategori_filter;
    $types .= 's';
}

$where_sql = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : '';

// Query untuk get pasien dengan info tindakan terbaru
$sql = "
    SELECT 
        p.id,
        p.no_rekam_medis,
        p.nama_lengkap,
        p.tanggal_lahir,
        p.usia,
        p.jenis_kelamin,
        p.kategori_usia,
        t.id as tindakan_id,
        t.booking_id,
        GROUP_CONCAT(DISTINCT bs.nama_layanan SEPARATOR ', ') as layanan,
        t.kedatangan_ke,
        t.kedatangan_selanjutnya,
        t.status as status_tindakan,
        b.tanggal_booking,
        b.waktu_booking
    FROM patients p
    LEFT JOIN tindakan t ON p.id = t.patient_id
    LEFT JOIN bookings b ON t.booking_id = b.id
    LEFT JOIN booking_services bs ON b.id = bs.booking_id
    $where_sql
    GROUP BY p.id, t.id
    HAVING t.id IS NOT NULL
    ORDER BY t.created_at DESC
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$patients = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasien Aktif - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/patients.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                class="nav-item has-submenu <?= in_array($current_page, ['products.php','products_pelayanan.php']) ? 'active open' : '' ?>" 
                onclick="toggleSubmenu(this)">
                <i class="fas fa-capsules"></i>
                <span>Produk</span>
                <i class="fas fa-chevron-down arrow"></i>
            </a>
            
            <ul class="submenu <?= in_array($current_page, ['products.php','products_pelayanan.php']) ? 'open' : '' ?>">
                <li>
                    <a href="products.php" class="<?= $current_page == 'products.php' ? 'active' : '' ?>">
                        Stok
                    </a>
                </li>
                <li>
                    <a href="products_pelayanan.php" class="<?= $current_page == 'products_pelayanan.php' ? 'active' : '' ?>">
                        Pelayanan/Paket
                    </a>
                </li>
            </ul>
            
            <a href="patients.php" class="nav-item <?= $current_page == 'patients.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Pasien</span>
            </a>
            
            <a href="calendar_setting.php" class="nav-item">
                <i class="fas fa-calendar"></i>
                <span>Kalender</span>
            </a>
            
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="#" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="patients-container">
            <h1 class="page-title">
                Pasien Aktif
            </h1>

            <!-- Search and Filter Section -->
            <div class="search-filter-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           id="searchInput" 
                           placeholder="Cari nama atau no. rekam medis..." 
                           value="<?= htmlspecialchars($search) ?>" 
                           onkeyup="handleSearch()">
                </div>
                
                <select id="jenisKelaminFilter" class="filter-dropdown" onchange="handleFilter()">
                    <option value="">Semua Jenis Kelamin</option>
                    <option value="L" <?= $jenis_kelamin_filter == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= $jenis_kelamin_filter == 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>

                <select id="kategoriFilter" class="filter-dropdown" onchange="handleFilter()">
                    <option value="">Semua Kategori</option>
                    <option value="Anak" <?= $kategori_filter == 'Anak' ? 'selected' : '' ?>>Anak</option>
                    <option value="Dewasa" <?= $kategori_filter == 'Dewasa' ? 'selected' : '' ?>>Dewasa</option>
                </select>
                
                <button class="btn-reset" onclick="resetFilters()">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>

            <!-- Patients Table -->
            <div class="table-container">
                <?php if (!empty($patients)): ?>
                    <div class="table-responsive">
                        <table class="patients-table">
                            <thead>
                                <tr>
                                    <th>Nama Pasien</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Usia</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Layanan</th>
                                    <th>Kedatangan</th>
                                    <th>Kedatangan Selanjutnya</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $patient): ?>
                                    <tr data-patient-id="<?= $patient['id'] ?>">
                                        <td>
                                            <div class="patient-name">
                                                <strong><?= htmlspecialchars($patient['nama_lengkap']) ?></strong>
                                                <?php if (!empty($patient['no_rekam_medis'])): ?>
                                                    <div class="patient-mrn">
                                                        <small><?= htmlspecialchars($patient['no_rekam_medis']) ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?= !empty($patient['tanggal_lahir']) ? date('d/m/Y', strtotime($patient['tanggal_lahir'])) : '-' ?></td>
                                        <td>
                                            <span class="badge-age"><?= $patient['usia'] ?> tahun</span>
                                        </td>
                                        <td>
                                            <span class="badge-gender <?= $patient['jenis_kelamin'] == 'L' ? 'badge-male' : 'badge-female' ?>">
                                                <?= $patient['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="service-list"><?= htmlspecialchars($patient['layanan'] ?: '-') ?></span>
                                        </td>
                                        <td>
                                            <span class="visit-count">
                                                <?= $patient['kedatangan_ke'] ?>/<?= ($patient['kedatangan_ke'] ?? 0) + 2 ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($patient['kedatangan_selanjutnya'])): ?>
                                                <div class="next-visit">
                                                    <i class="fas fa-calendar-day"></i>
                                                    <span><?= date('d M Y', strtotime($patient['kedatangan_selanjutnya'])) ?></span>
                                                    <?php 
                                                    $next_visit = strtotime($patient['kedatangan_selanjutnya']);
                                                    $today = time();
                                                    $diff_days = floor(($next_visit - $today) / (60 * 60 * 24));
                                                    
                                                    if ($diff_days <= 7 && $diff_days >= 0): ?>
                                                        <span class="badge-soon">Segera</span>
                                                    <?php elseif ($diff_days < 0): ?>
                                                        <span class="badge-overdue">Terlambat</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="no-visit">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" 
                                                        class="btn-reminder" 
                                                        onclick="ingatkanPasien(<?= $patient['id'] ?>, '<?= htmlspecialchars($patient['nama_lengkap']) ?>')">
                                                    <i class="fas fa-bell"></i> Ingatkan
                                                </button>
                                                <button type="button" 
                                                        class="btn-view" 
                                                        onclick="window.location.href='patient_detail.php?id=<?= $patient['id'] ?>'">
                                                    <i class="fas fa-eye"></i> Detail
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
                            <i class="fas fa-user-check"></i>
                            <span>Total: <?= count($patients) ?> pasien aktif</span>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <h3>Belum ada data pasien aktif</h3>
                        <p>Tidak ditemukan pasien dengan kriteria yang dipilih</p>
                        <button class="btn-refresh" onclick="resetFilters()">
                            <i class="fas fa-redo"></i> Tampilkan semua pasien
                        </button>
                    </div>
                <?php endif; ?>
            </div>
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
            const jenisKelamin = document.getElementById('jenisKelaminFilter').value;
            const kategori = document.getElementById('kategoriFilter').value;
            
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (jenisKelamin) params.set('jenis_kelamin', jenisKelamin);
            if (kategori) params.set('kategori', kategori);
            
            window.location.href = `patients.php?${params.toString()}`;
        }
        
        function handleFilter() {
            performSearch();
        }
        
        function resetFilters() {
            window.location.href = 'patients.php';
        }
        
        function ingatkanPasien(patientId, patientName) {
            if (confirm(`Kirim pengingatan kepada ${patientName}?`)) {
                // Show loading state
                const buttons = document.querySelectorAll(`[data-patient-id="${patientId}"] .btn-reminder`);
                buttons.forEach(btn => {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
                    btn.disabled = true;
                    
                    // Simulate API call
                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                        btn.disabled = false;
                        
                        // Show success notification
                        showNotification(`Pengingatan berhasil dikirim ke ${patientName}`, 'success');
                    }, 1500);
                });
            }
        }
        
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
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
        
    </script>
</body>
</html>