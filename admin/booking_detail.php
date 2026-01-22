<?php
session_start();
include "../config.php";

// Get booking ID
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id == 0) {
    header('Location: dashboard.php');
    exit;
}

// Get booking detail
$sql = "SELECT b.*, p.* 
        FROM bookings b 
        JOIN patients p ON b.patient_id = p.id 
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: dashboard.php');
    exit;
}

$booking = $result->fetch_assoc();

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
$disable_accept =
    ($dokter_count === 0) ||
    ($booking['status'] === 'confirmed');

// Get emails
$sql_emails = "SELECT email FROM patient_emails WHERE patient_id = ? ORDER BY is_primary DESC";
$stmt_e = $conn->prepare($sql_emails);
$stmt_e->bind_param('i', $booking['patient_id']);
$stmt_e->execute();
$emails = $stmt_e->get_result();

// Get phones
$sql_phones = "SELECT phone FROM patient_phones WHERE patient_id = ? ORDER BY is_primary DESC";
$stmt_p = $conn->prepare($sql_phones);
$stmt_p->bind_param('i', $booking['patient_id']);
$stmt_p->execute();
$phones = $stmt_p->get_result();

// Get address
$sql_addr = "SELECT * FROM patient_addresses WHERE patient_id = ? AND is_primary = 1 LIMIT 1";
$stmt_a = $conn->prepare($sql_addr);
$stmt_a->bind_param('i', $booking['patient_id']);
$stmt_a->execute();
$address = $stmt_a->get_result()->fetch_assoc();

