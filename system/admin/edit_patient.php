<?php 
include "config.php";

date_default_timezone_set('Asia/Jakarta');

$current_page = 'patients.php';

// Get patient ID from URL
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($patient_id == 0) {
    $_SESSION['error'] = "ID pasien tidak ditemukan";
    header("Location: patients.php");
    exit;
}

// ================= AMBIL DATA PASIEN =================
$query_detail = "
    SELECT 
        p.*
    FROM patients p
    WHERE p.id = ?
";
$stmt = $conn->prepare($query_detail);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

if (!$patient) {
    $_SESSION['error'] = "Data pasien tidak ditemukan";
    header("Location: patients.php");
    exit;
}

// ================= AMBIL EMAIL =================
$query_emails = "SELECT id, email, is_primary FROM patient_emails WHERE patient_id = ? ORDER BY is_primary DESC, id ASC";
$stmt = $conn->prepare($query_emails);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$result_emails = $stmt->get_result();
$emails = [];
while ($row = $result_emails->fetch_assoc()) {
    $emails[] = $row;
}

// ================= AMBIL TELEPON =================
$query_phones = "SELECT id, phone, is_primary FROM patient_phones WHERE patient_id = ? ORDER BY is_primary DESC, id ASC";
$stmt = $conn->prepare($query_phones);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$result_phones = $stmt->get_result();
$phones = [];
while ($row = $result_phones->fetch_assoc()) {
    $phones[] = $row;
}

// ================= AMBIL ALAMAT UTAMA =================
$query_address = "SELECT * FROM patient_addresses WHERE patient_id = ? AND is_primary = 1 LIMIT 1";
$stmt = $conn->prepare($query_address);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$result_address = $stmt->get_result();
$address = $result_address->fetch_assoc();

