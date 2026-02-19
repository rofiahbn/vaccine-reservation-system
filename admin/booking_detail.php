<?php
session_start();
include "../config.php";

// Get booking ID
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id == 0) {
    header('Location: dashboard.php');
    exit;
}

/* 🔥 Pastikan selalu parent booking */
$sql_parent = "SELECT parent_id FROM bookings WHERE id = ?";
$stmt = $conn->prepare($sql_parent);
$stmt->bind_param("i", $booking_id);
$stmt->execute();

$row_parent = $stmt->get_result()->fetch_assoc();

if ($row_parent && $row_parent['parent_id']) {
    $booking_id = $row_parent['parent_id'];
}


/* 🔥 Ambil semua peserta */
$sql = "
SELECT 
    b.id AS booking_id,
    b.*,

    p.id AS patient_id,
    p.*

FROM bookings b
JOIN patients p ON b.patient_id = p.id
WHERE b.id = ? OR b.parent_id = ?
ORDER BY CASE WHEN b.id = ? THEN 0 ELSE 1 END
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $booking_id, $booking_id, $booking_id);
$stmt->execute();

$participants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


/* 🔥 Validasi */
if (empty($participants)) {
    header('Location: dashboard.php');
    exit;
}


/* 🔥 Parent booking = index pertama */
$booking = $participants[0];

// Get staff yang sudah ditugaskan ke booking
$sql_staff = "
    SELECT s.id, s.nama_lengkap, s.gelar, s.role
    FROM booking_staff bs
    JOIN staff s ON bs.staff_id = s.id
    WHERE bs.booking_id = ?
