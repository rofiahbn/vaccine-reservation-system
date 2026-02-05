<?php
session_start();
include "../config.php";

$parent_booking_id = intval($_GET['booking_id'] ?? 0);
$patient_id = intval($_GET['patient_id'] ?? 0);

error_log("edit_booking.php - Parent Booking ID: $parent_booking_id, Patient ID: $patient_id");

if ($parent_booking_id == 0 || $patient_id == 0) {
    error_log("ERROR: Missing parameters, redirecting to dashboard");
    header('Location: dashboard.php');
    exit;
}

/* Cari booking record yang spesifik untuk patient ini */
$sql = "
SELECT 
    b.id AS booking_record_id,
    b.*,
    p.id AS patient_real_id,
    p.*
FROM bookings b
JOIN patients p ON b.patient_id = p.id
WHERE (b.id = ? OR b.parent_id = ?) AND p.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $parent_booking_id, $parent_booking_id, $patient_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    error_log("ERROR: No booking found for parent_booking_id: $parent_booking_id, patient_id: $patient_id");
    header('Location: dashboard.php');
    exit;
}

$booking = $result->fetch_assoc();
$booking_record_id = $booking['booking_record_id'];

// Get ALL emails
$sql_emails = "SELECT id, email, is_primary FROM patient_emails WHERE patient_id = ? ORDER BY is_primary DESC, id ASC";
$stmt_e = $conn->prepare($sql_emails);
$stmt_e->bind_param('i', $booking['patient_id']);
$stmt_e->execute();
$emails_result = $stmt_e->get_result();
$emails = [];
while ($e = $emails_result->fetch_assoc()) {
    $emails[] = $e;
}

// Get ALL phones
$sql_phones = "SELECT id, phone, is_primary FROM patient_phones WHERE patient_id = ? ORDER BY is_primary DESC, id ASC";
$stmt_p = $conn->prepare($sql_phones);
$stmt_p->bind_param('i', $booking['patient_id']);
$stmt_p->execute();
$phones_result = $stmt_p->get_result();
$phones = [];
while ($p = $phones_result->fetch_assoc()) {
    $phones[] = $p;
}

// Get address
$sql_addr = "SELECT * FROM patient_addresses WHERE patient_id = ? AND is_primary = 1 LIMIT 1";
$stmt_a = $conn->prepare($sql_addr);
$stmt_a->bind_param('i', $booking['patient_id']);
$stmt_a->execute();
$address = $stmt_a->get_result()->fetch_assoc();

// Get services (FULL DATA)
$sql_services = "SELECT * FROM booking_services WHERE booking_id = ? AND patient_id = ?";
$stmt_s = $conn->prepare($sql_services);
$stmt_s->bind_param('ii', $booking_record_id, $booking['patient_id']);
$stmt_s->execute();
$services_result = $stmt_s->get_result();
$services = [];
while ($srv = $services_result->fetch_assoc()) {
    $services[] = $srv;
}

// Get master services
$sql_master = "SELECT * FROM services ORDER BY kategori, nama_layanan";
$result_master = $conn->query($sql_master);

