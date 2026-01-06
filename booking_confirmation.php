<?php
session_start();
include "config.php";

// Cek apakah ada data dari form
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_pasien']) || !isset($_POST['tanggal'])) {
    header('Location: order.php');
    exit;
}

$patient_id = intval($_POST['id_pasien']);
$tanggal = $_POST['tanggal'];
$waktu   = $_POST['waktu_booking'];

$datetime = new DateTime("$tanggal $waktu");

// Ambil data pasien dari database
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
$email = $email_data ? $email_data['email'] : '-';
mysqli_stmt_close($stmt);

// Ambil phone primary
$stmt = mysqli_prepare($conn, "SELECT phone FROM patient_phones WHERE patient_id = ? AND is_primary = 1 LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$phone_data = mysqli_fetch_assoc($result);
$phone = $phone_data ? $phone_data['phone'] : '-';
mysqli_stmt_close($stmt);

// Ambil phone primary
$stmt = mysqli_prepare($conn, "SELECT alamat FROM patient_addresses WHERE patient_id = ? AND is_primary = 1 LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$address_data = mysqli_fetch_assoc($result);
$address = $address_data ? $address_data['alamat'] : '-';
mysqli_stmt_close($stmt);

// Format tanggal Indonesia
$tanggal_obj = new DateTime($tanggal);
$hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tanggal_format = $hari[$tanggal_obj->format('w')] . ', ' . $tanggal_obj->format('d') . ' ' . $bulan[$tanggal_obj->format('n')] . ' ' . $tanggal_obj->format('Y');

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Data Reservasi</title>
    <link rel="stylesheet" href="confirmation.css">
    <link rel="stylesheet" href="layout.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">✓</div>
            <h1>Konfirmasi Ulang Data</h1>
            <p class="subtitle">Pastikan data Anda sudah benar sebelum melanjutkan</p>
        </div>

        <div class="card">
            <h2 class="card-title">📋 Data Pasien</h2>
            
            <div class="data-row">
                <span class="label">No. Rekam Medis</span>
                <span class="value"><?php echo htmlspecialchars($patient['no_rekam_medis']); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Nama Lengkap</span>
                <span class="value"><?php echo htmlspecialchars($patient['nama_lengkap']); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Nama Panggilan</span>
                <span class="value"><?php echo htmlspecialchars($patient['nama_panggilan']); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Tanggal Lahir</span>
                <span class="value"><?php echo date('d/m/Y', strtotime($patient['tanggal_lahir'])); ?> (<?php echo $patient['usia']; ?> tahun)</span>
            </div>

            <div class="data-row">
                <span class="label">Jenis Kelamin</span>
                <span class="value"><?php echo $patient['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></span>
            </div>

            <div class="data-row">
                <span class="label">NIK/Paspor</span>
                <span class="value"><?php echo htmlspecialchars($patient['nik_paspor']); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Kebangsaan</span>
                <span class="value"><?php echo htmlspecialchars($patient['kebangsaan']); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Pekerjaan</span>
                <span class="value"><?php echo htmlspecialchars($patient['pekerjaan']); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Nama Wali</span>
                <span class="value"><?php echo htmlspecialchars($patient['nama_wali']); ?></span>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">📞 Informasi Kontak</h2>
            
            <div class="data-row">
                <span class="label">Email</span>
                <span class="value"><?php echo htmlspecialchars($email); ?></span>
            </div>

            <div class="data-row">
                <span class="label">No. HP</span>
                <span class="value"><?php echo htmlspecialchars($phone); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Alamat</span>
                <span class="value"><?php echo htmlspecialchars($address); ?></span>
            </div>
        </div>

        <div class="card highlight">
            <h2 class="card-title">📅 Jadwal Reservasi</h2>
            
            <div class="data-row">
                <span class="label">Tanggal</span>
                <span class="value bold"><?php echo $tanggal_format; ?></span>
            </div>
        </div>

        <?php if ($patient['riwayat_alergi'] || $patient['riwayat_penyakit']): ?>
        <div class="card warning">
            <h2 class="card-title">⚠️ Riwayat Kesehatan</h2>
            
            <?php if ($patient['riwayat_alergi']): ?>
            <div class="data-row">
                <span class="label">Riwayat Alergi</span>
                <span class="value"><?php echo htmlspecialchars($patient['riwayat_alergi']); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($patient['riwayat_penyakit']): ?>
            <div class="data-row">
                <span class="label">Riwayat Penyakit</span>
                <span class="value"><?php echo htmlspecialchars($patient['riwayat_penyakit']); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="actions">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='order.php?id_pasien=<?php echo $patient_id; ?>'">
                ← Edit
            </button>

            <form method="POST" action="save_booking.php" style="flex: 1;">
                <input type="hidden" name="id_pasien" value="<?php echo $patient_id; ?>">
                <input type="hidden" name="tanggal" value="<?php echo $tanggal; ?>">
                
                <button type="submit" class="btn btn-primary">
                    Konfirmasi & Lanjutkan →
                </button>
            </form>
        </div>

        <div class="note">
            <p><strong>Catatan:</strong> Setelah konfirmasi, Anda akan menerima nomor antrian dan detail reservasi via email/WhatsApp.</p>
        </div>
    </div>
</body>
</html>