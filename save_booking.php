<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ========== DEBUG ==========
error_log("========== SAVE BOOKING START ==========");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("ERROR: Bukan POST request");
    $_SESSION['error_message'] = 'Method tidak valid';
    header('Location: order.php');
    exit;
}

// Inisialisasi session participants jika belum ada
if (!isset($_SESSION['participants'])) {
    $_SESSION['participants'] = [];
    error_log("Inisialisasi participants array");
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
$action = $_POST['submit_action'] ?? '';

error_log("Action: $action");
error_log("Service Type: $service_type");
error_log("Nama: $nama_lengkap");

// Validasi dasar
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
$raw_emails = $_POST['emails'] ?? [];
$raw_phones = $_POST['phones'] ?? [];

if (!is_array($raw_emails)) $raw_emails = [$raw_emails];
if (!is_array($raw_phones)) $raw_phones = [$raw_phones];

if (empty($raw_emails[0])) $errors[] = 'Email harus diisi';
if (empty($raw_phones[0])) $errors[] = 'Nomor HP harus diisi';

// Filter array (hapus yang kosong) dan unique
$emails = array_values(array_unique(array_filter($raw_emails)));
$phones = array_values(array_unique(array_filter($raw_phones)));

// Validasi alamat
if (empty($_POST['alamat'])) $errors[] = 'Alamat harus diisi';
if (empty($_POST['provinsi'])) $errors[] = 'Provinsi harus dipilih';
if (empty($_POST['kota'])) $errors[] = 'Kota harus dipilih';

// Jika ada error
if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    error_log("ERRORS: " . print_r($errors, true));
    header('Location: order.php');
    exit;
}

// Hitung usia
try {
    $birthDate = new DateTime($tanggal_lahir);
    $today = new DateTime();
    $usia = $today->diff($birthDate)->y;
    $kategori_usia = ($usia < 18) ? 'Anak' : 'Dewasa';
} catch (Exception $e) {
    error_log("ERROR hitung usia: " . $e->getMessage());
    $usia = 0;
    $kategori_usia = 'Dewasa';
}

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

error_log("Participant data: " . print_r($participant_data, true));

// ========== CEK KONSISTENSI JADWAL ==========
if (count($_SESSION['participants']) > 0) {
    $first = $_SESSION['participants'][0];
    error_log("Cek konsistensi dengan peserta pertama");

    if ($participant_data['tanggal_booking'] !== $first['tanggal_booking'] ||
        $participant_data['waktu_booking'] !== $first['waktu_booking']) {
        $_SESSION['error_message'] = 'Semua peserta harus memiliki jadwal yang sama.';
        error_log("ERROR: Jadwal tidak sama");
        header('Location: add_participant.php');
        exit;
    }

    if ($participant_data['service_type'] !== $first['service_type']) {
        $_SESSION['error_message'] = 'Semua peserta harus menggunakan tipe layanan yang sama.';
        error_log("ERROR: Service type tidak sama");
        header('Location: add_participant.php');
        exit;
    }
}

// ========== SIMPAN SELECTED PRODUCTS ==========
if (isset($_POST['selected_products']) && !empty($_POST['selected_products'])) {
    $selected_products = json_decode($_POST['selected_products'], true);
    error_log("Selected products: " . print_r($selected_products, true));

    if (is_array($selected_products)) {
        $participant_data['selected_products'] = $selected_products;
        $_SESSION['selected_products_raw'] = $selected_products;
    } else {
        $participant_data['selected_products'] = [];
    }
} else {
    $participant_data['selected_products'] = [];
}

// ========== SIMPAN DATA PESERTA ==========
$_SESSION['participants'][] = $participant_data;
error_log("Total participants: " . count($_SESSION['participants']));

// ========== REDIRECT ==========
if ($action === 'add_more') {
    $_SESSION['success_message'] = 'Peserta berhasil ditambahkan!';
    error_log("REDIRECT to add_participant.php");
    header('Location: add_participant.php');
    exit;
} else if ($action === 'finish') {
    $_SESSION['success_message'] = 'Data berhasil disimpan.';
    error_log("REDIRECT to booking_confirmation.php");
    header('Location: booking_confirmation.php');
    exit;
} else {
    $_SESSION['error_message'] = 'Action tidak valid';
    error_log("ERROR: Action tidak valid: $action");
    header('Location: order.php');
    exit;
}
?>