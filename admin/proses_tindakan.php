<?php
session_start();
include "../config.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Booking ID tidak ditemukan.");
}

$parent_booking_id = intval($_GET['id']);

/* Ambil booking parent */
$sql_parent = "
SELECT b.* 
FROM bookings b
WHERE b.id = ?
";
$stmt_parent = $conn->prepare($sql_parent);
$stmt_parent->bind_param("i", $parent_booking_id);
$stmt_parent->execute();
$parent_booking = $stmt_parent->get_result()->fetch_assoc();

if (!$parent_booking) {
    die("Data booking tidak ditemukan.");
}

/* Ambil semua child bookings (peserta) */
$sql_peserta = "
SELECT b.*, 
       p.id AS patient_id,
       p.no_rekam_medis,
       p.nama_lengkap,
       p.nama_panggilan,
       p.tanggal_lahir,
       p.jenis_kelamin,
       p.riwayat_alergi,
       p.riwayat_penyakit,
       p.riwayat_obat,
       p.nik,
       p.paspor
FROM bookings b
JOIN patients p ON b.patient_id = p.id
WHERE b.parent_id = ? OR (b.parent_id IS NULL AND b.id = ?)
ORDER BY b.id
";
$stmt_peserta = $conn->prepare($sql_peserta);
$stmt_peserta->bind_param("ii", $parent_booking_id, $parent_booking_id);
$stmt_peserta->execute();
$peserta_result = $stmt_peserta->get_result();

$semua_peserta = [];
while ($row = $peserta_result->fetch_assoc()) {
    $semua_peserta[] = $row;
}

// Ambil participant_id dari URL, default ke peserta pertama
$current_patient_id = isset($_GET['participant_id']) ? intval($_GET['participant_id']) : $semua_peserta[0]['patient_id'];

// Cari booking untuk peserta yang aktif
$current_peserta = null;
$current_booking_id = null;
foreach ($semua_peserta as $peserta) {
    if ($peserta['patient_id'] == $current_patient_id) {
        $current_peserta = $peserta;
        $current_booking_id = $peserta['id'];
        break;
    }
}

if (!$current_peserta) {
    die("Data peserta tidak ditemukan.");
}

// ================= AMBIL DATA TINDAKAN UNTUK PESERTA AKTIF =================
$sql_tindakan = "SELECT * FROM tindakan WHERE booking_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt_t = $conn->prepare($sql_tindakan);
$stmt_t->bind_param("i", $current_booking_id);
$stmt_t->execute();
$tindakan = $stmt_t->get_result()->fetch_assoc();

function hitungUsia($tanggal_lahir) {
    $lahir = new DateTime($tanggal_lahir);
    $sekarang = new DateTime();
    $diff = $sekarang->diff($lahir);

    return $diff->y . " tahun " . $diff->m . " bulan";
}

$usia = hitungUsia($current_peserta['tanggal_lahir']);