// ================= PROSES UPDATE =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // UPDATE DATA PRIBADI
    if ($action === 'update_personal') {
        $nama_lengkap = $_POST['nama_lengkap'];
        $nama_panggilan = $_POST['nama_panggilan'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $kategori_usia = $_POST['kategori_usia'];
        $kebangsaan = $_POST['kebangsaan'];
        $nik = $_POST['nik'];
        $paspor = $_POST['paspor'];
        $no_rekam_medis = $_POST['no_rekam_medis'];
        
        $update_sql = "UPDATE patients SET 
            nama_lengkap = ?,
            nama_panggilan = ?,
            tanggal_lahir = ?,
            jenis_kelamin = ?,
            kategori_usia = ?,
            kebangsaan = ?,
            nik = ?,
            paspor = ?,
            no_rekam_medis = ?,
            updated_at = NOW()
            WHERE id = ?";
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('sssssssssi', 
            $nama_lengkap,
            $nama_panggilan,
            $tanggal_lahir,
            $jenis_kelamin,
            $kategori_usia,
            $kebangsaan,
            $nik,
            $paspor,
            $no_rekam_medis,
            $patient_id
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data pribadi berhasil diperbarui";
        } else {
            $_SESSION['error'] = "Gagal memperbarui data pribadi: " . $conn->error;
        }
        header("Location: edit_patient.php?id=" . $patient_id);
        exit;
    }

    // UPDATE KONTAK & PEKERJAAN
    if ($action === 'update_contact') {
        $pekerjaan = $_POST['pekerjaan'];
        $nama_wali = $_POST['nama_wali'];
        
        $update_sql = "UPDATE patients SET 
            pekerjaan = ?,
            nama_wali = ?,
            updated_at = NOW()
            WHERE id = ?";
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ssi', $pekerjaan, $nama_wali, $patient_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data kontak berhasil diperbarui";
        } else {
            $_SESSION['error'] = "Gagal memperbarui data kontak: " . $conn->error;
        }
        header("Location: edit_patient.php?id=" . $patient_id);
        exit;
    }

    // UPDATE RIWAYAT KESEHATAN
    if ($action === 'update_health') {
        $tidak_ada_alergi = isset($_POST['tidak_ada_alergi']) ? 1 : 0;
        $tidak_ada_penyakit = isset($_POST['tidak_ada_penyakit']) ? 1 : 0;
        $tidak_ada_obat = isset($_POST['tidak_ada_obat']) ? 1 : 0;
        
        $riwayat_alergi = $tidak_ada_alergi ? null : $_POST['riwayat_alergi'];
        $riwayat_penyakit = $tidak_ada_penyakit ? null : $_POST['riwayat_penyakit'];
        $riwayat_obat = $tidak_ada_obat ? null : $_POST['riwayat_obat'];
        
        $update_sql = "UPDATE patients SET 
            riwayat_alergi = ?,
            riwayat_penyakit = ?,
            riwayat_obat = ?,
            updated_at = NOW()
            WHERE id = ?";
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('sssi', $riwayat_alergi, $riwayat_penyakit, $riwayat_obat, $patient_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Riwayat kesehatan berhasil diperbarui";
        } else {
            $_SESSION['error'] = "Gagal memperbarui riwayat kesehatan: " . $conn->error;
        }
        header("Location: edit_patient.php?id=" . $patient_id);
        exit;
    }

    // TAMBAH EMAIL
    if ($action === 'add_email') {
        $email = $_POST['email'];
        $is_primary = isset($_POST['is_primary']) ? 1 : 0;
        
        // Jika set sebagai primary, update semua email lain jadi tidak primary
        if ($is_primary) {
            $reset_primary = "UPDATE patient_emails SET is_primary = 0 WHERE patient_id = ?";
            $stmt_reset = $conn->prepare($reset_primary);
            $stmt_reset->bind_param('i', $patient_id);
            $stmt_reset->execute();
        }
        
        $insert_sql = "INSERT INTO patient_emails (patient_id, email, is_primary) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param('isi', $patient_id, $email, $is_primary);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Email berhasil ditambahkan";
        } else {
            $_SESSION['error'] = "Gagal menambahkan email: " . $conn->error;
        }
        header("Location: edit_patient.php?id=" . $patient_id);
        exit;
    }

    // HAPUS EMAIL
    if ($action === 'delete_email') {
        $email_id = $_POST['email_id'];
        
        // Cek apakah email yang dihapus adalah primary
        $check_sql = "SELECT is_primary FROM patient_emails WHERE id = ? AND patient_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('ii', $email_id, $patient_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $email_data = $check_result->fetch_assoc();
        
        $delete_sql = "DELETE FROM patient_emails WHERE id = ? AND patient_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param('ii', $email_id, $patient_id);
        
        if ($stmt->execute()) {
            // Jika yang dihapus adalah primary, set email pertama sebagai primary
            if ($email_data && $email_data['is_primary'] == 1) {
                $set_new_primary = "UPDATE patient_emails SET is_primary = 1 WHERE patient_id = ? LIMIT 1";
                $stmt_primary = $conn->prepare($set_new_primary);
                $stmt_primary->bind_param('i', $patient_id);
                $stmt_primary->execute();
            }
            $_SESSION['success'] = "Email berhasil dihapus";
        } else {
            $_SESSION['error'] = "Gagal menghapus email";
        }
        header("Location: edit_patient.php?id=" . $patient_id);
        exit;
    }

    // SET PRIMARY EMAIL
    if ($action === 'set_primary_email') {
        $email_id = $_POST['email_id'];
        
        // Reset semua email jadi tidak primary
        $reset_sql = "UPDATE patient_emails SET is_primary = 0 WHERE patient_id = ?";
        $stmt_reset = $conn->prepare($reset_sql);
        $stmt_reset->bind_param('i', $patient_id);
        $stmt_reset->execute();
        
        // Set email yang dipilih jadi primary
        $set_sql = "UPDATE patient_emails SET is_primary = 1 WHERE id = ? AND patient_id = ?";
        $stmt = $conn->prepare($set_sql);
        $stmt->bind_param('ii', $email_id, $patient_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Email utama berhasil diubah";
        } else {
            $_SESSION['error'] = "Gagal mengubah email utama";
        }
        header("Location: edit_patient.php?id=" . $patient_id);
        exit;
    }

    // TAMBAH TELEPON
    if ($action === 'add_phone') {
        $phone = $_POST['phone'];
        $is_primary = isset($_POST['is_primary']) ? 1 : 0;
        
        if ($is_primary) {
            $reset_primary = "UPDATE patient_phones SET is_primary = 0 WHERE patient_id = ?";
            $stmt_reset = $conn->prepare($reset_primary);
            $stmt_reset->bind_param('i', $patient_id);
            $stmt_reset->execute();
        }
        
        $insert_sql = "INSERT INTO patient_phones (patient_id, phone, is_primary) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param('isi', $patient_id, $phone, $is_primary);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Nomor telepon berhasil ditambahkan";
        } else {
            $_SESSION['error'] = "Gagal menambahkan nomor telepon: " . $conn->error;
        }
        header("Location: edit_patient.php?id=" . $patient_id);
        exit;
    }

    // HAPUS TELEPON
    if ($action === 'delete_phone') {
        $phone_id = $_POST['phone_id'];
        
        $check_sql = "SELECT is_primary FROM patient_phones WHERE id = ? AND patient_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('ii', $phone_id, $patient_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $phone_data = $check_result->fetch_assoc();
        
        $delete_sql = "DELETE FROM patient_phones WHERE id = ? AND patient_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param('ii', $phone_id, $patient_id);
        
        if ($stmt->execute()) {
            if ($phone_data && $phone_data['is_primary'] == 1) {
                $set_new_primary = "UPDATE patient_phones SET is_primary = 1 WHERE patient_id = ? LIMIT 1";
                $stmt_primary = $conn->prepare($set_new_primary);
                $stmt_primary->bind_param('i', $patient_id);
                $stmt_primary->execute();
            }
            $_SESSION['success'] = "Nomor telepon berhasil dihapus";
        } else {
            $_SESSION['error'] = "Gagal menghapus nomor telepon";
        }
        header("Location: edit_patient.php?id=" . $patient_id);
        exit;
    }

    // SET PRIMARY TELEPON
    if ($action === 'set_primary_phone') {
        $phone_id = $_POST['phone_id'];
        
        $reset_sql = "UPDATE patient_phones SET is_primary = 0 WHERE patient_id = ?";
        $stmt_reset = $conn->prepare($reset_sql);
        $stmt_reset->bind_param('i', $patient_id);
        $stmt_reset->execute();
        
        $set_sql = "UPDATE patient_phones SET is_primary = 1 WHERE id = ? AND patient_id = ?";
        $stmt = $conn->prepare($set_sql);
        $stmt->bind_param('ii', $phone_id, $patient_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Nomor telepon utama berhasil diubah";
        } else {
            $_SESSION['error'] = "Gagal mengubah nomor telepon utama";
        }
        header("Location: edit_patient.php?id=" . $patient_id);
        exit;
    }

    // UPDATE ALAMAT
    if ($action === 'update_address') {
        $alamat = $_POST['alamat'];
        $provinsi = $_POST['provinsi'];
        $kota = $_POST['kota'];
        $kecamatan = $_POST['kecamatan'];
        $kelurahan = $_POST['kelurahan'];
        $kode_pos = $_POST['kode_pos'];
        
        if ($address) {
            // Update alamat yang ada
            $update_sql = "UPDATE patient_addresses SET 
                alamat = ?,
                provinsi = ?,
                kota = ?,
                kecamatan = ?,
                kelurahan = ?,
                kode_pos = ?
                WHERE id = ? AND patient_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param('ssssssii', 
                $alamat, $provinsi, $kota, $kecamatan, $kelurahan, $kode_pos,
                $address['id'], $patient_id
            );
        } else {
            // Insert alamat baru
            $insert_sql = "INSERT INTO patient_addresses 
                (patient_id, alamat, provinsi, kota, kecamatan, kelurahan, kode_pos, is_primary) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param('issssss', 
                $patient_id, $alamat, $provinsi, $kota, $kecamatan, $kelurahan, $kode_pos
            );
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Alamat berhasil diperbarui";
        } else {
            $_SESSION['error'] = "Gagal memperbarui alamat: " . $conn->error;
        }
        header("Location: edit_patient.php?id=" . $patient_id);
        exit;
    }
}