$master_services = [];
while ($ms = $result_master->fetch_assoc()) {
    $master_services[] = $ms;
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
            <a href="#" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Pasien</span>
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
            <div class="detail-header-left">
                <button onclick="window.location.href='booking_detail.php?id=<?php echo $parent_booking_id; ?>'" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
                <h1>Edit Pesanan #<?php echo $booking['nomor_antrian']; ?></h1>
            </div>
        </div>

        <form action="update_booking.php" method="POST" class="edit-form" id="editForm">
            <input type="hidden" name="booking_id" value="<?php echo $booking_record_id; ?>">
            <input type="hidden" name="parent_booking_id" value="<?php echo $parent_booking_id; ?>">
            <input type="hidden" name="patient_id" value="<?php echo $booking['patient_id']; ?>">
            <input type="hidden" name="status" value="<?php echo $booking['status']; ?>">

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
                </div>

                <!-- DAFTAR LAYANAN dengan Add/Delete -->
                <div class="form-group">
                    <label>Pesanan yang Dipilih <span class="required">*</span></label>
                    
                    <div id="servicesContainer">
                        <?php if (count($services) > 0): ?>
                            <?php foreach ($services as $idx => $srv): ?>
                                <div class="service-item-wrapper" data-service-index="<?= $idx ?>">
                                    <div class="dynamic-field-group">
                                        <!-- Hidden field untuk ID service (untuk update) -->
                                        <input type="hidden" name="service_db_id[]" value="<?= $srv['id'] ?>">
                                        
                                        <select name="service_master_id[]" 
                                                onchange="updateServiceName(this, <?= $idx ?>)"
                                                required>
                                            <option value="">-- Pilih Layanan --</option>
                                            <?php foreach ($master_services as $ms): ?>
                                                <option value="<?= $ms['id'] ?>"
                                                    data-name="<?= htmlspecialchars($ms['nama_layanan']) ?>"
                                                    <?= $ms['id'] == $srv['service_id'] ? 'selected' : '' ?>>
                                                    [<?= $ms['kategori'] ?>] <?= $ms['nama_layanan'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <input type="hidden" 
                                            name="nama_layanan[]" 
                                            id="nama_layanan_<?= $idx ?>" 
                                            value="<?= htmlspecialchars($srv['nama_layanan']) ?>">

                                        <button type="button" 
                                                class="btn-remove-field" 
                                                onclick="removeService(this)"
                                                <?= count($services) <= 1 ? 'disabled title="Minimal harus ada 1 layanan"' : '' ?>>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="service-item-wrapper" data-service-index="0">
                                <div class="dynamic-field-group">
                                    <input type="hidden" name="service_db_id[]" value="new">
                                    
                                    <select name="service_master_id[]" 
                                            onchange="updateServiceName(this, 0)"
                                            required>
                                        <option value="">-- Pilih Layanan --</option>
                                        <?php foreach ($master_services as $ms): ?>
                                            <option value="<?= $ms['id'] ?>"
                                                data-name="<?= htmlspecialchars($ms['nama_layanan']) ?>">
                                                [<?= $ms['kategori'] ?>] <?= $ms['nama_layanan'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <input type="hidden" name="nama_layanan[]" id="nama_layanan_0" value="">

                                    <button type="button" 
                                            class="btn-remove-field" 
                                            onclick="removeService(this)"
                                            disabled 
                                            title="Minimal harus ada 1 layanan">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="btn-add-field" onclick="addService()">
                        <i class="fas fa-plus"></i> Tambah Layanan
                    </button>
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

                        <?php 
                            $kebangsaan_list = [
                                "Indonesia",
                                "Malaysia",
                                "Singapore",
                                "Thailand",
                                "Filipina",
                                "Vietnam",
                                "Brunei",
                                "China",
                                "India",
                                "Arab Saudi",
                                "Pakistan",
                                "Bangladesh",
                                "Australia",
                                "United States",
                                "United Kingdom",
                                "Jepang",
                                "Korea Selatan",
                                "Lainnya"
                            ];
                        ?>

                        <select name="kebangsaan" id="kebangsaanSelect">

                        <?php foreach ($kebangsaan_list as $negara): ?>

                        <option value="<?= $negara ?>"
                            <?= (
                                $booking['kebangsaan'] === $negara ||
                                (
                                    $negara === 'Lainnya' &&
                                    !in_array($booking['kebangsaan'], $kebangsaan_list)
                                )
                            ) ? 'selected' : '' ?>>

                            <?= $negara ?>

                        </option>

                        <?php endforeach; ?>

                        </select>

                        <?php
                        $is_lainnya = !in_array($booking['kebangsaan'], $kebangsaan_list);
                        ?>

                        <input 
                            type="text" 
                            name="kebangsaan_lainnya" 
                            id="kebangsaanLainnya"
                            placeholder="Isi kebangsaan lainnya"
                            value="<?= $is_lainnya ? htmlspecialchars($booking['kebangsaan']) : '' ?>"
                            style="display: <?= $is_lainnya ? 'block' : 'none' ?>; margin-top:8px;">

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
                
                <!-- EMAIL dengan Add/Delete -->
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    
                    <div id="emailsContainer">
                        <?php if (count($emails) > 0): ?>
                            <?php foreach ($emails as $idx => $e): ?>
                                <div class="dynamic-field-group">
                                    <input type="hidden" name="email_db_id[]" value="<?= $e['id'] ?>">
                                    <input type="hidden" name="email_is_primary[]" value="<?= $e['is_primary'] ?>">
                                    
                                    <input type="email" 
                                           name="email[]" 
                                           value="<?= htmlspecialchars($e['email']) ?>" 
                                           placeholder="email@example.com"
                                           required>
                                    
                                    <?php if ($e['is_primary']): ?>
                                        <span class="primary-badge">Primary</span>
                                    <?php endif; ?>
                                    
                                    <button type="button" 
                                            class="btn-remove-field" 
                                            onclick="removeEmail(this)"
                                            <?= count($emails) <= 1 ? 'disabled title="Minimal harus ada 1 email"' : '' ?>>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="dynamic-field-group">
                                <input type="hidden" name="email_db_id[]" value="new">
                                <input type="hidden" name="email_is_primary[]" value="1">
                                <input type="email" name="email[]" placeholder="email@example.com" required>
                                <span class="primary-badge">Primary</span>
                                <button type="button" class="btn-remove-field" onclick="removeEmail(this)" disabled title="Minimal harus ada 1 email">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" class="btn-add-field" onclick="addEmail()">
                        <i class="fas fa-plus"></i> Tambah Email
                    </button>
                </div>

                <!-- PHONE dengan Add/Delete -->
                <div class="form-group">
                    <label>Nomor HP <span class="required">*</span></label>
                    
                    <div id="phonesContainer">
                        <?php if (count($phones) > 0): ?>
                            <?php foreach ($phones as $idx => $ph): ?>
                                <div class="dynamic-field-group">
                                    <input type="hidden" name="phone_db_id[]" value="<?= $ph['id'] ?>">
                                    <input type="hidden" name="phone_is_primary[]" value="<?= $ph['is_primary'] ?>">
                                    
                                    <input type="tel" 
                                           name="phone[]" 
                                           value="<?= htmlspecialchars($ph['phone']) ?>" 
                                           placeholder="08xxxxxxxxxx"
                                           required>
                                    
                                    <?php if ($ph['is_primary']): ?>
                                        <span class="primary-badge">Primary</span>
                                    <?php endif; ?>
                                    
                                    <button type="button" 
                                            class="btn-remove-field" 
                                            onclick="removePhone(this)"
                                            <?= count($phones) <= 1 ? 'disabled title="Minimal harus ada 1 nomor HP"' : '' ?>>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="dynamic-field-group">
                                <input type="hidden" name="phone_db_id[]" value="new">
                                <input type="hidden" name="phone_is_primary[]" value="1">
                                <input type="tel" name="phone[]" placeholder="08xxxxxxxxxx" required>
                                <span class="primary-badge">Primary</span>
                                <button type="button" class="btn-remove-field" onclick="removePhone(this)" disabled title="Minimal harus ada 1 nomor HP">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" class="btn-add-field" onclick="addPhone()">
                        <i class="fas fa-plus"></i> Tambah Nomor HP
                    </button>
                </div>

                <!-- ADDRESS -->
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
                <button type="button" class="btn-cancel-edit" onclick="window.location.href='booking_detail.php?id=<?php echo $parent_booking_id; ?>'">
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
    // GLOBAL VARIABLES
    let serviceIndex = <?= count($services) ?>;
    const masterServices = <?= json_encode($master_services) ?>;

    // ================================
    // PROVINCE & CITY HANDLING
    // ================================
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

    // ================================
    // SERVICE FUNCTIONS
    // ================================
    function updateServiceName(select, idx) {
        const selectedOption = select.options[select.selectedIndex];
        const nama = selectedOption.getAttribute('data-name') || '';
        document.getElementById("nama_layanan_" + idx).value = nama;
    }

    function addService() {
        const container = document.getElementById('servicesContainer');
        const newIndex = serviceIndex++;
        
        const wrapper = document.createElement('div');
        wrapper.className = 'service-item-wrapper';
        wrapper.setAttribute('data-service-index', newIndex);
        
        let optionsHTML = '<option value="">-- Pilih Layanan --</option>';
        masterServices.forEach(ms => {
            optionsHTML += `<option value="${ms.id}" data-name="${escapeHtml(ms.nama_layanan)}">[${ms.kategori}] ${escapeHtml(ms.nama_layanan)}</option>`;
        });
        
        wrapper.innerHTML = `
            <div class="dynamic-field-group">
                <input type="hidden" name="service_db_id[]" value="new">
                <select name="service_master_id[]" onchange="updateServiceName(this, ${newIndex})" required>
                    ${optionsHTML}
                </select>
                <input type="hidden" name="nama_layanan[]" id="nama_layanan_${newIndex}" value="">
                <button type="button" class="btn-remove-field" onclick="removeService(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        container.appendChild(wrapper);
        updateServiceButtons();
    }

    function removeService(button) {
        const wrapper = button.closest('.service-item-wrapper');
        wrapper.remove();
        updateServiceButtons();
    }

    function updateServiceButtons() {
        const services = document.querySelectorAll('#servicesContainer .service-item-wrapper');
        const buttons = document.querySelectorAll('#servicesContainer .btn-remove-field');
        
        buttons.forEach(btn => {
            if (services.length <= 1) {
                btn.disabled = true;
                btn.title = 'Minimal harus ada 1 layanan';
            } else {
                btn.disabled = false;
                btn.title = '';
            }
        });
    }

    // ================================
    // EMAIL FUNCTIONS
    // ================================
    function addEmail() {
        const container = document.getElementById('emailsContainer');
        
        const div = document.createElement('div');
        div.className = 'dynamic-field-group';
        div.innerHTML = `
            <input type="hidden" name="email_db_id[]" value="new">
            <input type="hidden" name="email_is_primary[]" value="0">
            <input type="email" name="email[]" placeholder="email@example.com" required>
            <button type="button" class="btn-remove-field" onclick="removeEmail(this)">
                <i class="fas fa-trash"></i>
            </button>
        `;
        
        container.appendChild(div);
        updateEmailButtons();
    }

    function removeEmail(button) {
        const group = button.closest('.dynamic-field-group');
        group.remove();
        updateEmailButtons();
    }

    function updateEmailButtons() {
        const emails = document.querySelectorAll('#emailsContainer .dynamic-field-group');
        const buttons = document.querySelectorAll('#emailsContainer .btn-remove-field');
        
        buttons.forEach(btn => {
            if (emails.length <= 1) {
                btn.disabled = true;
                btn.title = 'Minimal harus ada 1 email';
            } else {
                btn.disabled = false;
                btn.title = '';
            }
        });
    }

    // ================================
    // PHONE FUNCTIONS
    // ================================
    function addPhone() {
        const container = document.getElementById('phonesContainer');
        
        const div = document.createElement('div');
        div.className = 'dynamic-field-group';
        div.innerHTML = `
            <input type="hidden" name="phone_db_id[]" value="new">
            <input type="hidden" name="phone_is_primary[]" value="0">
            <input type="tel" name="phone[]" placeholder="08xxxxxxxxxx" required>
            <button type="button" class="btn-remove-field" onclick="removePhone(this)">
                <i class="fas fa-trash"></i>
            </button>
        `;
        
        container.appendChild(div);
        updatePhoneButtons();
    }

    function removePhone(button) {
        const group = button.closest('.dynamic-field-group');
        group.remove();
        updatePhoneButtons();
    }

    function updatePhoneButtons() {
        const phones = document.querySelectorAll('#phonesContainer .dynamic-field-group');
        const buttons = document.querySelectorAll('#phonesContainer .btn-remove-field');
        
        buttons.forEach(btn => {
            if (phones.length <= 1) {
                btn.disabled = true;
                btn.title = 'Minimal harus ada 1 nomor HP';
            } else {
                btn.disabled = false;
                btn.title = '';
            }
        });
    }

    // ================================
    // HELPER FUNCTIONS
    // ================================
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize button states
    document.addEventListener('DOMContentLoaded', function() {
        updateServiceButtons();
        updateEmailButtons();
        updatePhoneButtons();
    });

    document.addEventListener('DOMContentLoaded', function() {

        const select = document.getElementById('kebangsaanSelect');
        const inputLainnya = document.getElementById('kebangsaanLainnya');

        function toggleKebangsaan() {
            if (select.value === 'Lainnya') {
                inputLainnya.style.display = 'block';
            } else {
                inputLainnya.style.display = 'none';
                inputLainnya.value = '';
            }
        }

        toggleKebangsaan();
        select.addEventListener('change', toggleKebangsaan);

    });

    </script>
    <script src="js/sidebar-toggle.js"></script>
</body>
</html>