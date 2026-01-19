<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: order.php');
    exit;
}

// Validasi data
$errors = [];

$service_type = $_POST['service_type'] ?? '';
$pelayanan = $_POST['pelayanan'] ?? '';
$nama_lengkap = $_POST['nama_lengkap'] ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$tanggal_booking = $_POST['tanggal_booking'] ?? '';
$waktu_booking = $_POST['waktu_booking'] ?? '';
$action = $_POST['action'] ?? ''; // 'add_more' atau 'finish'

if (empty($service_type)) $errors[] = 'Tipe layanan harus dipilih';
if (empty($pelayanan)) $errors[] = 'Pelayanan harus dipilih';
if (empty($nama_lengkap)) $errors[] = 'Nama lengkap harus diisi';
if (empty($tanggal_lahir)) $errors[] = 'Tanggal lahir harus diisi';
if (empty($jenis_kelamin)) $errors[] = 'Jenis kelamin harus dipilih';
if (empty($tanggal_booking)) $errors[] = 'Tanggal booking harus dipilih';
if (empty($waktu_booking)) $errors[] = 'Waktu booking harus dipilih';

// Validasi identitas sesuai layanan
if ($pelayanan === 'Umroh/Haji/Luar Negeri') {
    if (empty($_POST['paspor'])) {
        $errors[] = 'Nomor Paspor harus diisi untuk layanan Umroh/Haji/Luar Negeri';
    }
} else if ($pelayanan === 'Vaksinasi Umum/Infus Vitamin') {
    if (empty($_POST['nik'])) {
        $errors[] = 'NIK harus diisi untuk layanan Vaksinasi Umum/Infus Vitamin';
    } else if (strlen($_POST['nik']) !== 16) {
        $errors[] = 'NIK harus 16 digit';
    }
}

// Validasi kontak
$emails = $_POST['emails'] ?? [];
$phones = $_POST['phones'] ?? [];

if (empty($emails[0])) $errors[] = 'Email harus diisi';
if (empty($phones[0])) $errors[] = 'Nomor HP harus diisi';

// Filter array (hapus yang kosong)
$emails = array_filter($emails);
$phones = array_filter($phones);

// Validasi alamat
if (empty($_POST['alamat'])) $errors[] = 'Alamat harus diisi';
if (empty($_POST['provinsi'])) $errors[] = 'Provinsi harus dipilih';
if (empty($_POST['kota'])) $errors[] = 'Kota harus dipilih';

// Jika ada error, kembali ke order.php dengan error message
if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header('Location: order.php');
    exit;
}

// Hitung usia
$birthDate = new DateTime($tanggal_lahir);
$today = new DateTime();
$usia = $today->diff($birthDate)->y;
$kategori_usia = ($usia < 18) ? 'Anak' : 'Dewasa';

// Siapkan data peserta
$participant_data = [
    'service_type' => $service_type,
    'pelayanan' => $pelayanan,
    'nama_lengkap' => $nama_lengkap,
    'nama_panggilan' => $_POST['nama_panggilan'] ?? '',
    'tanggal_lahir' => $tanggal_lahir,
    'usia' => $usia,
    'kategori_usia' => $kategori_usia,
    'jenis_kelamin' => $jenis_kelamin,
    'nik' => $_POST['nik'] ?? '',
    'paspor' => $_POST['paspor'] ?? '',
    'kebangsaan' => $_POST['kebangsaan'] ?? 'Indonesia',
    'pekerjaan' => $_POST['pekerjaan'] ?? '',
    'nama_wali' => $_POST['nama_wali'] ?? '',
    'emails' => $emails,
    'phones' => $phones,
    'alamat' => $_POST['alamat'],
    'provinsi' => $_POST['provinsi'],
    'kota' => $_POST['kota'],
    'riwayat_alergi' => $_POST['riwayat_alergi'] ?? '',
    'riwayat_penyakit' => $_POST['riwayat_penyakit'] ?? '',
    'riwayat_obat' => $_POST['riwayat_obat'] ?? '',
    'tanggal_booking' => $tanggal_booking,
    'waktu_booking' => $waktu_booking
];

// ========== SIMPAN SELECTED PRODUCTS KE PARTICIPANT DATA ==========
if (isset($_POST['selected_products']) && !empty($_POST['selected_products'])) {
    $selected_products = json_decode($_POST['selected_products'], true);
    $participant_data['selected_products'] = $selected_products;
} else {
    $participant_data['selected_products'] = [];
}

// Cek action
if ($action === 'add_more') {
    // Simpan ke session sebagai peserta pertama
    if (!isset($_SESSION['participants'])) {
        $_SESSION['participants'] = [];
    }
    $_SESSION['participants'][] = $participant_data;
    
    // Redirect ke add_participant.php
    $_SESSION['success_message'] = 'Peserta pertama berhasil ditambahkan! Silakan tambah peserta berikutnya.';
    header('Location: add_participant.php');
    exit;
    
} else if ($action === 'finish') {
    // Simpan peserta pertama ke session juga
    if (!isset($_SESSION['participants'])) {
        $_SESSION['participants'] = [];
    }
    $_SESSION['participants'][] = $participant_data;
    
    // Redirect langsung ke konfirmasi
    header('Location: booking_confirmation.php');
    exit;
}

// Fallback jika action tidak valid
$_SESSION['error_message'] = 'Action tidak valid';
header('Location: order.php');
exit;
?>