// Get services
$sql_services = "SELECT nama_layanan FROM booking_services WHERE booking_id = ?";
$stmt_s = $conn->prepare($sql_services);
$stmt_s->bind_param('i', $booking_id);
$stmt_s->execute();
$services = $stmt_s->get_result();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="vaksinin-logo.png" alt="Vaksinin">
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Kalender</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Pasien</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="detail-header">
            <button onclick="window.location.href='dashboard.php'" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
            <h1>Detail Pesanan #<?php echo $booking['nomor_antrian']; ?></h1>

            <button class="btn-edit" onclick="editBooking(<?php echo $booking_id; ?>)">
                <i class="fas fa-edit"></i> Edit
            </button>
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

                <!-- Booking Info -->
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
                <div class="detail-section">
                    <h2><i class="fas fa-user"></i> Data Pasien</h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Nama Lengkap</label>
                            <p><?php echo htmlspecialchars($booking['nama_lengkap']); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Nama Panggilan</label>
                            <p><?php echo htmlspecialchars($booking['nama_panggilan'] ?: '-'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Tanggal Lahir</label>
                            <p><?php echo date('d F Y', strtotime($booking['tanggal_lahir'])); ?> (<?php echo $booking['usia']; ?> tahun)</p>
                        </div>
                        <div class="detail-item">
                            <label>Jenis Kelamin</label>
                            <p><?php echo $booking['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></p>
                        </div>
                        <div class="detail-item">
                            <label>NIK</label>
                            <p><?php echo htmlspecialchars($booking['nik'] ?: '-'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>No. Paspor</label>
                            <p><?php echo htmlspecialchars($booking['paspor'] ?: '-'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Kebangsaan</label>
                            <p><?php echo htmlspecialchars($booking['kebangsaan']); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Pekerjaan</label>
                            <p><?php echo htmlspecialchars($booking['pekerjaan'] ?: '-'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="detail-section">
                    <h2><i class="fas fa-phone"></i> Kontak</h2>
                    <div class="detail-grid">
                        <div class="detail-item full-width">
                            <label>Email</label>
                            <?php while ($e = $emails->fetch_assoc()): ?>
                                <p><?php echo htmlspecialchars($e['email']); ?></p>
                            <?php endwhile; ?>
                        </div>
                        <div class="detail-item full-width">
                            <label>Nomor HP</label>
                            <?php while ($ph = $phones->fetch_assoc()): ?>
                                <p><?php echo htmlspecialchars($ph['phone']); ?></p>
                            <?php endwhile; ?>
                        </div>
                        <?php if ($address): ?>
                        <div class="detail-item full-width">
                            <label>Alamat</label>
                            <p><?php echo htmlspecialchars($address['alamat']); ?></p>
                            <p><?php echo htmlspecialchars($address['kota']) . ', ' . htmlspecialchars($address['provinsi']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Services -->
                <div class="detail-section">
                    <h2><i class="fas fa-syringe"></i> Layanan yang Dipilih</h2>
                    <div class="services-grid">
                        <?php while ($srv = $services->fetch_assoc()): ?>
                            <div class="service-item">
                                <i class="fas fa-check-circle"></i>
                                <?php echo htmlspecialchars($srv['nama_layanan']); ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Medical History -->
                <div class="detail-section">
                    <h2><i class="fas fa-file-medical"></i> Riwayat Kesehatan</h2>
                    <div class="detail-grid">
                        <div class="detail-item full-width">
                            <label>Riwayat Alergi</label>
                            <p><?php echo htmlspecialchars($booking['riwayat_alergi'] ?: 'Tidak ada'); ?></p>
                        </div>
                        <div class="detail-item full-width">
                            <label>Riwayat Penyakit</label>
                            <p><?php echo htmlspecialchars($booking['riwayat_penyakit'] ?: 'Tidak ada'); ?></p>
                        </div>
                        <div class="detail-item full-width">
                            <label>Riwayat Obat</label>
                            <p><?php echo htmlspecialchars($booking['riwayat_obat'] ?: 'Tidak ada'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE PANEL -->
            <div class="detail-right">

                <!-- Tenaga Kerja -->
                <div class="side-card">
                    <div class="side-header">
                        <h3>Tenaga Kerja</h3>
                    </div>

                    <div class="side-body">
                        <?php if($staffs->num_rows > 0): ?>
                            <?php while($s = $staffs->fetch_assoc()): ?>
                                <div class="staff-item" id="staff-<?= $s['id'] ?>">
                                    <span><?= htmlspecialchars($s['gelar'].' '.$s['nama_lengkap']); ?></span>
                                    <button class="btn-delete-staff" onclick="removeStaff(<?= $booking_id ?>, <?= $s['id'] ?>)" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <span class="empty-text">Belum ada staff</span>
                        <?php endif; ?>
                    </div>

                    <button class="btn-add-worker" onclick="openAddDoctorPopup()">
                        <i class="fas fa-user-md"></i> Tambah Dokter
                    </button>
                </div>

                <!-- Action Buttons -->
                <div class="side-card">
                    <button class="btn-accept"
                            onclick="updateStatus(<?= $booking_id ?>, 'confirmed')"
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

    <!-- Popup overlay -->
    <div id="addDoctorPopup" class="popup-overlay" style="display:none;">
        <div class="popup-content">
            <h3>Pilih Dokter</h3>
            <div id="doctorContainer">
                <select class="doctorSelect">
                    <option value="">-- Pilih Dokter --</option>
                    <?php
                    $staff_result = $conn->query("SELECT id, nama_lengkap, gelar FROM staff ORDER BY nama_lengkap ASC");
                    while ($staff = $staff_result->fetch_assoc()) {
                        echo '<option value="'.$staff['id'].'">'.htmlspecialchars($staff['gelar'].' '.$staff['nama_lengkap']).'</option>';
                    }
                    ?>
                </select>
            </div>
            <button type="button" onclick="addDoctorDropdown()">Tambah Tenaga Kerja</button>
            <button type="button" onclick="assignDoctors()">Selesai</button>
            <button type="button" onclick="closeAddDoctorPopup()">Batal</button>
        </div>
    </div>

    <script>
        const bookingId = <?= $booking_id ?>;
    </script>
    <script src="js/detail.js"></script>
    <script src="js/reschedule.js"></script>
</body>
</html>