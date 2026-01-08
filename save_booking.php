<?php
session_start();
include "config.php";

// Cek apakah ada data dari form
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['id_pasien'], $_POST['tanggal'], $_POST['waktu_booking'])
) {
    header('Location: order.php');
    exit;
}

$patient_id = intval($_POST['id_pasien']);
$tanggal = $_POST['tanggal'];
$waktu = $_POST['waktu_booking'];

// Ambil data pasien
$stmt = mysqli_prepare($conn, "SELECT * FROM patients WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$patient = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$patient) {
    die("Data pasien tidak ditemukan!");
}

// Ambil email primary
$stmt = mysqli_prepare($conn, "SELECT email FROM patient_emails WHERE patient_id = ? AND is_primary = 1 LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$email_data = mysqli_fetch_assoc($result);
$email = $email_data ? $email_data['email'] : '';
mysqli_stmt_close($stmt);

// Ambil phone primary
$stmt = mysqli_prepare($conn, "SELECT phone FROM patient_phones WHERE patient_id = ? AND is_primary = 1 LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$phone_data = mysqli_fetch_assoc($result);
$phone = $phone_data ? $phone_data['phone'] : '';
mysqli_stmt_close($stmt);

// Generate nomor antrian (format: YYMMDD-XXX)
$date_code = date('ymd', strtotime($tanggal));

// Cek nomor antrian terakhir untuk tanggal tersebut
$stmt = mysqli_prepare($conn, "SELECT nomor_antrian FROM bookings WHERE DATE(tanggal_booking) = ? ORDER BY id DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $tanggal);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$last_booking = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($last_booking) {
    // Ambil 3 digit terakhir dan tambah 1
    $last_number = intval(substr($last_booking['nomor_antrian'], -3));
    $new_number = $last_number + 1;
} else {
    $new_number = 1;
}

$nomor_antrian = $date_code . '-' . str_pad($new_number, 3, '0', STR_PAD_LEFT);

// Simpan booking ke database
$status = 'pending'; // status: pending, confirmed, completed, cancelled
$created_at = date('Y-m-d H:i:s');

$cek = $conn->prepare("
    SELECT id FROM bookings
    WHERE tanggal_booking = ?
      AND waktu_booking = ?
      AND status != 'cancelled'
    LIMIT 1
");
$cek->bind_param("ss", $tanggal, $waktu);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    die("Slot waktu ini sudah terisi");
}
$cek->close();

$stmt = mysqli_prepare($conn, "
    INSERT INTO bookings
    (patient_id, nomor_antrian, tanggal_booking, waktu_booking, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?)
");

mysqli_stmt_bind_param(
    $stmt,
    "isssss",
    $patient_id,
    $nomor_antrian,
    $tanggal,
    $waktu,
    $status,
    $created_at
);


if (mysqli_stmt_execute($stmt)) {
    $booking_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    // TODO: Kirim email notifikasi
    // TODO: Kirim WhatsApp notifikasi
    
    // Simpan booking_id ke session untuk halaman sukses
    $_SESSION['last_booking_id'] = $booking_id;
    $_SESSION['nomor_antrian'] = $nomor_antrian;
    
} else {
    mysqli_stmt_close($stmt);
    die("Gagal menyimpan booking: " . mysqli_error($conn));
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil</title>
    <link rel="stylesheet" href="save_booking.css">
    <link rel="stylesheet" href="layout.css">
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <svg viewBox="0 0 52 52">
                <polyline class="checkmark" points="14 27 22 35 38 19"/>
            </svg>
        </div>

        <h1>Pendaftaran Berhasil</h1>
        <p class="subtitle">
            Invoice telah dikirim ke email atau nomor telepon Anda.<br>
            Klik di sini untuk download invoice.
        </p>

        <div class="booking-info">
            <div class="booking-number">Nomor Antrian Anda</div>
            <div class="antrian-number"><?php echo htmlspecialchars($nomor_antrian); ?></div>
            
            <div class="booking-details">
                <div class="booking-detail-item">
                    <span class="label">Nama Pasien</span>
                    <span class="value"><?php echo htmlspecialchars($patient['nama_lengkap']); ?></span>
                </div>
                <div class="booking-detail-item">
                    <span class="label">Tanggal</span>
                    <span class="value"><?php echo date('d/m/Y', strtotime($tanggal)); ?></span>
                </div>
                <div class="booking-detail-item">
                    <span class="label">Waktu</span>
                    <span class="value"><?php echo date('H:i', strtotime($waktu)); ?></span>
                </div>
                <?php if ($email): ?>
                <div class="booking-detail-item">
                    <span class="label">Email</span>
                    <span class="value"><?php echo htmlspecialchars($email); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($phone): ?>
                <div class="booking-detail-item">
                    <span class="label">No. HP</span>
                    <span class="value"><?php echo htmlspecialchars($phone); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <a href="generate_invoice.php?booking_id=<?php echo $booking_id; ?>" class="btn-download">
            Download Invoice
        </a>

        <div class="info-text">
            <strong>Catatan:</strong> Simpan nomor antrian Anda. Tunjukkan nomor ini saat datang ke klinik. Anda juga akan menerima notifikasi via email/WhatsApp.
        </div>

        <a href="index.php" class="back-link">← Kembali ke Beranda</a>
    </div>
</body>
</html>