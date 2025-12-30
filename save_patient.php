<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $p = $_POST['participants'][0];

    $nama_lengkap   = mysqli_real_escape_string($conn, $p['nama_lengkap']);
    $nama_panggilan = mysqli_real_escape_string($conn, $p['nama_panggilan']);
    $tanggal_lahir  = mysqli_real_escape_string($conn, $p['tanggal_lahir']);
    $jenis_kelamin  = mysqli_real_escape_string($conn, $p['jenis_kelamin']);
    $nik_paspor     = mysqli_real_escape_string($conn, $p['nik_paspor']);
    $kebangsaan     = mysqli_real_escape_string($conn, $p['kebangsaan']);
    $pekerjaan      = mysqli_real_escape_string($conn, $p['pekerjaan']);
    $nama_wali      = mysqli_real_escape_string($conn, $p['nama_wali']);

    $riwayat_alergi   = mysqli_real_escape_string($conn, $p['riwayat_alergi']);
    $riwayat_penyakit = mysqli_real_escape_string($conn, $p['riwayat_penyakit']);
    $riwayat_obat     = mysqli_real_escape_string($conn, $p['riwayat_obat']);

    $pelayanan = mysqli_real_escape_string($conn, $p['pelayanan']);




    // hitung usia
    $birthDate = new DateTime($tanggal_lahir);
    $today = new DateTime();
    $usia = $today->diff($birthDate)->y;
    $kategori_usia = ($usia < 18) ? 'Anak' : 'Dewasa';


    // generate no rekam medis
    $no_rekam_medis = 'RM' . time();

    // INSERT ke patients
    $query = "INSERT INTO patients 
    (no_rekam_medis, nama_lengkap, nama_panggilan, tanggal_lahir, usia, kategori_usia, jenis_kelamin, nik_paspor, kebangsaan, pekerjaan, nama_wali, riwayat_alergi, riwayat_penyakit, riwayat_obat)
    VALUES 
    ('$no_rekam_medis', '$nama_lengkap', '$nama_panggilan', '$tanggal_lahir', '$usia', '$kategori_usia', '$jenis_kelamin', '$nik_paspor', '$kebangsaan', '$pekerjaan', '$nama_wali', '$riwayat_alergi', '$riwayat_penyakit', '$riwayat_obat')";

    if (mysqli_query($conn, $query)) {

        // ambil id pasien terakhir
        $patient_id = mysqli_insert_id($conn);

        // simpan email
        foreach ($p['emails'] ?? [] as $i => $email) {
            if ($email != '') {
                $is_primary = ($i == 0) ? 1 : 0;
                mysqli_query($conn, "INSERT INTO patient_emails (patient_id, email, is_primary)
                VALUES ($patient_id, '$email', $is_primary)");
            }
        }

        // simpan phone
        foreach ($p['phones'] ?? [] as $i => $phone) {
            if ($phone != '') {
                $is_primary = ($i == 0) ? 1 : 0;
                mysqli_query($conn, "INSERT INTO patient_phones (patient_id, phone, is_primary)
                VALUES ($patient_id, '$phone', $is_primary)");
            }
        }

        // simpan address
        foreach ($p['addresses'] ?? [] as $i => $address) {
            if ($address != '') {
                $is_primary = ($i == 0) ? 1 : 0;
                mysqli_query($conn, "INSERT INTO patient_addresses (patient_id, alamat, is_primary)
                VALUES ($patient_id, '$address', $is_primary)");
            }
        }

        if (!empty($p['vaksin'])) {
            foreach ($p['vaksin'] as $vaksin) {
                $vaksin = mysqli_real_escape_string($conn, $vaksin);
                mysqli_query($conn, "
                    INSERT INTO patient_services (patient_id, service_type, service_name)
                    VALUES ($patient_id, 'Vaksin', '$vaksin')
                ");
            }
        }

        if (!empty($p['vitamin'])) {
            foreach ($p['vitamin'] as $vitamin) {
                $vitamin = mysqli_real_escape_string($conn, $vitamin);
                mysqli_query($conn, "
                    INSERT INTO patient_services (patient_id, service_type, service_name)
                    VALUES ($patient_id, 'Vitamin', '$vitamin')
                ");
            }
        }

        // REDIRECT KE CALENDAR
        header("Location: calender.php?id_pasien=$patient_id");
        exit;

    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
