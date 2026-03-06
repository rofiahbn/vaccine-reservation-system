<?php 
include "config.php";

// Cek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// Ambil data dari form
$booking_id        = intval($_POST['booking_id']);
$parent_booking_id = intval($_POST['parent_booking_id']);
$patient_id        = intval($_POST['patient_id']);

$nama_lengkap      = $_POST['nama_lengkap'];
$nama_panggilan    = $_POST['nama_panggilan'];
$tanggal_lahir     = $_POST['tanggal_lahir'];
$jenis_kelamin     = $_POST['jenis_kelamin'];

// PERBAIKAN: Ubah string kosong jadi NULL untuk field UNIQUE
$nik               = !empty($_POST['nik']) ? $_POST['nik'] : null;
$paspor            = !empty($_POST['paspor']) ? $_POST['paspor'] : null;
$kebangsaan = $_POST['kebangsaan'] ?? null;

// Kalau pilih "Lainnya"
if ($kebangsaan === 'Lainnya' && !empty($_POST['kebangsaan_lainnya'])) {
    $kebangsaan = $_POST['kebangsaan_lainnya'];
}

// Kalau kosong → NULL
if (empty($kebangsaan)) {
    $kebangsaan = null;
}

$pekerjaan         = !empty($_POST['pekerjaan']) ? $_POST['pekerjaan'] : null;

$alamat            = $_POST['alamat'];
$provinsi          = $_POST['provinsi'];
$kota              = $_POST['kota'];

$tanggal_booking   = $_POST['tanggal_booking'];
$waktu_booking     = $_POST['waktu_booking'];
$service_type      = $_POST['service_type'];
$status            = $_POST['status'];

$riwayat_alergi    = !empty($_POST['riwayat_alergi']) ? $_POST['riwayat_alergi'] : null;
$riwayat_penyakit  = !empty($_POST['riwayat_penyakit']) ? $_POST['riwayat_penyakit'] : null;
$riwayat_obat      = !empty($_POST['riwayat_obat']) ? $_POST['riwayat_obat'] : null;

// ============================
// UPDATE TABLE patients
// ============================
$usia = date('Y') - date('Y', strtotime($tanggal_lahir));
$kategori_usia = ($usia < 18) ? 'Anak' : 'Dewasa';

