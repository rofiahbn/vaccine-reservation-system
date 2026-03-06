<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

include "config.php";

/*
|--------------------------------------------------------------------------
| Supaya error MySQL bisa ditangkap try-catch
|--------------------------------------------------------------------------
*/
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Data tidak valid');
    }

    if (empty($input['nama_lengkap']) || empty($input['tanggal_lahir']) || empty($input['jenis_kelamin'])) {
        throw new Exception('Data wajib harus diisi');
    }

    /*
    |--------------------------------------------------------------------------
    | Hitung usia
    |--------------------------------------------------------------------------
    */
    $tgl_lahir = new DateTime($input['tanggal_lahir']);
    $today = new DateTime();
    $usia = $today->diff($tgl_lahir)->y;
    $kategori_usia = $usia < 18 ? 'Anak' : 'Dewasa';

    /*
    |--------------------------------------------------------------------------
    | Normalisasi Jenis Kelamin
    |--------------------------------------------------------------------------
    */
    if ($input['jenis_kelamin'] === 'Laki-laki' || $input['jenis_kelamin'] === 'L') {
        $jk = 'L';
    } elseif ($input['jenis_kelamin'] === 'Perempuan' || $input['jenis_kelamin'] === 'P') {
        $jk = 'P';
    } else {
        throw new Exception('Jenis kelamin tidak valid');
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Nomor Rekam Medis
    |--------------------------------------------------------------------------
    */
    $tahun = date('Y');
    $bulan = date('m');

    $sql_rm = "SELECT COUNT(*) as total 
               FROM patients 
               WHERE YEAR(created_at)=? AND MONTH(created_at)=?";
               
    $stmt_rm = mysqli_prepare($conn, $sql_rm);
    mysqli_stmt_bind_param($stmt_rm, 'ss', $tahun, $bulan);
    mysqli_stmt_execute($stmt_rm);
    $res = mysqli_stmt_get_result($stmt_rm);
    $row = mysqli_fetch_assoc($res);

    $urutan = str_pad($row['total'] + 1, 4, '0', STR_PAD_LEFT);
    $no_rekam_medis = $tahun . $bulan . $urutan;

    /*
    |--------------------------------------------------------------------------
    | Assign Variable
    |--------------------------------------------------------------------------
    */
    $nama_lengkap    = trim($input['nama_lengkap']);
    $nama_panggilan  = $input['nama_panggilan'] ?? '';
    $tanggal_lahir   = $input['tanggal_lahir'];
    $nik             = !empty($input['nik']) ? $input['nik'] : NULL;
    $paspor          = !empty($input['paspor']) ? $input['paspor'] : NULL;
    $kebangsaan      = $input['kebangsaan'] ?? 'Indonesia';
    $pekerjaan       = $input['pekerjaan'] ?? '';
    $nama_wali       = $input['nama_wali'] ?? '';
    $riwayat_alergi  = $input['riwayat_alergi'] ?? '';
    $riwayat_penyakit= $input['riwayat_penyakit'] ?? '';
    $riwayat_obat    = $input['riwayat_obat'] ?? '';
    $pelayanan       = 'booking';

    /*
    |--------------------------------------------------------------------------
    | Cek Duplicate NIK (jika diisi)
    |--------------------------------------------------------------------------
    */
    if (!empty($nik)) {
        $cek = mysqli_prepare($conn, "SELECT id FROM patients WHERE nik = ?");
        mysqli_stmt_bind_param($cek, 's', $nik);
        mysqli_stmt_execute($cek);
        $result = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($result) > 0) {
            throw new Exception('NIK sudah terdaftar. Silakan gunakan NIK lain.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Insert Patient
    |--------------------------------------------------------------------------
    */
    $sql = "INSERT INTO patients (
        no_rekam_medis,
        nama_lengkap,
        nama_panggilan,
        tanggal_lahir,
        usia,
        kategori_usia,
        jenis_kelamin,
        nik,
        paspor,
        kebangsaan,
        pekerjaan,
        nama_wali,
        riwayat_alergi,
        riwayat_penyakit,
        riwayat_obat,
        pelayanan,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        'ssssisssssssssss',
        $no_rekam_medis,
        $nama_lengkap,
        $nama_panggilan,
        $tanggal_lahir,
        $usia,
        $kategori_usia,
        $jk,
        $nik,
        $paspor,
        $kebangsaan,
        $pekerjaan,
        $nama_wali,
        $riwayat_alergi,
        $riwayat_penyakit,
        $riwayat_obat,
        $pelayanan
    );

    mysqli_stmt_execute($stmt);

    $patient_id = mysqli_insert_id($conn);

    echo json_encode([
        'success' => true,
        'patient_id' => $patient_id,
        'nama_lengkap' => $nama_lengkap,
        'message' => 'Pasien berhasil disimpan'
    ]);

} catch (mysqli_sql_exception $e) {

    if ($e->getCode() == 1062) {
        echo json_encode([
            'success' => false,
            'message' => 'Data sudah terdaftar (duplicate entry)'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan database'
        ]);
    }

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

}