$success_message = $_SESSION['success'] ?? '';
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pasien - <?= htmlspecialchars($patient['nama_lengkap']) ?> - Vaksinin</title>
    <link rel="stylesheet" href="system/admin/css/admin.css"> 
    <link rel="stylesheet" href="system/admin/css/product-form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            background: white;
            padding: 20px 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 6px solid #FFA500;
        }
        
        .page-header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 6px 0;
        }
        
        .page-header .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-back {
            background: white;
            border: 1.5px solid #e2e8f0;
            padding: 10px 20px;
            border-radius: 8px;
            color: #475569;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-back:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }
        
        .edit-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        
        .edit-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 24px;
            overflow: hidden;
        }
        
        .edit-card-full {
            grid-column: 1 / -1;
        }
        
        .card-header {
            padding: 20px 24px;
            border-bottom: 2px solid #f1f5f9;
            background: #f8fafc;
        }
        
        .card-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .card-header i {
            color: #FFA500;
        }
        
        .card-body {
            padding: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }
        
        .form-group label .required {
            color: #e74c3c;
            margin-left: 4px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            font-family: inherit;
        }
        
        .form-control:focus {
            border-color: #FFA500;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255,165,0,0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            padding: 8px 0;
        }
        
        .radio-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #FFA500;
        }
        
        .contact-list {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .contact-item:last-child {
            border-bottom: none;
        }
        
        .contact-item .contact-value {
            font-weight: 500;
            color: #2c3e50;
        }
        
        .contact-item .badge-primary {
            background: #e8f4fc;
            color: #2980b9;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }
        
        .contact-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-icon:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }
        
        .btn-icon.delete:hover {
            background: #fef2f2;
            border-color: #fecaca;
            color: #ef4444;
        }
        
        .btn-icon.primary:hover {
            background: #fff3e0;
            border-color: #ffe0b2;
            color: #ef6c00;
        }
        
        .add-contact-form {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border: 1px dashed #cbd5e1;
        }
        
        .add-contact-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 16px;
        }
        
        .add-contact-title i {
            color: #27ae60;
        }
        
        .btn-save {
            background: #FFA500;
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-save:hover {
            background: #FF8C00;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255,165,0,0.3);
        }
        
        .btn-cancel {
            background: white;
            border: 1.5px solid #e2e8f0;
            padding: 12px 32px;
            border-radius: 8px;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-cancel:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 992px) {
            .edit-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .edit-container {
                padding: 16px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn-save,
            .btn-cancel {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
      <!-- Sidebar -->
  <?php include "content/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="edit-container">
            <!-- Header -->
            <div class="page-header">
                <a href="patient_detail.php?id=<?= $patient_id ?>" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali ke Detail
                </a>
                <h1>Edit Data Pasien</h1>
                <div>
                    <p class="subtitle">
                        <i class="fas fa-user"></i>
                        <?= htmlspecialchars($patient['nama_lengkap']) ?>
                        <?php if (!empty($patient['no_rekam_medis'])): ?>
                            <span class="product-code"><?= htmlspecialchars($patient['no_rekam_medis']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $success_message ?>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
                </div>
            <?php endif; ?>

            <!-- Edit Grid -->
            <div class="edit-grid">
                <!-- Data Pribadi -->
                <div class="edit-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user"></i> Data Pribadi</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_personal">
                            
                            <div class="form-group">
                                <label>Nama Lengkap <span class="required">*</span></label>
                                <input type="text" name="nama_lengkap" class="form-control" 
                                       value="<?= htmlspecialchars($patient['nama_lengkap'] ?? '') ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Nama Panggilan</label>
                                    <input type="text" name="nama_panggilan" class="form-control" 
                                           value="<?= htmlspecialchars($patient['nama_panggilan'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" class="form-control" 
                                           value="<?= htmlspecialchars($patient['tanggal_lahir'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="form-control">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L" <?= ($patient['jenis_kelamin'] ?? '') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                        <option value="P" <?= ($patient['jenis_kelamin'] ?? '') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Kategori Usia</label>
                                    <select name="kategori_usia" class="form-control">
                                        <option value="">Pilih Kategori</option>
                                        <option value="Anak" <?= ($patient['kategori_usia'] ?? '') == 'Anak' ? 'selected' : '' ?>>Anak</option>
                                        <option value="Dewasa" <?= ($patient['kategori_usia'] ?? '') == 'Dewasa' ? 'selected' : '' ?>>Dewasa</option>
                                        <option value="Semua Usia" <?= ($patient['kategori_usia'] ?? '') == 'Semua Usia' ? 'selected' : '' ?>>Semua Usia</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Kebangsaan</label>
                                <input type="text" name="kebangsaan" class="form-control" 
                                       value="<?= htmlspecialchars($patient['kebangsaan'] ?? 'Indonesia') ?>">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>NIK</label>
                                    <input type="text" name="nik" class="form-control" 
                                           value="<?= htmlspecialchars($patient['nik'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Paspor</label>
                                    <input type="text" name="paspor" class="form-control" 
                                           value="<?= htmlspecialchars($patient['paspor'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>No. Rekam Medis</label>
                                <input type="text" name="no_rekam_medis" class="form-control" 
                                       value="<?= htmlspecialchars($patient['no_rekam_medis'] ?? '') ?>">
                            </div>
                            
                            <div class="form-actions" style="margin-top: 16px; padding-top: 16px;">
                                <button type="submit" class="btn-save">Simpan Data Pribadi</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Kontak & Pekerjaan -->
                <div class="edit-card">
                    <div class="card-header">
                        <h3><i class="fas fa-address-book"></i> Kontak & Pekerjaan</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_contact">
                            
                            <div class="form-group">
                                <label>Pekerjaan</label>
                                <input type="text" name="pekerjaan" class="form-control" 
                                       value="<?= htmlspecialchars($patient['pekerjaan'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Nama Wali</label>
                                <input type="text" name="nama_wali" class="form-control" 
                                       value="<?= htmlspecialchars($patient['nama_wali'] ?? '') ?>">
                            </div>
                            
                            <div class="form-actions" style="margin-top: 16px; padding-top: 16px;">
                                <button type="submit" class="btn-save">Simpan Kontak</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Email -->
                <div class="edit-card">
                    <div class="card-header">
                        <h3><i class="fas fa-envelope"></i> Email</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($emails)): ?>
                            <div class="contact-list">
                                <?php foreach ($emails as $email): ?>
                                    <div class="contact-item">
                                        <div>
                                            <span class="contact-value"><?= htmlspecialchars($email['email']) ?></span>
                                            <?php if ($email['is_primary'] == 1): ?>
                                                <span class="badge-primary">Utama</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="contact-actions">
                                            <?php if ($email['is_primary'] != 1): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="set_primary_email">
                                                    <input type="hidden" name="email_id" value="<?= $email['id'] ?>">
                                                    <button type="submit" class="btn-icon primary" title="Jadikan Utama">
                                                        <i class="fas fa-star"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Hapus email ini?')">
                                                <input type="hidden" name="action" value="delete_email">
                                                <input type="hidden" name="email_id" value="<?= $email['id'] ?>">
                                                <button type="submit" class="btn-icon delete" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted" style="margin-bottom: 16px;">Belum ada email</p>
                        <?php endif; ?>

                        <div class="add-contact-form">
                            <div class="add-contact-title">
                                <i class="fas fa-plus-circle"></i> Tambah Email Baru
                            </div>
                            <form method="POST">
                                <input type="hidden" name="action" value="add_email">
                                
                                <div class="form-group">
                                    <input type="email" name="email" class="form-control" 
                                           placeholder="email@contoh.com" required>
                                </div>
                                
                                <div class="checkbox-group">
                                    <input type="checkbox" name="is_primary" id="email_primary" value="1">
                                    <label for="email_primary">Jadikan email utama</label>
                                </div>
                                
                                <button type="submit" class="btn-save" style="width: 100%;">
                                    <i class="fas fa-plus"></i> Tambah Email
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Telepon -->
                <div class="edit-card">
                    <div class="card-header">
                        <h3><i class="fas fa-phone"></i> Nomor Telepon</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($phones)): ?>
                            <div class="contact-list">
                                <?php foreach ($phones as $phone): ?>
                                    <div class="contact-item">
                                        <div>
                                            <span class="contact-value"><?= htmlspecialchars($phone['phone']) ?></span>
                                            <?php if ($phone['is_primary'] == 1): ?>
                                                <span class="badge-primary">Utama</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="contact-actions">
                                            <?php if ($phone['is_primary'] != 1): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="set_primary_phone">
                                                    <input type="hidden" name="phone_id" value="<?= $phone['id'] ?>">
                                                    <button type="submit" class="btn-icon primary" title="Jadikan Utama">
                                                        <i class="fas fa-star"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Hapus nomor telepon ini?')">
                                                <input type="hidden" name="action" value="delete_phone">
                                                <input type="hidden" name="phone_id" value="<?= $phone['id'] ?>">
                                                <button type="submit" class="btn-icon delete" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted" style="margin-bottom: 16px;">Belum ada nomor telepon</p>
                        <?php endif; ?>

                        <div class="add-contact-form">
                            <div class="add-contact-title">
                                <i class="fas fa-plus-circle"></i> Tambah Nomor Telepon Baru
                            </div>
                            <form method="POST">
                                <input type="hidden" name="action" value="add_phone">
                                
                                <div class="form-group">
                                    <input type="text" name="phone" class="form-control" 
                                           placeholder="Contoh: 08123456789" required>
                                </div>
                                
                                <div class="checkbox-group">
                                    <input type="checkbox" name="is_primary" id="phone_primary" value="1">
                                    <label for="phone_primary">Jadikan nomor utama</label>
                                </div>
                                
                                <button type="submit" class="btn-save" style="width: 100%;">
                                    <i class="fas fa-plus"></i> Tambah Nomor
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Alamat -->
                <div class="edit-card edit-card-full">
                    <div class="card-header">
                        <h3><i class="fas fa-map-marker-alt"></i> Alamat</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_address">
                            
                            <div class="form-group">
                                <label>Alamat</label>
                                <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($address['alamat'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Provinsi</label>
                                    <input type="text" name="provinsi" class="form-control" 
                                           value="<?= htmlspecialchars($address['provinsi'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Kota/Kabupaten</label>
                                    <input type="text" name="kota" class="form-control" 
                                           value="<?= htmlspecialchars($address['kota'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Kecamatan</label>
                                    <input type="text" name="kecamatan" class="form-control" 
                                           value="<?= htmlspecialchars($address['kecamatan'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Kelurahan/Desa</label>
                                    <input type="text" name="kelurahan" class="form-control" 
                                           value="<?= htmlspecialchars($address['kelurahan'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Kode Pos</label>
                                <input type="text" name="kode_pos" class="form-control" 
                                       value="<?= htmlspecialchars($address['kode_pos'] ?? '') ?>">
                            </div>
                            
                            <div class="form-actions" style="margin-top: 16px; padding-top: 16px;">
                                <button type="submit" class="btn-save">Simpan Alamat</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Riwayat Kesehatan -->
                <div class="edit-card edit-card-full">
                    <div class="card-header">
                        <h3><i class="fas fa-heartbeat"></i> Riwayat Kesehatan</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_health">
                            
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" name="tidak_ada_alergi" id="tidak_ada_alergi" value="1"
                                           <?= empty($patient['riwayat_alergi']) ? 'checked' : '' ?>>
                                    <label for="tidak_ada_alergi">Tidak ada riwayat alergi</label>
                                </div>
                                <textarea name="riwayat_alergi" class="form-control" rows="3" 
                                          placeholder="Riwayat alergi (jika ada)"><?= htmlspecialchars($patient['riwayat_alergi'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" name="tidak_ada_penyakit" id="tidak_ada_penyakit" value="1"
                                           <?= empty($patient['riwayat_penyakit']) ? 'checked' : '' ?>>
                                    <label for="tidak_ada_penyakit">Tidak ada riwayat penyakit</label>
                                </div>
                                <textarea name="riwayat_penyakit" class="form-control" rows="3" 
                                          placeholder="Riwayat penyakit (jika ada)"><?= htmlspecialchars($patient['riwayat_penyakit'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" name="tidak_ada_obat" id="tidak_ada_obat" value="1"
                                           <?= empty($patient['riwayat_obat']) ? 'checked' : '' ?>>
                                    <label for="tidak_ada_obat">Tidak ada riwayat obat</label>
                                </div>
                                <textarea name="riwayat_obat" class="form-control" rows="3" 
                                          placeholder="Riwayat obat (jika ada)"><?= htmlspecialchars($patient['riwayat_obat'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-actions" style="margin-top: 16px; padding-top: 16px;">
                                <button type="submit" class="btn-save">Simpan Riwayat Kesehatan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="system/admin/js/sidebar-toggle.js"></script>
    <script>
        // Toggle textarea berdasarkan checkbox
        document.getElementById('tidak_ada_alergi')?.addEventListener('change', function() {
            document.querySelector('textarea[name="riwayat_alergi"]').disabled = this.checked;
            if (this.checked) {
                document.querySelector('textarea[name="riwayat_alergi"]').value = '';
            }
        });
        
        document.getElementById('tidak_ada_penyakit')?.addEventListener('change', function() {
            document.querySelector('textarea[name="riwayat_penyakit"]').disabled = this.checked;
            if (this.checked) {
                document.querySelector('textarea[name="riwayat_penyakit"]').value = '';
            }
        });
        
        document.getElementById('tidak_ada_obat')?.addEventListener('change', function() {
            document.querySelector('textarea[name="riwayat_obat"]').disabled = this.checked;
            if (this.checked) {
                document.querySelector('textarea[name="riwayat_obat"]').value = '';
            }
        });
        
        // Inisialisasi disabled state
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('tidak_ada_alergi')?.checked) {
                document.querySelector('textarea[name="riwayat_alergi"]').disabled = true;
            }
            if (document.getElementById('tidak_ada_penyakit')?.checked) {
                document.querySelector('textarea[name="riwayat_penyakit"]').disabled = true;
            }
            if (document.getElementById('tidak_ada_obat')?.checked) {
                document.querySelector('textarea[name="riwayat_obat"]').disabled = true;
            }
        });
    </script>
</body>
</html>