$stmt = $conn->prepare("
    UPDATE patients SET
        nama_lengkap = ?, 
        nama_panggilan = ?, 
        tanggal_lahir = ?, 
        usia = ?,
        kategori_usia = ?,
        jenis_kelamin = ?, 
        nik = ?, 
        paspor = ?, 
        kebangsaan = ?, 
        pekerjaan = ?,
        riwayat_alergi = ?, 
        riwayat_penyakit = ?, 
        riwayat_obat = ?,
        pelayanan = ?
    WHERE id = ?
");

if (!$stmt) {
    die("Error prepare patients: " . $conn->error);
}

$stmt->bind_param(
    "sssissssssssssi",
    $nama_lengkap,
    $nama_panggilan,
    $tanggal_lahir,
    $usia,
    $kategori_usia,
    $jenis_kelamin,
    $nik,
    $paspor,
    $kebangsaan,
    $pekerjaan,
    $riwayat_alergi,
    $riwayat_penyakit,
    $riwayat_obat,
    $service_type,
    $patient_id
);

if (!$stmt->execute()) {
    die("Error execute patients: " . $stmt->error);
}
$stmt->close();

// ============================
// UPDATE EMAILS (dengan Add/Delete)
// ============================
if (isset($_POST['email']) && is_array($_POST['email'])) {
    $email_db_ids = $_POST['email_db_id'];
    $emails = $_POST['email'];
    $email_primaries = $_POST['email_is_primary'];
    
    // Get existing email IDs
    $existing_ids = [];
    $stmt_existing = $conn->prepare("SELECT id FROM patient_emails WHERE patient_id = ?");
    $stmt_existing->bind_param("i", $patient_id);
    $stmt_existing->execute();
    $result_existing = $stmt_existing->get_result();
    while ($row = $result_existing->fetch_assoc()) {
        $existing_ids[] = $row['id'];
    }
    $stmt_existing->close();
    
    $processed_ids = [];
    
    // Process each email
    for ($i = 0; $i < count($emails); $i++) {
        $email_db_id = $email_db_ids[$i];
        $email = trim($emails[$i]);
        $is_primary = intval($email_primaries[$i]);
        
        if (empty($email)) continue;
        
        if ($email_db_id === 'new') {
            // INSERT new email
            $stmt = $conn->prepare("INSERT INTO patient_emails (patient_id, email, is_primary) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $patient_id, $email, $is_primary);
            $stmt->execute();
            $stmt->close();
        } else {
            // UPDATE existing email
            $email_db_id = intval($email_db_id);
            $processed_ids[] = $email_db_id;
            
            $stmt = $conn->prepare("UPDATE patient_emails SET email = ?, is_primary = ? WHERE id = ? AND patient_id = ?");
            $stmt->bind_param("siii", $email, $is_primary, $email_db_id, $patient_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // DELETE emails yang tidak ada di form (dihapus user)
    $to_delete = array_diff($existing_ids, $processed_ids);
    if (!empty($to_delete)) {
        $placeholders = str_repeat('?,', count($to_delete) - 1) . '?';
        $stmt = $conn->prepare("DELETE FROM patient_emails WHERE patient_id = ? AND id IN ($placeholders)");
        $types = str_repeat('i', count($to_delete) + 1);
        $params = array_merge([$patient_id], $to_delete);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();
    }
}

// ============================
// UPDATE PHONES (dengan Add/Delete)
// ============================
if (isset($_POST['phone']) && is_array($_POST['phone'])) {
    $phone_db_ids = $_POST['phone_db_id'];
    $phones = $_POST['phone'];
    $phone_primaries = $_POST['phone_is_primary'];
    
    // Get existing phone IDs
    $existing_ids = [];
    $stmt_existing = $conn->prepare("SELECT id FROM patient_phones WHERE patient_id = ?");
    $stmt_existing->bind_param("i", $patient_id);
    $stmt_existing->execute();
    $result_existing = $stmt_existing->get_result();
    while ($row = $result_existing->fetch_assoc()) {
        $existing_ids[] = $row['id'];
    }
    $stmt_existing->close();
    
    $processed_ids = [];
    
    // Process each phone
    for ($i = 0; $i < count($phones); $i++) {
        $phone_db_id = $phone_db_ids[$i];
        $phone = trim($phones[$i]);
        $is_primary = intval($phone_primaries[$i]);
        
        if (empty($phone)) continue;
        
        if ($phone_db_id === 'new') {
            // INSERT new phone
            $stmt = $conn->prepare("INSERT INTO patient_phones (patient_id, phone, is_primary) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $patient_id, $phone, $is_primary);
            $stmt->execute();
            $stmt->close();
        } else {
            // UPDATE existing phone
            $phone_db_id = intval($phone_db_id);
            $processed_ids[] = $phone_db_id;
            
            $stmt = $conn->prepare("UPDATE patient_phones SET phone = ?, is_primary = ? WHERE id = ? AND patient_id = ?");
            $stmt->bind_param("siii", $phone, $is_primary, $phone_db_id, $patient_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // DELETE phones yang tidak ada di form (dihapus user)
    $to_delete = array_diff($existing_ids, $processed_ids);
    if (!empty($to_delete)) {
        $placeholders = str_repeat('?,', count($to_delete) - 1) . '?';
        $stmt = $conn->prepare("DELETE FROM patient_phones WHERE patient_id = ? AND id IN ($placeholders)");
        $types = str_repeat('i', count($to_delete) + 1);
        $params = array_merge([$patient_id], $to_delete);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();
    }
}

// ============================
// UPDATE / INSERT patient_addresses
// ============================
$stmt_check = $conn->prepare("SELECT id FROM patient_addresses WHERE patient_id = ? AND is_primary = 1 LIMIT 1");
$stmt_check->bind_param("i", $patient_id);
$stmt_check->execute();
$res_check = $stmt_check->get_result();

if ($res_check->num_rows > 0) {
    // SUDAH ADA → UPDATE
    $stmt = $conn->prepare("UPDATE patient_addresses SET alamat = ?, provinsi = ?, kota = ? WHERE patient_id = ? AND is_primary = 1");
    $stmt->bind_param("sssi", $alamat, $provinsi, $kota, $patient_id);
    $stmt->execute();
    $stmt->close();
} else {
    // BELUM ADA → INSERT
    $stmt = $conn->prepare("INSERT INTO patient_addresses (patient_id, alamat, provinsi, kota, is_primary) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("isss", $patient_id, $alamat, $provinsi, $kota);
    $stmt->execute();
    $stmt->close();
}
$stmt_check->close();

// ============================
// UPDATE bookings
// ============================
$stmt = $conn->prepare("
    UPDATE bookings SET 
        tanggal_booking = ?, 
        waktu_booking = ?, 
        service_type = ?, 
        status = ?
    WHERE id = ?
");
$stmt->bind_param("ssssi", $tanggal_booking, $waktu_booking, $service_type, $status, $booking_id);
$stmt->execute();
$stmt->close();

// ============================
// UPDATE SERVICES - PERBAIKAN UTAMA
// ============================
if (isset($_POST['service_master_id']) && is_array($_POST['service_master_id'])) {
    
    $service_db_ids = $_POST['service_db_id'];
    $service_master_ids = $_POST['service_master_id'];
    $nama_layanans = $_POST['nama_layanan'];
    
    // Get existing service IDs untuk booking ini
    $existing_ids = [];
    $stmt_existing = $conn->prepare("SELECT id FROM booking_services WHERE booking_id = ? AND patient_id = ?");
    if ($stmt_existing) {
        $stmt_existing->bind_param("ii", $booking_id, $patient_id);
        $stmt_existing->execute();
        $result_existing = $stmt_existing->get_result();
        while ($row = $result_existing->fetch_assoc()) {
            $existing_ids[] = $row['id'];
        }
        $stmt_existing->close();
    } else {
        error_log("Error prepare existing services: " . $conn->error);
    }
    
    $processed_ids = [];
    
    // Process each service
    for ($i = 0; $i < count($service_master_ids); $i++) {
        
        $service_db_id = $service_db_ids[$i];
        $service_master_id = intval($service_master_ids[$i]);
        $nama_layanan = trim($nama_layanans[$i]);
        
        if (empty($service_master_id) || empty($nama_layanan)) continue;
        
        // Get service details from master
        $stmt_master = $conn->prepare("SELECT * FROM services WHERE id = ?");
        if ($stmt_master) {
            $stmt_master->bind_param("i", $service_master_id);
            $stmt_master->execute();
            $service_data = $stmt_master->get_result()->fetch_assoc();
            $stmt_master->close();
        } else {
            error_log("Error prepare master service: " . $conn->error);
            continue;
        }
        
        if (!$service_data) continue;
        
        $harga = $service_data['harga'];
        $diskon = $service_data['diskon'] ?? 0;
        $diskon_tipe = $service_data['diskon_tipe'] ?? 'persen';
        
        // Calculate total
        if ($diskon_tipe == 'persen') {
            $total = $harga - ($harga * $diskon / 100);
        } else {
            $total = $harga - $diskon;
        }
        
        if ($total < 0) $total = 0;
        
        if ($service_db_id === 'new') {
            // INSERT new service
            $stmt = $conn->prepare("
                INSERT INTO booking_services 
                (booking_id, patient_id, service_id, nama_layanan, harga, diskon, diskon_tipe, total) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt) {
                $stmt->bind_param("iiisddsd", 
                    $booking_id,
                    $patient_id,
                    $service_master_id,
                    $nama_layanan,
                    $harga,
                    $diskon,
                    $diskon_tipe,
                    $total
                );
                
                if (!$stmt->execute()) {
                    error_log("Error insert service: " . $stmt->error);
                }
                $stmt->close();
            } else {
                error_log("Error prepare insert service: " . $conn->error);
            }
        } else {
            // UPDATE existing service
            $service_db_id = intval($service_db_id);
            $processed_ids[] = $service_db_id;
            
            $stmt = $conn->prepare("
                UPDATE booking_services SET 
                    service_id = ?, 
                    nama_layanan = ?,
                    harga = ?,
                    diskon = ?,
                    diskon_tipe = ?,
                    total = ?
                WHERE id = ? AND booking_id = ? AND patient_id = ?
            ");
            if ($stmt) {
                $stmt->bind_param("isddsdiii", 
                    $service_master_id,
                    $nama_layanan,
                    $harga,
                    $diskon,
                    $diskon_tipe,
                    $total,
                    $service_db_id,
                    $booking_id,
                    $patient_id
                );
                
                if (!$stmt->execute()) {
                    error_log("Error update service: " . $stmt->error);
                }
                $stmt->close();
            } else {
                error_log("Error prepare update service: " . $conn->error);
            }
        }
    }
    
    // DELETE services yang tidak ada di form (dihapus user)
    $to_delete = array_diff($existing_ids, $processed_ids);
    if (!empty($to_delete)) {
        $placeholders = str_repeat('?,', count($to_delete) - 1) . '?';
        $delete_sql = "DELETE FROM booking_services WHERE booking_id = ? AND patient_id = ? AND id IN ($placeholders)";
        $stmt = $conn->prepare($delete_sql);
        
        if ($stmt) {
            $types = 'ii' . str_repeat('i', count($to_delete));
            $params = array_merge([$booking_id, $patient_id], $to_delete);
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                error_log("Error delete services: " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Error prepare delete services: " . $conn->error);
        }
    }
}

// Redirect kembali ke detail booking (gunakan parent_booking_id)
header("Location: booking_detail.php?id=$parent_booking_id");
exit;
?>