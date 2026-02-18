<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Jakarta');

$current_page = 'patients.php';

// Get patient ID from URL
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get all patients for sidebar
$query_all_patients = "SELECT id, nama_lengkap FROM patients ORDER BY nama_lengkap ASC";
$result_all_patients = mysqli_query($conn, $query_all_patients);

// Get selected patient detail
$patient_detail = null;
$patient_emails = [];
$patient_phones = [];

if ($patient_id > 0) {
    $query_detail = "
        SELECT 
            p.*,
            GROUP_CONCAT(DISTINCT bs.nama_layanan SEPARATOR ', ') as layanan,
            (SELECT alamat FROM patient_addresses WHERE patient_id = p.id AND is_primary = 1 LIMIT 1) as alamat_primary,
            (SELECT provinsi FROM patient_addresses WHERE patient_id = p.id AND is_primary = 1 LIMIT 1) as provinsi,
            (SELECT kota FROM patient_addresses WHERE patient_id = p.id AND is_primary = 1 LIMIT 1) as kota
        FROM patients p
        LEFT JOIN tindakan t ON p.id = t.patient_id
        LEFT JOIN bookings b ON t.booking_id = b.id
        LEFT JOIN booking_services bs ON b.id = bs.booking_id
        WHERE p.id = ?
        GROUP BY p.id
    ";
    $stmt = $conn->prepare($query_detail);
    $stmt->bind_param('i', $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient_detail = $result->fetch_assoc();
    
    // Get all emails
    $query_emails = "SELECT email, is_primary FROM patient_emails WHERE patient_id = ? ORDER BY is_primary DESC, id ASC";
    $stmt = $conn->prepare($query_emails);
    $stmt->bind_param('i', $patient_id);
    $stmt->execute();
    $result_emails = $stmt->get_result();
    while ($email = $result_emails->fetch_assoc()) {
        $patient_emails[] = $email;
    }
    
    // Get all phones
    $query_phones = "SELECT phone, is_primary FROM patient_phones WHERE patient_id = ? ORDER BY is_primary DESC, id ASC";
    $stmt = $conn->prepare($query_phones);
    $stmt->bind_param('i', $patient_id);
    $stmt->execute();
    $result_phones = $stmt->get_result();
    while ($phone = $result_phones->fetch_assoc()) {
        $patient_phones[] = $phone;
    }
}

// Fungsi untuk menghitung usia dengan bulan
function calculateAgeWithMonths($tanggal_lahir) {
    if (empty($tanggal_lahir) || $tanggal_lahir == '0000-00-00') {
        return '-';
    }
    
    $birthDate = new DateTime($tanggal_lahir);
    $today = new DateTime();
    
    if ($birthDate > $today) {
        return '-';
    }
    
    $diff = $today->diff($birthDate);
    $years = $diff->y;
    $months = $diff->m;
    
    if ($years == 0 && $months == 0) {
        $days = $diff->d;
        return "$days hari";
    }
    
    $result = '';
    if ($years > 0) {
        $result .= "$years tahun";
    }
    if ($months > 0) {
        if ($years > 0) {
            $result .= " ";
        }
        $result .= "$months bulan";
    }
    
    return $result;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Lengkap Pasien - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/patient_detail.css">
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
                class="nav-item has-submenu" 
                onclick="toggleSubmenu(this)">
                <i class="fas fa-capsules"></i>
                <span>Produk</span>
                <i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="products.php">Stok</a></li>
                <li><a href="products_pelayanan.php">Pelayanan/Paket</a></li>
            </ul>
            <a href="patients.php" class="nav-item">
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
        <div class="patient-detail-container">
            <!-- Patient List Sidebar -->
            <div class="patient-list-sidebar">
                <div class="sidebar-header">
                    <a href="patients.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Daftar Pasien
                    </a>
                </div>
                
                <div class="patient-list">
                    <?php while ($patient = mysqli_fetch_assoc($result_all_patients)): ?>
                        <a href="?id=<?= $patient['id'] ?>" 
                           class="patient-item <?= $patient['id'] == $patient_id ? 'active' : '' ?>">
                            <?= htmlspecialchars($patient['nama_lengkap']) ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Patient Detail Content -->
            <div class="patient-detail-content">
                <?php if ($patient_detail): ?>
                    <div class="detail-header">
                        <h2>
                            <i class="fas fa-user-circle"></i>
                            <?= htmlspecialchars($patient_detail['nama_lengkap']) ?>
                        </h2>
                    </div>

                    <!-- Data Pribadi Card -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <h3><i class="fas fa-user"></i> Data Pribadi</h3>
                        </div>
                        <div class="info-card-content">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-item-row">
                                        <span class="info-label">Nama Lengkap</span>
                                        <span class="info-value"><?= htmlspecialchars($patient_detail['nama_lengkap']) ?></span>
                                    </div>
                                    <div class="info-item-row">
                                        <span class="info-label">Nama Panggilan</span>
                                        <span class="info-value"><?= htmlspecialchars($patient_detail['nama_panggilan'] ?: '-') ?></span>
                                    </div>
                                    <div class="info-item-row">
                                        <span class="info-label">Tanggal Lahir</span>
                                        <span class="info-value">
                                            <?= !empty($patient_detail['tanggal_lahir']) ? date('d F Y', strtotime($patient_detail['tanggal_lahir'])) : '-' ?>
                                            <?php if (!empty($patient_detail['tanggal_lahir']) && $patient_detail['tanggal_lahir'] != '0000-00-00'): ?>
                                                <span class="highlight">
                                                    (<?= calculateAgeWithMonths($patient_detail['tanggal_lahir']) ?>)
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-item-row">
                                        <span class="info-label">Jenis Kelamin</span>
                                        <span class="info-value">
                                            <?= $patient_detail['jenis_kelamin'] == 'L' ? 'Laki-laki' : ($patient_detail['jenis_kelamin'] == 'P' ? 'Perempuan' : '-') ?>
                                        </span>
                                    </div>
                                    <div class="info-item-row">
                                        <span class="info-label">Kategori</span>
                                        <span class="info-value"><?= htmlspecialchars($patient_detail['kategori_usia'] ?: '-') ?></span>
                                    </div>
                                    <div class="info-item-row">
                                        <span class="info-label">Kebangsaan</span>
                                        <span class="info-value"><?= htmlspecialchars($patient_detail['kebangsaan'] ?: 'Indonesia') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Identitas Card -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <h3><i class="fas fa-id-card"></i> Identitas</h3>
                        </div>
                        <div class="info-card-content">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-item-row">
                                        <span class="info-label">Jenis Identitas</span>
                                        <span class="info-value">
                                            <?= !empty($patient_detail['nik']) ? 'NIK' : (!empty($patient_detail['paspor']) ? 'Paspor' : 'KTP') ?>
                                        </span>
                                    </div>
                                    <div class="info-item-row">
                                        <span class="info-label">Nomor Identitas</span>
                                        <span class="info-value highlight">
                                            <?= htmlspecialchars($patient_detail['nik'] ?: $patient_detail['paspor'] ?: $patient_detail['no_rekam_medis'] ?: '-') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Kontak Card -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <h3><i class="fas fa-address-book"></i> Informasi Kontak</h3>
                        </div>
                        <div class="info-card-content">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-item-row">
                                        <span class="info-label">No Telepon</span>
                                        <span class="info-value">
                                            <?php if (!empty($patient_phones)): ?>
                                                <div class="info-value-list">
                                                    <?php foreach ($patient_phones as $phone_data): ?>
                                                        <div class="info-value-item">
                                                            <span><?= htmlspecialchars($phone_data['phone']) ?></span>
                                                            <?php if ($phone_data['is_primary'] == 1): ?>
                                                                <span class="badge-primary">Utama</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="info-item-row">
                                        <span class="info-label">Email</span>
                                        <span class="info-value">
                                            <?php if (!empty($patient_emails)): ?>
                                                <div class="info-value-list">
                                                    <?php foreach ($patient_emails as $email_data): ?>
                                                        <div class="info-value-item">
                                                            <span><?= htmlspecialchars($email_data['email']) ?></span>
                                                            <?php if ($email_data['is_primary'] == 1): ?>
                                                                <span class="badge-primary">Utama</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-item-row">
                                        <span class="info-label">Alamat</span>
                                        <span class="info-value">
                                            <?php 
                                            $alamat = [];
                                            if (!empty($patient_detail['alamat_primary'])) $alamat[] = $patient_detail['alamat_primary'];
                                            if (!empty($patient_detail['kota'])) $alamat[] = $patient_detail['kota'];
                                            if (!empty($patient_detail['provinsi'])) $alamat[] = $patient_detail['provinsi'];
                                            echo htmlspecialchars(implode(', ', $alamat) ?: '-');
                                            ?>
                                        </span>
                                    </div>
                                    <div class="info-item-row">
                                        <span class="info-label">Pekerjaan</span>
                                        <span class="info-value"><?= htmlspecialchars($patient_detail['pekerjaan'] ?: '-') ?></span>
                                    </div>
                                    <div class="info-item-row">
                                        <span class="info-label">Nama Wali</span>
                                        <span class="info-value"><?= htmlspecialchars($patient_detail['nama_wali'] ?? '-') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Kesehatan Card -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <h3><i class="fas fa-heartbeat"></i> Riwayat Kesehatan</h3>
                        </div>
                        <div class="info-card-content">
                            <!-- Riwayat Alergi -->
                            <div class="info-item">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="tidakAdaAlergi" <?= empty($patient_detail['riwayat_alergi']) ? 'checked' : '' ?> disabled>
                                    <label for="tidakAdaAlergi">Tidak ada riwayat alergi</label>
                                </div>
                                <?php if (!empty($patient_detail['riwayat_alergi'])): ?>
                                    <div class="info-textarea">
                                        <?= nl2br(htmlspecialchars($patient_detail['riwayat_alergi'])) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="info-textarea"></div>
                                <?php endif; ?>
                            </div>

                            <!-- Separator -->
                            <div class="info-separator"></div>

                            <!-- Riwayat Penyakit -->
                            <div class="info-item">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="tidakAdaPenyakit" <?= empty($patient_detail['riwayat_penyakit']) ? 'checked' : '' ?> disabled>
                                    <label for="tidakAdaPenyakit">Tidak ada riwayat penyakit</label>
                                </div>
                                <?php if (!empty($patient_detail['riwayat_penyakit'])): ?>
                                    <div class="info-textarea">
                                        <?= nl2br(htmlspecialchars($patient_detail['riwayat_penyakit'])) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="info-textarea"></div>
                                <?php endif; ?>
                            </div>

                            <!-- Separator -->
                            <div class="info-separator"></div>

                            <!-- Riwayat Obat -->
                            <div class="info-item">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="tidakAdaObat" <?= empty($patient_detail['riwayat_obat']) ? 'checked' : '' ?> disabled>
                                    <label for="tidakAdaObat">Tidak ada riwayat obat</label>
                                </div>
                                <?php if (!empty($patient_detail['riwayat_obat'])): ?>
                                    <div class="info-textarea">
                                        <?= nl2br(htmlspecialchars($patient_detail['riwayat_obat'])) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="info-textarea"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Layanan Card -->
                    <?php if (!empty($patient_detail['layanan'])): ?>
                    <div class="info-card">
                        <div class="info-card-header">
                            <h3><i class="fas fa-procedures"></i> Informasi Layanan</h3>
                        </div>
                        <div class="info-card-content">
                            <div class="info-item">
                                <div class="info-item-row">
                                    <span class="info-value"><?= htmlspecialchars($patient_detail['layanan']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button class="btn-primary" onclick="window.location.href='edit_patient.php?id=<?= $patient_id ?>'">
                            <i class="fas fa-edit"></i> Edit Data
                        </button>
                    </div>

                <?php else: ?>
                    <div class="empty-detail">
                        <i class="fas fa-user-circle"></i>
                        <p>Pilih pasien dari daftar untuk melihat detail</p>
                        <p class="hint">Klik nama pasien di sidebar kiri</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/sidebar-toggle.js"></script>
</body>
</html>