function formatTanggalIndo($tanggal) {
    if (!$tanggal) return "";

    $bulan = [
        1 => 'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ];

    $exp = explode('-', $tanggal);

    if (count($exp) != 3) return $tanggal;

    return $exp[2] . ' ' . $bulan[(int)$exp[1]] . ' ' . $exp[0];
}

$tgl_lahir_indo = formatTanggalIndo($current_peserta['tanggal_lahir']);

/* Ambil layanan untuk booking ini */
$sql_services = "SELECT nama_layanan FROM booking_services WHERE booking_id = ?";
$stmt_s = $conn->prepare($sql_services);
$stmt_s->bind_param("i", $current_booking_id);
$stmt_s->execute();
$services = $stmt_s->get_result();

/* Ambil dokter */
$sql_staff = "
SELECT s.id, s.nama_lengkap, s.gelar, s.sip
FROM booking_staff bs
JOIN staff s ON bs.staff_id = s.id
WHERE bs.booking_id = ?
";
$stmt_d = $conn->prepare($sql_staff);
$stmt_d->bind_param("i", $parent_booking_id); // Ambil dari parent booking
$stmt_d->execute();
$dokters = $stmt_d->get_result();

$dokter_default = null;
if ($dokters->num_rows > 0) {
    $dokters->data_seek(0);
    $dokter_default = $dokters->fetch_assoc();
    $dokters->data_seek(0); // balikin pointer biar select tetap jalan
}

$tanggal_surat = date("Y-m-d");
$tanggal_surat_indo = formatTanggalIndo($tanggal_surat);

// ================= DATA BOOKING SUMMARY =================
$total_tagihan = $parent_booking['total_tagihan'] ?? 0;

$tgl_layanan = formatTanggalIndo($parent_booking['tanggal_booking'] ?? '');
$jam_layanan = !empty($parent_booking['waktu_booking'])
    ? date('H:i', strtotime($parent_booking['waktu_booking'])) . ' WIB'
    : '-';

// Lokasi layanan dari service_type
$service_type_raw = $parent_booking['service_type'] ?? '';

$service_type_map = [
    'in_clinic' => 'In Clinic',
    'home_service' => 'Home Service',
    'onsite' => 'On Site',
    'corporate' => 'Corporate'
];

$lokasi_layanan = $service_type_map[strtolower($service_type_raw)] 
    ?? ucwords(str_replace('_',' ', $service_type_raw)) 
    ?: '-';

// Gabungkan dokter
$nakes_list = [];
$dokters->data_seek(0);
while($d = $dokters->fetch_assoc()){
    $nakes_list[] = $d['gelar'].' '.$d['nama_lengkap'];
}

$nakes_text = count($nakes_list) ? implode(', ', $nakes_list) : '-';

// ================= STATUS BOOKING BADGE =================
$status_booking = strtolower($parent_booking['status'] ?? 'pending');

$status_label = [
    'pending'   => 'Menunggu',
    'confirmed' => 'Terkonfirmasi',
    'completed' => 'Selesai',
    'cancel'    => 'Dibatalkan'
];

$status_text = $status_label[$status_booking] ?? ucfirst($status_booking);

$status_class = 'badge-default';

if ($status_booking === 'pending') {
    $status_class = 'badge-pending';
} elseif ($status_booking === 'confirmed') {
    $status_class = 'badge-confirmed';
} elseif ($status_booking === 'completed') {
    $status_class = 'badge-completed';
} elseif ($status_booking === 'cancel') {
    $status_class = 'badge-cancel';
}


?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Proses Tindakan - <?= htmlspecialchars($current_peserta['nama_lengkap']) ?></title>

<link rel="stylesheet" href="css/proses_tindakan.css">
<link rel="stylesheet" href="css/surat.css">
<link rel="stylesheet" href="css/sidebar-toggle.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<!-- ================= SIDEBAR ================= -->
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
        <a href="patients.php" class="nav-item">
            <i class="fas fa-users"></i>
            <span>Pasien</span>
        </a>
        <a href="staff.php" class="nav-item">
            <i class="fas fa-user-md"></i>
            <span>Staff</span>
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

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content">

    <!-- HEADER -->
    <div class="detail-header">
        <button onclick="window.location.href='booking_detail.php?id=<?= $parent_booking_id ?>'" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </button>
        <h1>Proses / Tindakan Pasien</h1>
    </div>

    <!-- TAB PESERTA + STATUS -->
    <div class="participant-header">

        <div class="participant-tabs">
            <?php foreach ($semua_peserta as $index => $peserta): ?>
                <a href="?id=<?= $parent_booking_id ?>&participant_id=<?= $peserta['patient_id'] ?>" 
                class="participant-tab <?= $peserta['patient_id'] == $current_patient_id ? 'active' : '' ?>">
                    Peserta <?= $index + 1 ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="booking-badge <?= $status_class ?>">
            <?= htmlspecialchars($status_text) ?>
        </div>

    </div>

    <div class="detail-layout">

        <!-- ================= FORM KIRI ================= -->
        <div class="detail-container">
            <form id="formTindakan" action="proses_simpan_tindakan.php" method="POST">
                <input type="hidden" name="booking_id" value="<?= $current_booking_id ?>">
                <input type="hidden" name="parent_booking_id" value="<?= $parent_booking_id ?>">
                <input type="hidden" name="patient_id" value="<?= $current_patient_id ?>">
    
            <div class="booking-summary-card">

                <div class="summary-header">
                    <i class="fas fa-calendar-check"></i>
                    <span>Informasi Booking</span>
                </div>

                <div class="summary-grid">

                    <div class="summary-row">
                        <div class="summary-label">No. Antrian</div>
                        <div class="summary-value">
                            <?= htmlspecialchars($parent_booking['nomor_antrian'] ?? $parent_booking['id']) ?>
                        </div>
                    </div>

                    <div class="summary-row">
                        <div class="summary-label">Tanggal & Jam Layanan</div>
                        <div class="summary-value">
                            <?= $tgl_layanan ?>, <?= htmlspecialchars($jam_layanan) ?>
                        </div>
                    </div>

                    <div class="summary-row">
                        <div class="summary-label">Lokasi Layanan</div>
                        <div class="summary-value">
                            <?= htmlspecialchars($lokasi_layanan) ?>
                        </div>
                    </div>

                    <div class="summary-row">
                        <div class="summary-label">Nakes Bertugas</div>
                        <div class="summary-value">
                            <?= htmlspecialchars($nakes_text) ?>
                        </div>
                    </div>

                    <div class="summary-row highlight">
                        <div class="summary-label">Total Tagihan</div>
                        <div class="summary-value">
                            Rp <?= number_format($total_tagihan,0,',','.') ?>
                        </div>
                    </div>

                </div>
            </div>

            <div class="patient-summary-card">

                <div class="summary-header">
                    <i class="fas fa-user"></i>
                    <span>Informasi Pasien</span>
                </div>

                <div class="patient-summary-grid">

                    <div class="summary-row">
                        <div class="summary-label">No. Rekam Medis</div>
                        <div class="summary-value">
                            <?= htmlspecialchars($current_peserta['no_rekam_medis']) ?>
                        </div>
                    </div>

                    <div class="summary-row">
                        <div class="summary-label">Nama Lengkap</div>
                        <div class="summary-value">
                            <?= htmlspecialchars($current_peserta['nama_lengkap']) ?>
                            <?php if(!empty($current_peserta['nama_panggilan'])): ?>
                                (<?= htmlspecialchars($current_peserta['nama_panggilan']) ?>)
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="summary-row">
                        <div class="summary-label">Tanggal Lahir</div>
                        <div class="summary-value">
                            <?= $tgl_lahir_indo ?> (<?= $usia ?>)
                        </div>
                    </div>

                    <div class="summary-row">
                        <div class="summary-label">Jenis Kelamin</div>
                        <div class="summary-value">
                            <?= htmlspecialchars($current_peserta['jenis_kelamin']) ?>
                        </div>
                    </div>

                    <div class="summary-row full-width">
                        <div class="summary-label">Riwayat Alergi</div>
                        <div class="summary-value">
                            <?= htmlspecialchars($current_peserta['riwayat_alergi'] ?? '-') ?>
                        </div>
                    </div>

                    <div class="summary-row full-width">
                        <div class="summary-label">Riwayat Penyakit</div>
                        <div class="summary-value">
                            <?= htmlspecialchars($current_peserta['riwayat_penyakit'] ?? '-') ?>
                        </div>
                    </div>

                    <div class="summary-row full-width">
                        <div class="summary-label">Konsumsi Obat</div>
                        <div class="summary-value">
                            <?= htmlspecialchars($current_peserta['konsumsi_obat'] ?? '-') ?>
                        </div>
                    </div>

                </div>

                <div class="patient-summary-actions">

                    <a href="patient_detail.php?id=<?= $current_patient_id ?>" class="btn-summary">
                        Informasi Lengkap
                    </a>

                    <a href="rekam_medis_history.php?id=<?= $current_patient_id ?>" class="btn-summary">
                        Riwayat Rekam Medis
                    </a>

                    <a href="vaksin_history.php?id=<?= $current_patient_id ?>" class="btn-summary">
                        Riwayat Vaksinasi
                    </a>

                    <a href="patient_edit.php?id=<?= $current_patient_id ?>" class="btn-summary primary">
                        Edit Data Pasien
                    </a>

                </div>

            </div>

                <div class="proses-container">
                    <div class="detail-grid">

                        <!-- ================= DATA VAKSIN ================= -->
                        <div class="detail-item">
                            <label>Jenis Vaksinasi</label>
                            <input type="text" name="jenis_vaksin"
                                value="<?= htmlspecialchars($tindakan['jenis_vaksin'] ?? '') ?>">
                        </div>

                        <div class="detail-item">
                            <label>No. Batch Vaksin</label>
                            <input type="text" name="batch_vaksin"
                                value="<?= htmlspecialchars($tindakan['batch_vaksin'] ?? '') ?>">
                        </div>

                        <div class="detail-item">
                            <label>Tanggal Kadaluarsa Vaksin</label>
                            <input type="date" name="expired_vaksin"
                                value="<?= $tindakan['expired_vaksin'] ?? '' ?>">
                        </div>

                        <!-- ================= KEDATANGAN ================= -->
                        <div class="detail-item">
                            <label>Kedatangan ke</label>
                            <select name="kedatangan_ke">
                                <option value="">-- Pilih --</option>
                                <option value="1" <?= ($tindakan['kedatangan_ke'] ?? '') == '1' ? 'selected' : '' ?>>1</option>
                                <option value="2" <?= ($tindakan['kedatangan_ke'] ?? '') == '2' ? 'selected' : '' ?>>2</option>
                                <option value="3" <?= ($tindakan['kedatangan_ke'] ?? '') == '3' ? 'selected' : '' ?>>3</option>
                            </select>
                        </div>

                        <div class="detail-item">
                            <label>Kedatangan Selanjutnya</label>
                            <input type="date" 
                                name="kedatangan_selanjutnya" 
                                value="<?= htmlspecialchars($tindakan['kedatangan_selanjutnya'] ?? '') ?>"
                                min="<?= date('Y-m-d') ?>">
                            <small style="display: block; color: #64748b; margin-top: 4px; font-size: 12px;">
                                <i class="fas fa-info-circle"></i> Jadwal kunjungan berikutnya
                            </small>
                        </div>

                        <div class="detail-item">
                            <label>Status</label>
                            <select name="status">
                                <option value="">-- Pilih --</option>
                                <option value="Aktif" <?= ($tindakan['status'] ?? '') == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="Selesai" <?= ($tindakan['status'] ?? '') == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>

                        <!-- ================= ANAMNESIS ================= -->
                        <div class="detail-item full-width">
                            <label>Anamnesis</label>

                            <div class="anamnesis-subbox">

                                <div class="anamnesis-row">
                                    <label>Keluhan</label>
                                    <textarea name="keluhan"><?= htmlspecialchars($tindakan['keluhan'] ?? 'Tidak ada keluhan') ?></textarea>
                                </div>

                                <div class="anamnesis-row">
                                    <label>KIPI Sebelumnya</label>
                                    <textarea name="kipi_sebelumnya"><?= htmlspecialchars($tindakan['kipi_sebelumnya'] ?? 'Tidak ada') ?></textarea>
                                </div>

                                <div class="anamnesis-row">
                                    <label>Kontraindikasi</label>
                                    <textarea name="kontraindikasi"><?= htmlspecialchars($tindakan['kontraindikasi'] ?? 'Tidak ada') ?></textarea>
                                </div>

                                <textarea name="anamnesis" placeholder="Catatan anamnesis tambahan"><?= htmlspecialchars($tindakan['anamnesis'] ?? '') ?></textarea>

                            </div>

                            
                        </div>

                        <div class="detail-item full-width">
                            <label>Pemeriksaan Fisik</label>

                            <div class="pf-subbox">

                                <div class="pf-row">
                                    <label>Berat Badan</label>
                                    <input type="number" step="0.1" name="bb"
                                        value="<?= htmlspecialchars($tindakan['bb'] ?? '') ?>">
                                    <span class="pf-unit">kg</span>
                                </div>

                                <div class="pf-row">
                                    <label>Tinggi / Panjang Badan</label>
                                    <input type="number" step="0.1" name="tb"
                                        value="<?= htmlspecialchars($tindakan['tb'] ?? '') ?>">
                                    <span class="pf-unit">cm</span>
                                </div>

                                <div class="pf-row">
                                    <label>Lingkar Kepala</label>
                                    <input type="number" step="0.1" name="lingkar_kepala"
                                        value="<?= htmlspecialchars($tindakan['lingkar_kepala'] ?? '') ?>">
                                    <span class="pf-unit">cm</span>
                                </div>

                                <div class="pf-row">
                                    <label>Suhu</label>
                                    <input type="number" step="0.1" name="suhu"
                                        value="<?= htmlspecialchars($tindakan['suhu'] ?? '') ?>">
                                    <span class="pf-unit">°C</span>
                                </div>

                                <div class="pf-row">
                                    <label>Tekanan Darah</label>
                                    <input type="text" name="tekanan_darah"
                                        value="<?= htmlspecialchars($tindakan['tekanan_darah'] ?? '') ?>">
                                    <span class="pf-unit">mmHg</span>
                                </div>

                                <div class="pf-row full">
                                    <label>PF Lainnya</label>
                                    <textarea name="pf_lainnya"><?= htmlspecialchars($tindakan['pf_lainnya'] ?? 'Dalam batas normal') ?></textarea>
                                </div>

                                <textarea name="pemeriksaan_fisik"
                                    placeholder="Catatan tambahan pemeriksaan fisik"><?= htmlspecialchars($tindakan['pemeriksaan_fisik'] ?? '') ?></textarea>

                            </div>

                            
                        </div>

                        <div class="detail-item full-width">
                            <label>Diagnosis</label>

                            <!-- Tombol Diagnosis Cepat -->
                            <div class="diagnosis-buttons">
                                <button type="button" onclick="setDiagnosis('Pro Vaksinasi (Z23)')">
                                    Pro Vaksinasi (Z23)
                                </button>

                                <button type="button" onclick="setDiagnosis('Pro Infus Vitamin (Z51.89)')">
                                    Pro Infus Vitamin (Z51.89)
                                </button>

                                <button type="button" onclick="setDiagnosis('Infeksi saluran napas atas (J06.9)')">
                                    ISPA (J06.9)
                                </button>
                            </div>

                            <textarea 
                                id="diagnosisBox"
                                name="diagnosis"><?= htmlspecialchars($tindakan['diagnosis'] ?? '') ?></textarea>
                        </div>

                        <div class="detail-item full-width">
                            <label>Tatalaksana</label>
                            <textarea name="tatalaksana"><?= htmlspecialchars($tindakan['tatalaksana'] ?? '') ?></textarea>
                        </div>

                        <!-- ================= VITAL SIGNS ================= -->
                        <div class="detail-item">
                            <label>Suhu (°C)</label>
                            <input type="number" step="0.1" name="suhu"
                                value="<?= $tindakan['suhu'] ?? '' ?>">
                        </div>

                        <div class="detail-item">
                            <label>Tekanan Darah (mmHg)</label>
                            <input type="text" name="tekanan_darah"
                                value="<?= htmlspecialchars($tindakan['tekanan_darah'] ?? '') ?>"
                                placeholder="120/80">
                        </div>

                        <div class="detail-item">
                            <label>Respirasi (/menit)</label>
                            <input type="number" name="respirasi"
                                value="<?= $tindakan['respirasi'] ?? '' ?>">
                        </div>

                        <div class="detail-item">
                            <label>Nadi (/menit)</label>
                            <input type="number" name="nadi"
                                value="<?= $tindakan['nadi'] ?? '' ?>">
                        </div>

                    </div>

                    <!-- ACTION BUTTON -->
                    <div class="action-buttons">
                        <button type="button" class="btn-secondary" onclick="window.history.back()">Batal</button>
                        <button type="submit" class="btn-save">Simpan Tindakan</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ================= PANEL KANAN (PREVIEW SURAT) ================= -->
        <div class="detail-right">
            <!-- ===== CARD TENAGA KESEHATAN ===== -->
            <div class="side-card staff-card">
                <div class="side-header">
                    <div class="header-title">
                        <i class="fas fa-user-md"></i>
                        <h3>Tenaga Kesehatan</h3>
                    </div>
                    <span class="staff-count"><?= $dokters->num_rows ?> Orang</span>
                </div>

                <div class="side-body">
                    <?php if($dokters->num_rows > 0): ?>
                        <?php 
                        $dokters->data_seek(0); // Reset pointer
                        while($dokter = $dokters->fetch_assoc()): 
                        ?>
                            <div class="staff-item" id="staff-<?= $dokter['id'] ?>">
                                <div class="staff-info">
                                    <div class="staff-name">
                                        <i class="fas fa-user-circle"></i>
                                        <span>
                                            <?= htmlspecialchars($dokter['gelar'] . ' ' . $dokter['nama_lengkap']) ?>
                                        </span>
                                    </div>
                                    <?php if(!empty($dokter['sip'])): ?>
                                        <div class="staff-sip">
                                            SIP: <?= htmlspecialchars($dokter['sip']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Tombol hapus selalu enable karena status sudah confirmed -->
                                <button class="btn-delete-staff" 
                                        onclick="removeStaff(<?= $parent_booking_id ?>, <?= $dokter['id'] ?>)" 
                                        title="Hapus dari tim">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-staff">
                            <i class="fas fa-user-md-slash"></i>
                            <p>Belum ada tenaga kesehatan</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tombol Tambah Nakes - SELALU ENABLE -->
                <button class="btn-add-worker" onclick="openAddDoctorPopup()">
                    <i class="fas fa-plus-circle"></i>
                    Tambah Nakes
                </button>
            </div>

            <!-- PREVIEW SURAT -->
            <div class="preview-panel" id="previewPanel">
                <button class="btn-maximize" onclick="openFullPreview()">
                    <i class="fas fa-expand"></i>
                </button>

                <div class="preview-placeholder" id="previewPlaceholder">
                    Isi kelengkapan data dan pilih jenis surat terlebih dahulu. Preview surat akan muncul di sini
                </div>

                <div class="preview-content" id="previewContent" style="display:none;"></div>
            </div>

            <div class="surat-control-panel">
                <div class="panel-title">
                    <i class="fas fa-file-medical"></i>
                    Pengaturan Surat
                </div>

                <!-- PILIH JENIS SURAT -->
                <div class="control-group">
                    <label class="group-label">Jenis Surat</label>
                    <div class="radio-group modern-radio">
                        <label class="radio-card">
                            <input type="radio" name="surat" value="sehat">
                            <span>Surat Sehat</span>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="surat" value="sakit">
                            <span>Surat Sakit</span>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="surat" value="vaksin">
                            <span>Sertifikat Vaksin</span>
                        </label>
                    </div>
                </div>

                <!-- FORM ISTIRAHAT (KHUSUS SURAT SAKIT) -->
                <div class="control-group" id="form-istirahat" style="display:none;">
                    <label class="group-label">Keterangan Istirahat</label>
                    <div class="istirahat-grid">
                        <div class="istirahat-item">
                            <label>Lama (hari)</label>
                            <input type="number" id="input_lama" placeholder="Contoh: 2">
                        </div>
                        <div class="istirahat-item">
                            <label>Tanggal Awal</label>
                            <input type="date" id="input_tgl_awal">
                        </div>
                        <div class="istirahat-item">
                            <label>Tanggal Akhir</label>
                            <input type="date" id="input_tgl_akhir">
                        </div>
                    </div>
                </div>

                <!-- PEMERIKSAAN FISIK LAIN (KHUSUS SURAT SEHAT) -->
                <div class="control-group" id="form-pf-lain" style="display:none;">
                    <label class="group-label">Pemeriksaan Fisik Lain</label>
                    <textarea id="input_pf_lain"
                            class="modern-input"
                            rows="3"
                            placeholder="Kosongkan jika dalam batas normal"></textarea>
                </div>

                <!-- DOKTER PENANDATANGAN -->
                <div class="control-group">
                    <label class="group-label">Dokter Penandatangan</label>
                    <select name="dokter_id" class="modern-select">
                        <?php 
                        $dokters->data_seek(0);
                        while($d = $dokters->fetch_assoc()): ?>
                            <option value="<?= $d['id'] ?>">
                                <?= htmlspecialchars($d['gelar'].' '.$d['nama_lengkap']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- POSISI -->
                <div class="control-group">
                    <label class="group-label">Posisi</label>
                    <input type="text" name="posisi" class="modern-input"
                        value="Dokter Penanggung Jawab">
                </div>
            </div>

            <!-- BUTTON ACTION -->
            <div class="preview-actions">
                <button type="button" class="btn-print-preview" id="btnCetakSurat">
                    Cetak Surat
                </button>
                <button class="btn-send-preview">
                    Kirim Surat
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL FULL PREVIEW -->
<div id="modalPreview" class="modal-preview" style="display:none;">
    <div class="modal-content">
        <button class="btn-close" onclick="closePreview()">
            ✕
        </button>
        <div id="modalPreviewContent"></div>
    </div>
</div>

<!-- POPUP TAMBAH DOKTER -->
<div id="addDoctorPopup" class="popup-overlay" style="display:none;">
    <div class="popup-content">
        <div class="popup-header">
            <h3><i class="fas fa-user-md"></i> Pilih Dokter</h3>
            <button type="button" class="popup-close" onclick="closeAddDoctorPopup()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="popup-body">
            <div class="doctor-select-container" id="doctorContainer">
                <div class="doctor-select-row">
                    <select class="doctor-select">
                        <option value="" selected disabled>-- Pilih Dokter --</option>
                        <?php
                        // Ambil semua staff dengan role dokter
                        $sql_all_dokter = "SELECT id, nama_lengkap, gelar, sip FROM staff WHERE role = 'dokter' ORDER BY nama_lengkap";
                        $result_all_dokter = mysqli_query($conn, $sql_all_dokter);
                        
                        // Reset pointer dokters yang sudah dipilih
                        $dokters->data_seek(0);
                        $existing_dokter_ids = [];
                        while($d = $dokters->fetch_assoc()) {
                            $existing_dokter_ids[] = $d['id'];
                        }
                        $dokters->data_seek(0); // Reset lagi
                        
                        // Tampilkan semua dokter
                        while($dokter = mysqli_fetch_assoc($result_all_dokter)):
                            // Cek apakah dokter sudah dipilih
                            $disabled = in_array($dokter['id'], $existing_dokter_ids) ? 'disabled' : '';
                            // HAPUS selected, biar tidak ada yang terpilih otomatis
                        ?>
                            <option value="<?= $dokter['id'] ?>" <?= $disabled ?>>
                                <?= htmlspecialchars($dokter['gelar'] . ' ' . $dokter['nama_lengkap']) ?>
                                <?= !empty($dokter['sip']) ? ' (SIP: ' . $dokter['sip'] . ')' : '' ?>
                                <?= $disabled ? ' - Sudah Ditugaskan' : '' ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="button" class="btn-remove-row" onclick="removeDoctorRow(this)" style="display: none;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-add-row" onclick="addDoctorRow()">
                <i class="fas fa-plus"></i> Tambah Tenaga Kerja
            </button>
        </div>
        
        <div class="popup-footer">
            <button type="button" class="btn-selesai" onclick="assignDoctors()">
                <i class="fas fa-check"></i> Selesai
            </button>
            <button type="button" class="btn-batal-popup" onclick="closeAddDoctorPopup()">
                <i class="fas fa-times"></i> Batal
            </button>
        </div>
    </div>
</div>

<script>
    const PV_RM = "<?= $current_peserta['no_rekam_medis'] ?>";
    const PV_NAMA = "<?= addslashes($current_peserta['nama_lengkap']) ?>";
    const PV_TGL_LAHIR = "<?= $tgl_lahir_indo ?>";
    const PV_USIA = "<?= $usia ?>";
    const PV_JK = "<?= $current_peserta['jenis_kelamin'] ?>";
    const PV_IDENTITAS = "<?= $current_peserta['nik'] ?: $current_peserta['paspor'] ?>";
    const PV_TGL_VAKSIN = "<?= $current_peserta['tanggal_booking'] ?>";
    const PV_DOKTER = "<?= addslashes(($dokter_default['gelar'] ?? '') . ' ' . ($dokter_default['nama_lengkap'] ?? '')) ?>";
    const PV_SIP = "<?= $dokter_default['sip'] ?? '-' ?>";
    const PV_TANGGAL_SURAT = "<?= $tanggal_surat_indo ?>";
    const CURRENT_BOOKING_ID = "<?= $current_booking_id ?>";
    const CURRENT_PATIENT_ID = "<?= $current_patient_id ?>";
</script>
<script>
    function setDiagnosis(text) {
        const box = document.getElementById("diagnosisBox");

        if (box.value.trim() === "") {
            box.value = text;
        } else {
            box.value += "\n" + text;
        }
    }

    // Fungsi hapus staff dari tim
    function removeStaff(bookingId, staffId) {
        if (!confirm('Yakin ingin menghapus tenaga kesehatan dari tim ini?')) return;
        
        fetch('remove_staff.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `booking_id=${bookingId}&staff_id=${staffId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Hapus elemen staff dari tampilan
                const staffElement = document.getElementById(`staff-${staffId}`);
                if (staffElement) {
                    staffElement.remove();
                    
                    // Update count
                    const staffCount = document.querySelector('.staff-count');
                    const currentCount = parseInt(staffCount.textContent);
                    staffCount.textContent = (currentCount - 1) + ' Orang';
                    
                    // Tampilkan empty state jika sudah tidak ada staff
                    const staffBody = document.querySelector('.staff-card .side-body');
                    if (staffBody.children.length === 0) {
                        staffBody.innerHTML = `
                            <div class="empty-staff">
                                <i class="fas fa-user-md-slash"></i>
                                <p>Belum ada tenaga kesehatan</p>
                            </div>
                        `;
                    }
                }
                showNotification('Tenaga kesehatan berhasil dihapus', 'success');
            } else {
                showNotification('Gagal menghapus: ' + (data.message || ''), 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('Terjadi kesalahan', 'error');
        });
    }

    // Fungsi untuk membuka popup tambah dokter
    function openAddDoctorPopup() {
        document.getElementById('addDoctorPopup').style.display = 'flex';
    }

    // Fungsi untuk menutup popup
    function closeAddDoctorPopup() {
        document.getElementById('addDoctorPopup').style.display = 'none';
    }

    // Fungsi untuk menambah row select dokter
    function addDoctorRow() {
        const container = document.getElementById('doctorContainer');
        const firstRow = container.querySelector('.doctor-select-row');
        const newRow = firstRow.cloneNode(true);
        
        // Reset select value ke placeholder
        const select = newRow.querySelector('.doctor-select');
        select.value = ''; // Pilih opsi "-- Pilih Dokter --"
        
        // Tampilkan tombol remove
        const removeBtn = newRow.querySelector('.btn-remove-row');
        removeBtn.style.display = 'block';
        
        // Di row baru, ENABLE SEMUA OPTION (termasuk yang tadinya disabled)
        // Tapi kita biarkan saja, nanti validasi di backend
        // Agar user tidak bisa pilih dokter yang sudah ditugaskan di row lain,
        // kita bisa tambahkan validasi saat submit
        
        container.appendChild(newRow);
    }

    // Fungsi untuk menghapus row select dokter
    function removeDoctorRow(btn) {
        const row = btn.closest('.doctor-select-row');
        const container = document.getElementById('doctorContainer');
        
        // Minimal harus ada 1 row
        if (container.children.length > 1) {
            row.remove();
        } else {
            alert('Minimal harus ada 1 pilihan dokter');
        }
    }

    // Fungsi untuk menyimpan dokter yang dipilih
    function assignDoctors() {
        const rows = document.querySelectorAll('.doctor-select-row');
        let selectedDoctors = [];
        
        rows.forEach(row => {
            const select = row.querySelector('.doctor-select');
            if (select.value) {
                selectedDoctors.push(select.value);
            }
        });
        
        if (selectedDoctors.length === 0) {
            alert('Pilih minimal 1 dokter');
            return;
        }
        
        // Tampilkan loading
        const btnSelesai = document.querySelector('.btn-selesai');
        const originalText = btnSelesai.innerHTML;
        btnSelesai.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        btnSelesai.disabled = true;
        
        fetch('assign_doctor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                booking_id: <?= $parent_booking_id ?>,
                doctor_ids: selectedDoctors,
                mode: 'add'  // <-- GANTI DARI 'replace' KE 'add'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Gagal: ' + (data.message || ''), 'error');
                btnSelesai.innerHTML = originalText;
                btnSelesai.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan', 'error');
            btnSelesai.innerHTML = originalText;
            btnSelesai.disabled = false;
        });
    }

    // Tutup popup jika klik di luar
    window.onclick = function(event) {
        const popup = document.getElementById('addDoctorPopup');
        if (event.target == popup) {
            closeAddDoctorPopup();
        }
    }

    // Fungsi notifikasi (copy dari booking_detail)
    function showNotification(message, type = 'info') {
        // Hapus notifikasi yang sudah ada
        const existing = document.querySelector('.notification');
        if (existing) existing.remove();
        
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
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
</script>
<script src="js/preview_surat.js"></script>
<script src="js/simpan_tindakan.js"></script>
<script src="js/cetak_surat.js"></script>                        
<script src="js/sidebar-toggle.js"></script>

</body>
</html>