";
$stmt_staff = $conn->prepare($sql_staff);
$stmt_staff->bind_param("i", $booking_id);
$stmt_staff->execute();
$staffs = $stmt_staff->get_result();
$dokter_count = $staffs->num_rows;
$disable_accept = ($booking['status'] !== 'pending');

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/detail.css">
    <link rel="stylesheet" href="css/reschedule.css">
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
            <a href="products.php" class="nav-item">
                <i class="fas fa-capsules"></i>
                <span>Produk</span>
            </a>
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
        <div class="detail-header">
            <button onclick="window.location.href='dashboard.php'" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
            <h1>Detail Pesanan #<?php echo $booking['nomor_antrian']; ?></h1>

        </div>

        <div class="detail-layout">
            <div class="detail-container">
                <!-- Status Card -->
                <div class="status-card">
                    <div class="status-icon <?php echo $booking['status']; ?>">
                        <i class="fas fa-<?php echo $booking['status'] == 'pending' ? 'clock' : ($booking['status'] == 'confirmed' ? 'check-circle' : 'times-circle'); ?>"></i>
                    </div>
                    <div class="status-info">
                        <h3>Status Pesanan</h3>
                        <span class="status-badge-large <?= $booking['status']; ?>">
                            <?php 
                                if ($booking['status'] == 'pending') {
                                    echo 'Menunggu Konfirmasi';
                                } elseif ($booking['status'] == 'confirmed') {
                                    echo 'Pasien Dalam Antrian';
                                } elseif ($booking['status'] == 'completed') {
                                    echo 'Pesanan Selesai';
                                } elseif ($booking['status'] == 'cancelled') {
                                    echo 'Pesanan Dibatalkan';
                                } 
                            ?>
                        </span>
                    </div>
                    <div class="status-actions">
                        <?php if ($booking['status'] == 'pending'): ?>
                            <button class="btn btn-confirm" onclick="updateStatus(<?php echo $booking_id; ?>, 'confirmed')">
                                <i class="fas fa-check"></i> Konfirmasi
                            </button>
                            <button class="btn btn-cancel" onclick="updateStatus(<?php echo $booking_id; ?>, 'cancelled')">
                                <i class="fas fa-times"></i> Batalkan
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- CONTAINER UNTUK TABS + TOMBOL EDIT -->
                <div class="tabs-container">
                    <!-- TABS PARTICIPANT -->
                    <div class="participant-tabs">
                        <?php foreach ($participants as $index => $p): ?>
                            <button 
                                class="participant-tab <?= $index == 0 ? 'active' : '' ?>"
                                onclick="showParticipant(<?= $index ?>)">
                                Peserta <?= $index + 1 ?>
                            </button>
                        <?php endforeach; ?>

                        <!-- Tombol tambah peserta -->
                        <button class="participant-tab add" onclick="addParticipant()">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>

                    <!-- TOMBOL EDIT di pojok kanan -->
                    <div class="edit-button-container">
                        <?php if ($booking['status'] == 'completed' || $booking['status'] == 'cancelled' || $booking['payment_status'] == 'paid'): ?>
                            <button class="btn-edit disabled"
                                    disabled
                                    title="Data sudah selesai, tidak bisa diedit">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        <?php else: ?>
                            <div class="edit-wrapper">
                                <button class="btn-edit" id="btnEditMain">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                
                                <!-- Dropdown untuk pilih peserta -->
                                <div id="editOptions" class="edit-dropdown">
                                    <div class="edit-dropdown-header">
                                        <i class="fas fa-users"></i> Pilih Peserta
                                    </div>
                                    <?php foreach ($participants as $index => $p): ?>
                                        <?php
                                        $booking_record_id = $p['booking_id'] ?? $p['id'] ?? 0;
                                        $patient_id = $p['patient_id'] ?? $p['id'] ?? 0;
                                        ?>
                                        
                                        <a href="edit_booking.php?booking_id=<?= $booking_record_id ?>&patient_id=<?= $patient_id ?>"
                                        class="edit-dropdown-item"
                                        title="Edit data <?= htmlspecialchars($p['nama_lengkap']) ?>">
                                            <i class="fas fa-user-circle"></i>
                                            <div class="participant-info">
                                                <div class="participant-name">
                                                    Peserta <?= $index + 1 ?>: <?= htmlspecialchars($p['nama_lengkap']) ?>
                                                </div>
                                                <div class="participant-details">
                                                    <?= $p['usia'] ?> tahun, <?= $p['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?>
                                                </div>
                                            </div>
                                            <i class="fas fa-chevron-right dropdown-arrow"></i>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="detail-section">
                    <h2><i class="fas fa-calendar-check"></i> Informasi Booking</h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Nomor Antrian</label>
                            <p><?php echo htmlspecialchars($booking['nomor_antrian']); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Tanggal Booking</label>
                            <p><?php echo date('d F Y', strtotime($booking['tanggal_booking'])); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Waktu</label>
                            <p><?php echo substr($booking['waktu_booking'], 0, 5); ?> WIB</p>
                        </div>
                        <div class="detail-item">
                            <label>Tipe Layanan</label>
                            <p><?php echo htmlspecialchars($booking['service_type']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Patient Info -->
                <?php foreach ($participants as $index => $p): ?>

                <div class="participant-panel <?= $index == 0 ? 'active' : '' ?>" 
                    id="participant-<?= $index ?>"
                    data-booking-id="<?= $p['id'] ?>"
                    data-patient-id="<?= $p['patient_id'] ?>">

                    <div class="detail-section">
                        <h2><i class="fas fa-user"></i> Data Pasien</h2>

                        <div class="detail-grid">

                            <div class="detail-item">
                                <label>Nama Lengkap</label>
                                <p><?= htmlspecialchars($p['nama_lengkap']); ?></p>
                            </div>

                            <div class="detail-item">
                                <label>Nama Panggilan</label>
                                <p><?= htmlspecialchars($p['nama_panggilan'] ?: '-'); ?></p>
                            </div>

                            <div class="detail-item">
                                <label>Tanggal Lahir</label>
                                <p>
                                    <?= date('d F Y', strtotime($p['tanggal_lahir'])); ?>
                                    (<?= $p['usia']; ?> tahun)
                                </p>
                            </div>

                            <div class="detail-item">
                                <label>Jenis Kelamin</label>
                                <p><?= $p['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></p>
                            </div>

                            <div class="detail-item">
                                <label>NIK</label>
                                <p><?= htmlspecialchars($p['nik'] ?: '-'); ?></p>
                            </div>

                            <div class="detail-item">
                                <label>No. Paspor</label>
                                <p><?= htmlspecialchars($p['paspor'] ?: '-'); ?></p>
                            </div>

                            <div class="detail-item">
                                <label>Kebangsaan</label>
                                <p><?= htmlspecialchars($p['kebangsaan']); ?></p>
                            </div>

                            <div class="detail-item">
                                <label>Pekerjaan</label>
                                <p><?= htmlspecialchars($p['pekerjaan'] ?: '-'); ?></p>
                            </div>

                        </div>

                    </div>

                    <!-- CONTACT INFO -->
                    <div class="detail-section">
                        <h2><i class="fas fa-phone"></i> Kontak</h2>

                        <div class="detail-grid">

                            <!-- EMAIL -->
                            <div class="detail-item full-width">
                                <label>Email</label>

                                <?php
                                $sql_emails = "SELECT email FROM patient_emails WHERE patient_id = ?";
                                $stmt_e = $conn->prepare($sql_emails);
                                $stmt_e->bind_param("i", $p['patient_id']);
                                $stmt_e->execute();
                                $emails = $stmt_e->get_result();
                                ?>

                                <?php while ($e = $emails->fetch_assoc()): ?>
                                    <p><?= htmlspecialchars($e['email']) ?></p>
                                <?php endwhile; ?>

                            </div>


                            <!-- PHONE -->
                            <div class="detail-item full-width">
                                <label>Nomor HP</label>

                                <?php
                                $sql_phones = "SELECT phone FROM patient_phones WHERE patient_id = ?";
                                $stmt_p = $conn->prepare($sql_phones);
                                $stmt_p->bind_param("i", $p['patient_id']);
                                $stmt_p->execute();
                                $phones = $stmt_p->get_result();
                                ?>

                                <?php while ($ph = $phones->fetch_assoc()): ?>
                                    <p><?= htmlspecialchars($ph['phone']) ?></p>
                                <?php endwhile; ?>

                            </div>


                            <!-- ADDRESS -->
                            <div class="detail-item full-width">
                                <label>Alamat</label>

                                <?php
                                $sql_addr = "SELECT * FROM patient_addresses 
                                            WHERE patient_id = ? AND is_primary = 1 LIMIT 1";
                                $stmt_a = $conn->prepare($sql_addr);
                                $stmt_a->bind_param("i", $p['patient_id']);
                                $stmt_a->execute();
                                $address = $stmt_a->get_result()->fetch_assoc();
                                ?>

                                <?php if ($address): ?>
                                    <p><?= htmlspecialchars($address['alamat']) ?></p>
                                    <p><?= htmlspecialchars($address['kota']) ?>,
                                    <?= htmlspecialchars($address['provinsi']) ?></p>
                                <?php else: ?>
                                    <p>-</p>
                                <?php endif; ?>

                            </div>

                        </div>
                    </div>

                    <!-- SERVICES PER PESERTA -->
                    <div class="detail-section">
                        <h2><i class="fas fa-syringe"></i> Layanan Peserta</h2>

                        <?php
                        $sql_srv = "
                            SELECT nama_layanan 
                            FROM booking_services 
                            WHERE booking_id = ?
                            AND patient_id = ?
                        ";

                        $stmt_srv = $conn->prepare($sql_srv);
                        $stmt_srv->bind_param("ii", 
                            $p['booking_id'],
                            $p['patient_id']
                        );
                        $stmt_srv->execute();
                        $services = $stmt_srv->get_result();
                        ?>

                        <div class="services-grid">

                            <?php if ($services->num_rows > 0): ?>
                                <?php while ($srv = $services->fetch_assoc()): ?>
                                    <div class="service-item">
                                        <i class="fas fa-check-circle"></i>
                                        <?= htmlspecialchars($srv['nama_layanan']) ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>- Tidak ada layanan</p>
                            <?php endif; ?>

                        </div>
                    </div>

                    <!-- MEDICAL HISTORY PER PESERTA -->
                    <div class="detail-section">
                        <h2><i class="fas fa-file-medical"></i> Riwayat Kesehatan</h2>

                        <div class="detail-grid">

                            <div class="detail-item full-width">
                                <label>Riwayat Alergi</label>
                                <p><?= htmlspecialchars($p['riwayat_alergi'] ?: 'Tidak ada') ?></p>
                            </div>

                            <div class="detail-item full-width">
                                <label>Riwayat Penyakit</label>
                                <p><?= htmlspecialchars($p['riwayat_penyakit'] ?: 'Tidak ada') ?></p>
                            </div>

                            <div class="detail-item full-width">
                                <label>Riwayat Obat</label>
                                <p><?= htmlspecialchars($p['riwayat_obat'] ?: 'Tidak ada') ?></p>
                            </div>

                        </div>
                    </div>

                </div>

                <?php endforeach; ?>

            </div>

            <!-- RIGHT SIDE PANEL -->
            <div class="detail-right">

                <!-- Tenaga Kerja -->
                <div class="side-card">
                    <div class="side-header">
                        <h3>Tenaga Kesehatan</h3>
                    </div>

                    <div class="side-body">
                        <?php if($staffs->num_rows > 0): ?>
                            <?php while($s = $staffs->fetch_assoc()): ?>
                                <div class="staff-item" id="staff-<?= $s['id'] ?>">
                                    <span><?= htmlspecialchars($s['gelar'].' '.$s['nama_lengkap']); ?></span>

                                    <?php if (
                                        $booking['payment_status'] == 'unpaid' &&
                                        !in_array($booking['status'], ['cancelled', 'completed'])
                                    ): ?>
                                        <!-- masih boleh hapus dokter -->
                                        <button class="btn-delete-staff" 
                                                onclick="removeStaff(<?= $booking_id ?>, <?= $s['id'] ?>)" 
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <!-- sudah paid / cancelled / completed → LOCK -->
                                        <button class="btn-delete-staff" 
                                                disabled 
                                                title="Tidak bisa menghapus dokter karena pesanan sudah dibayar / dibatalkan / selesai"
                                                style="opacity:0.4; cursor:not-allowed;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>

                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <span class="empty-text">Belum ada staff</span>
                        <?php endif; ?>
                    </div>

                    <?php
                        $disable_add_dokter = 
                            ($booking['status'] == 'pending') ||
                            ($booking['status'] == 'completed') ||
                            ($booking['status'] == 'cancelled') ||
                            ($booking['payment_status'] == 'paid');
                        ?>

                        <button class="btn-add-worker" 
                                onclick="openAddDoctorPopup()" 
                                <?= $disable_add_dokter ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' ?>>
                            <i class="fas fa-user-md"></i> Tambah Nakes
                        </button>
                </div>

                <!-- Action Buttons -->
                <div class="side-card action-buttons">

                <?php if ($booking['status'] == 'pending'): ?>

                    <!-- MODE AWAL: BELUM DIKONFIRMASI -->
                    <button class="btn-accept"
                            onclick="openAddDoctorPopup()"
                            <?= $disable_accept ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>>
                        <i class="fas fa-check-circle"></i> Terima Booking
                    </button>

                    <button type="button" class="btn-reschedule" onclick="openRescheduleModal()">
                        <i class="fas fa-calendar-alt"></i> Reschedule
                    </button>

                    <button id="btn-cancel" class="btn-cancel" 
                            onclick="cancelBooking(this, <?= $booking_id ?>)">
                        <i class="fas fa-times-circle"></i> Cancel Booking
                    </button>

                <?php elseif ($booking['status'] == 'confirmed'): ?>

                    <?php if ($booking['payment_status'] == 'unpaid'): ?>

                        <!-- BELUM BAYAR → MODE OPERASIONAL -->

                        <button class="btn-process"
                                onclick="window.location.href='proses_tindakan.php?id=<?= $booking_id ?>'">
                            Proses / Tindakan
                        </button>

                        <button type="button" class="btn-reschedule" onclick="openRescheduleModal()">
                            <i class="fas fa-calendar-alt"></i> Reschedule
                        </button>

                        <button id="btn-cancel" class="btn-cancel" 
                                onclick="cancelBooking(this, <?= $booking_id ?>)">
                            <i class="fas fa-times-circle"></i> Cancel Booking
                        </button>

                        <hr>

                        <?php if ($booking['payment_status'] == 'unpaid' && $booking['tindakan_selesai'] == 1): ?>

                            <button class="btn-payment"
                                    onclick="window.location.href='pembayaran.php?id=<?= $booking_id ?>'">
                                Proses Pembayaran
                            </button>

                        <?php elseif ($booking['payment_status'] == 'unpaid' && $booking['tindakan_selesai'] == 0): ?>

                            <button class="btn-payment"
                                    disabled
                                    style="opacity:0.5; cursor:not-allowed;"
                                    title="Simpan tindakan terlebih dahulu sebelum melakukan pembayaran">
                                Proses Pembayaran
                            </button>

                        <?php endif; ?>

                    <?php else: ?>

                        <!-- SUDAH BAYAR → MODE ADMIN FINAL -->

                        <button class="btn-payment"
                                onclick="window.location.href='pembayaran.php?id=<?= $booking_id ?>'">
                            Detail Pembayaran
                        </button>

                        <button class="btn-print">
                            Cetak Surat
                        </button>

                    <?php endif; ?>

                <?php elseif ($booking['status'] == 'completed'): ?>

                    <!-- MODE SELESAI -->
                    <button class="btn-print" onclick="openCetakSuratPopup()">
                        Cetak Surat
                    </button>

                    <button class="btn-payment"
                            onclick="window.location.href='pembayaran.php?id=<?= $booking_id ?>'">
                        Pembayaran Selesai
                    </button>

                <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Reschedule Modal -->
        <div id="rescheduleModal" class="modal-reschedule" style="display:none;">
            <div class="reschedule-content">
                <h2>Ubah Jadwal Pasien</h2>
                
                <form id="rescheduleForm">
                    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                    
                    <!-- Pilih Tanggal -->
                    <div class="reschedule-section">                     
                        <div class="calendar-header-reschedule">
                            <button type="button" onclick="changeMonthReschedule(-1)">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span id="currentMonthYear"></span>
                            <button type="button" onclick="changeMonthReschedule(1)">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <div class="calendar-days-reschedule" id="calendarDaysReschedule">
                            <div class="day-header">M</div>
                            <div class="day-header">S</div>
                            <div class="day-header">S</div>
                            <div class="day-header">R</div>
                            <div class="day-header">K</div>
                            <div class="day-header">J</div>
                            <div class="day-header">S</div>
                            <!-- Days akan di-generate oleh JS -->
                        </div>
                        
                        <div class="legend-reschedule">
                            <div class="legend-item">
                                <div class="legend-box tersedia"></div>
                                <span>Tersedia</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box penuh"></div>
                                <span>Jadwal Penuh</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box holiday"></div>
                                <span>Libur</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box closed"></div>
                                <span>Tutup</span>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="new_date" id="selectedNewDate">
                    
                    <!-- Selected Date Display -->
                    <div class="selected-date-reschedule" id="dateDisplayReschedule" style="display:none;">
                        Tanggal yang dipilih: <strong id="dateTextReschedule"></strong>
                    </div>
                    
                    <!-- Pilih Waktu -->
                    <div class="reschedule-section" id="timeSlotsSection" style="display:none;">
                        <h3>Pilih Waktu</h3>
                        <div class="time-slots-reschedule" id="timeSlots">
                            <!-- Time slots akan di-generate oleh JS -->
                        </div>
                    </div>
                    
                    <input type="hidden" name="new_time" id="selectedNewTime">
                    
                    <!-- Actions -->
                    <div class="reschedule-actions">
                        <button type="button" class="btn-cancel-reschedule" onclick="closeRescheduleModal()">
                            Batal
                        </button>
                        <button type="submit" class="btn-submit-reschedule" id="btnSubmitReschedule" disabled>
                            Jadwalkan Ulang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Participant Modal -->
    <div id="addParticipantModal" class="modal-add-participant" style="display:none;">
        <div class="modal-add-content">
            <div class="modal-add-header">
                <h2><i class="fas fa-user-plus"></i> Tambah Peserta Baru</h2>
                <button class="modal-close" onclick="closeAddParticipantModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-add-body">
                <!-- Pilih Pasien Existing -->
                <div class="selection-section">
                    <h3>Pilih Pasien Existing</h3>
                    <div class="search-box-participant">
                        <i class="fas fa-search"></i>
                        <input type="text" 
                               id="searchPatient" 
                               placeholder="Cari nama atau nomor rekam medis..."
                               onkeyup="searchPatients()">
                    </div>
                    
                    <div class="patient-list" id="patientList">
                        <div class="loading-patients">
                            <i class="fas fa-spinner fa-spin"></i> Memuat data pasien...
                        </div>
                    </div>
                    
                    <div class="or-divider">
                        <span>ATAU</span>
                    </div>
                </div>
                
                <!-- Buat Pasien Baru -->
                <div class="new-patient-section">
                    <h3>Buat Pasien Baru</h3>
                    <button class="btn-create-new" onclick="showNewPatientForm()">
                        <i class="fas fa-plus-circle"></i> Buat Pasien Baru
                    </button>
                </div>
                
                <!-- Form Pasien Baru (hidden by default) -->
                <div id="newPatientForm" style="display:none;">
                    <form id="newPatientFormElement" onsubmit="saveNewPatient(event)">
                        <!-- Data Diri Pasien -->
                        <h4 style="margin-top: 0; margin-bottom: 16px; color: #1e293b;">
                            <i class="fas fa-user"></i> Data Diri Pasien
                        </h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nama Lengkap <span class="required">*</span></label>
                                <input type="text" name="nama_lengkap" required placeholder="Nama lengkap">
                            </div>
                            <div class="form-group">
                                <label>Nama Panggilan</label>
                                <input type="text" name="nama_panggilan" placeholder="Nama panggilan">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tanggal Lahir <span class="required">*</span></label>
                                <input type="date" name="tanggal_lahir" id="tanggalLahir" required onchange="hitungUsiaDetail(this)">
                            </div>
                            <div class="form-group">
                                <label>Usia</label>
                                <input type="text" name="usia_display" id="usiaDisplay" readonly placeholder="Tahun Bulan">
                                <input type="hidden" name="usia_tahun" id="usiaTahun">
                                <input type="hidden" name="usia_bulan" id="usiaBulan">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Jenis Kelamin <span class="required">*</span></label>
                                <select name="jenis_kelamin" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Kebangsaan</label>
                                <select name="kebangsaan">
                                    <option value="WNI">WNI</option>
                                    <option value="WNA">WNA</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>NIK</label>
                                <input type="text" name="nik" placeholder="Nomor NIK">
                            </div>
                            <div class="form-group">
                                <label>No. Paspor</label>
                                <input type="text" name="paspor" placeholder="Nomor paspor">
                            </div>
                        </div>
                        
                        <!-- Data Wali (untuk anak di bawah 12 tahun) -->
                        <div id="waliSection" style="display: none; margin-top: 20px; padding: 16px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
                            <h4 style="margin-top: 0; margin-bottom: 12px; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-user-shield" style="color: #3b82f6;"></i> Data Wali / Orang Tua
                                <span style="font-size: 12px; background: #dbeafe; padding: 2px 8px; border-radius: 100px; color: #1e40af;">Anak di bawah 12 tahun</span>
                            </h4>
                            
                            <div class="form-group">
                                <label>Nama Wali <span class="required">*</span></label>
                                <input type="text" name="nama_wali" id="namaWali" placeholder="Nama lengkap wali / orang tua">
                            </div>
                        </div>
                        
                        <!-- Data Kontak -->
                        <h4 style="margin-top: 24px; margin-bottom: 16px; color: #1e293b;">
                            <i class="fas fa-phone"></i> Data Kontak
                        </h4>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="email@example.com">
                        </div>
                        
                        <div class="form-group">
                            <label>Nomor HP</label>
                            <input type="text" name="phone" placeholder="08123456789">
                        </div>
                        
                        <!-- Data Alamat dengan Provinsi & Kota -->
                        <h4 style="margin-top: 24px; margin-bottom: 16px; color: #1e293b;">
                            <i class="fas fa-map-marker-alt"></i> Data Alamat
                        </h4>

                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea name="alamat" rows="2" placeholder="Alamat lengkap (jalan, gang, nomor)"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Provinsi</label>
                                <select name="provinsi" id="provinsiSelect">
                                    <option value="">-- Pilih Provinsi --</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Kota/Kabupaten</label>
                                <select name="kota" id="kotaSelect" disabled>
                                    <option value="">-- Pilih Kota --</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Data Pekerjaan & Riwayat Kesehatan -->
                        <h4 style="margin-top: 24px; margin-bottom: 16px; color: #1e293b;">
                            <i class="fas fa-briefcase"></i> Data Tambahan
                        </h4>
                        
                        <div class="form-group">
                            <label>Pekerjaan</label>
                            <input type="text" name="pekerjaan" placeholder="Pekerjaan">
                        </div>
                        
                        <h4 style="margin-top: 24px; margin-bottom: 16px; color: #1e293b;">
                            <i class="fas fa-file-medical"></i> Riwayat Kesehatan
                        </h4>
                        
                        <div class="form-group">
                            <label>Riwayat Alergi</label>
                            <textarea name="riwayat_alergi" rows="2" placeholder="Riwayat alergi (obat, makanan, dll)"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Riwayat Penyakit</label>
                            <textarea name="riwayat_penyakit" rows="2" placeholder="Riwayat penyakit yang pernah diderita"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Riwayat Obat</label>
                            <textarea name="riwayat_obat" rows="2" placeholder="Riwayat konsumsi obat"></textarea>
                        </div>
                        
                        <div class="modal-actions">
                            <button type="button" class="btn-back-to-select" onclick="hideNewPatientForm()">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </button>
                            <button type="submit" class="btn-save-patient">
                                <i class="fas fa-save"></i> Simpan Pasien
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Pilih Layanan Modal -->
    <div id="selectServicesModal" class="modal-select-services" style="display:none;">
        <div class="modal-services-content">
            <div class="modal-services-header">
                <h2><i class="fas fa-syringe"></i> Pilih Layanan</h2>
                <button class="modal-close" onclick="closeServicesModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-services-body">
                <div class="selected-patient-info" id="selectedPatientInfo">
                    <!-- Akan diisi dengan info pasien -->
                </div>
                
                <div class="services-list" id="servicesList">
                    <div class="loading-services">
                        <i class="fas fa-spinner fa-spin"></i> Memuat layanan...
                    </div>
                </div>
                
                <div class="selected-services-summary">
                    <h4>Layanan Dipilih</h4>
                    <div id="selectedServicesList"></div>
                    <div class="total-price">
                        Total: <span id="totalPrice">Rp 0</span>
                    </div>
                </div>
            </div>
            
            <div class="modal-services-footer">
                <button type="button" class="btn-back-to-participant" onclick="closeServicesModal()">
                    Batal
                </button>
                <button type="button" class="btn-add-to-booking" onclick="addServicesToBooking()" id="btnAddServices" disabled>
                    <i class="fas fa-plus-circle"></i> Tambahkan ke Booking
                </button>
            </div>
        </div>
    </div>

    <!-- Popup overlay -->
    <div id="addDoctorPopup" class="popup-overlay" style="display:none;">
        <div class="popup-content">
            <h3>Pilih Dokter</h3>
            <div id="doctorContainer">
                <select class="doctorSelect">
                    <option value="">-- Pilih Dokter --</option>
                    <?php
                    $staff_result = $conn->query("
                        SELECT id, nama_lengkap, gelar, role 
                        FROM staff 
                        ORDER BY nama_lengkap ASC
                    ");
                    while ($staff = $staff_result->fetch_assoc()) {

                        $nama = $staff['nama_lengkap'];
                        $gelar = $staff['gelar'];
                        $role = $staff['role'];

                        // Gabung gelar + nama
                        $label = trim($gelar . ' ' . $nama);

                        // Tambah role kalau ada
                        if (!empty($role)) {
                            $label .= ' (' . $role . ')';
                        }

                        echo '<option value="'.$staff['id'].'">'
                            . htmlspecialchars($label)
                            . '</option>';
                    }

                    ?>
                </select>
            </div>
            <button type="button" onclick="addDoctorDropdown()">Tambah Tenaga Kerja</button>
            <button type="button" onclick="assignDoctors()">Selesai</button>
            <button type="button" onclick="closeAddDoctorPopup()">Batal</button>
        </div>
    </div>

    <!-- Popup Cetak Surat -->
<div id="popupCetakSurat" class="popup-cetak-surat">
    <div class="popup-cetak-content">
        <h3>Cetak Surat</h3>
        <p>Pilih Surat yang akan dibuat</p>

        <!-- List surat yang sudah dibuat (PRIORITAS UTAMA) -->
        <div class="surat-list" id="suratList">
            <div class="empty-surat">Memuat...</div>
        </div>

        <hr style="margin: 25px 0; border: none; border-top: 1px solid #e0e0e0;">

        <!-- Tombol buat surat baru -->
        <div class="popup-actions">
            <button type="button" class="btn-batal-popup" onclick="closeCetakSuratPopup()">Batal</button>
            <button type="button" class="btn-buat-baru" onclick="buatSuratBaru()">
                <i class="fas fa-plus"></i> Buat Surat Baru
            </button>
        </div>
    </div>
</div>

    <script>
        const bookingId = <?= $booking_id ?>;
        const participants = <?= json_encode(array_map(fn($p) => $p['patient_id'], $participants)) ?>;
        const bookingStatus = '<?= $booking['status'] ?>';
        const bookingStatusText = '<?= $booking['status'] == 'confirmed' ? 'dikonfirmasi' : ($booking['status'] == 'completed' ? 'selesai' : ($booking['status'] == 'cancelled' ? 'dibatalkan' : 'pending')) ?>';
    </script>
    <script src="../provinces.js"></script>
    <script src="js/detail_tabs.js"></script>
    <script src="js/detail_edit.js"></script>
    <script src="js/detail.js"></script>
    <script src="js/reschedule.js"></script>
    <script src="js/cetak_surat_detail.js"></script>
    <script src="js/add_participant.js"></script>
    <script src="js/sidebar-toggle.js"></script>
</body>
</html>