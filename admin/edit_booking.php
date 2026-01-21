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

// Get emails
$sql_emails = "SELECT email FROM patient_emails WHERE patient_id = ? ORDER BY is_primary DESC";
$stmt_e = $conn->prepare($sql_emails);
$stmt_e->bind_param('i', $booking['patient_id']);
$stmt_e->execute();
$emails_result = $stmt_e->get_result();
$emails = [];
while ($e = $emails_result->fetch_assoc()) {
    $emails[] = $e['email'];
}

// Get phones
$sql_phones = "SELECT phone FROM patient_phones WHERE patient_id = ? ORDER BY is_primary DESC";
$stmt_p = $conn->prepare($sql_phones);
$stmt_p->bind_param('i', $booking['patient_id']);
$stmt_p->execute();
$phones_result = $stmt_p->get_result();
$phones = [];
while ($p = $phones_result->fetch_assoc()) {
    $phones[] = $p['phone'];
}

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
$services_result = $stmt_s->get_result();
$selected_services = [];
while ($srv = $services_result->fetch_assoc()) {
    $selected_services[] = $srv['nama_layanan'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pesanan - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/detail.css">
    <link rel="stylesheet" href="css/edit.css">
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
            <div class="detail-header-left">
                <button onclick="window.location.href='booking_detail.php?id=<?php echo $booking_id; ?>'" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
                <h1>Edit Pesanan #<?php echo $booking['nomor_antrian']; ?></h1>
            </div>
        </div>

        <form action="update_booking.php" method="POST" class="edit-form">
            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
            <input type="hidden" name="patient_id" value="<?php echo $booking['patient_id']; ?>">

            <!-- Informasi Booking -->
            <div class="form-section">
                <h3><i class="fas fa-calendar-check"></i> Informasi Booking</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal Booking <span class="required">*</span></label>
                        <input type="date" name="tanggal_booking" value="<?php echo $booking['tanggal_booking']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Waktu <span class="required">*</span></label>
                        <input type="time" name="waktu_booking" value="<?php echo substr($booking['waktu_booking'], 0, 5); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tipe Layanan <span class="required">*</span></label>
                        <select name="service_type" required>
                            <option value="Home Service" <?php echo $booking['service_type'] == 'Home Service' ? 'selected' : ''; ?>>Home Service</option>
                            <option value="In Clinic" <?php echo $booking['service_type'] == 'In Clinic' ? 'selected' : ''; ?>>In Clinic</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status <span class="required">*</span></label>
                        <select name="status" required>
                            <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Data Pasien -->
            <div class="form-section">
                <h3><i class="fas fa-user"></i> Data Pasien</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($booking['nama_lengkap']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Panggilan</label>
                        <input type="text" name="nama_panggilan" value="<?php echo htmlspecialchars($booking['nama_panggilan']); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal Lahir <span class="required">*</span></label>
                        <input type="date" name="tanggal_lahir" value="<?php echo $booking['tanggal_lahir']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin <span class="required">*</span></label>
                        <select name="jenis_kelamin" required>
                            <option value="L" <?php echo $booking['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?php echo $booking['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>NIK</label>
                        <input type="text" name="nik" value="<?php echo htmlspecialchars($booking['nik']); ?>" maxlength="16">
                    </div>
                    <div class="form-group">
                        <label>No. Paspor</label>
                        <input type="text" name="paspor" value="<?php echo htmlspecialchars($booking['paspor']); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Kebangsaan</label>
                        <input type="text" name="kebangsaan" value="<?php echo htmlspecialchars($booking['kebangsaan']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Pekerjaan</label>
                        <input type="text" name="pekerjaan" value="<?php echo htmlspecialchars($booking['pekerjaan']); ?>">
                    </div>
                </div>
            </div>

            <!-- Kontak -->
            <div class="form-section">
                <h3><i class="fas fa-phone"></i> Kontak</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($emails[0] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor HP <span class="required">*</span></label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($phones[0] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat"><?php echo htmlspecialchars($address['alamat'] ?? ''); ?></textarea>
                </div>

                <input type="hidden" id="oldProvinsi" value="<?php echo htmlspecialchars($address['provinsi'] ?? ''); ?>">
                <input type="hidden" id="oldKota" value="<?php echo htmlspecialchars($address['kota'] ?? ''); ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Provinsi <span class="required">*</span></label>
                        <select name="provinsi" id="provinsiSelect" required>
                            <option value="">-- Pilih Provinsi --</option>
                        </select>
                        
                    </div>

                    <div class="form-group">
                        <label>Kota / Kabupaten <span class="required">*</span></label>
                        <select name="kota" id="kotaSelect" required>
                            <option value="">-- Pilih Kota --</option>
                        </select>
                        
                    </div>
                </div>
            </div>

            <!-- Riwayat Kesehatan -->
            <div class="form-section">
                <h3><i class="fas fa-file-medical"></i> Riwayat Kesehatan</h3>
                <div class="form-group">
                    <label>Riwayat Alergi</label>
                    <textarea name="riwayat_alergi" placeholder="Kosongkan jika tidak ada"><?php echo htmlspecialchars($booking['riwayat_alergi']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Riwayat Penyakit</label>
                    <textarea name="riwayat_penyakit" placeholder="Kosongkan jika tidak ada"><?php echo htmlspecialchars($booking['riwayat_penyakit']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Riwayat Obat</label>
                    <textarea name="riwayat_obat" placeholder="Kosongkan jika tidak ada"><?php echo htmlspecialchars($booking['riwayat_obat']); ?></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn-cancel-edit" onclick="window.location.href='booking_detail.php?id=<?php echo $booking_id; ?>'">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <script src="../provinces.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const provSelect = document.getElementById('provinsiSelect');
        const kotaSelect = document.getElementById('kotaSelect');

        const oldProv = document.getElementById('oldProvinsi').value;
        const oldKota = document.getElementById('oldKota').value;

        // Load provinsi
        provSelect.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
        Object.keys(indonesiaData).sort().forEach(prov => {
            const option = document.createElement('option');
            option.value = prov;
            option.textContent = prov;
            if (prov === oldProv) option.selected = true;
            provSelect.appendChild(option);
        });

        // Load kota sesuai provinsi lama
        if (oldProv && indonesiaData[oldProv]) {
            kotaSelect.innerHTML = '<option value="">-- Pilih Kota --</option>';
            indonesiaData[oldProv].sort().forEach(kota => {
                const option = document.createElement('option');
                option.value = kota;
                option.textContent = kota;
                if (kota === oldKota) option.selected = true;
                kotaSelect.appendChild(option);
            });
            kotaSelect.disabled = false;
        } else {
            kotaSelect.disabled = true;
        }

        // Event change provinsi
        provSelect.addEventListener('change', function () {
            const selectedProv = this.value;
            kotaSelect.innerHTML = '<option value="">-- Pilih Kota --</option>';
            if (!selectedProv || !indonesiaData[selectedProv]) {
                kotaSelect.disabled = true;
                return;
            }
            indonesiaData[selectedProv].sort().forEach(kota => {
                const option = document.createElement('option');
                option.value = kota;
                option.textContent = kota;
                kotaSelect.appendChild(option);
            });
            kotaSelect.disabled = false;
        });
    });
    </script>

</body>
</html>