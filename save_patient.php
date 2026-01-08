<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $p = $_POST['participants'][0];

    // ================= VALIDASI KONTAK (INI PENTING) =================
    $emails    = array_filter($p['emails'] ?? []);
    $phones    = array_filter($p['phones'] ?? []);
    $addresses = array_filter($p['addresses'] ?? []);

    if (count($emails) < 1 || count($phones) < 1 || count($addresses) < 1) {
        die("Minimal harus ada 1 email, 1 nomor HP, dan 1 alamat yang diisi!");
    }
    // =================================================================

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

    // INSERT patients
    $query = "INSERT INTO patients 
        (no_rekam_medis, nama_lengkap, nama_panggilan, tanggal_lahir, usia, kategori_usia, jenis_kelamin, nik_paspor, kebangsaan, pekerjaan, nama_wali, riwayat_alergi, riwayat_penyakit, riwayat_obat, pelayanan)
        VALUES 
        ('$no_rekam_medis', '$nama_lengkap', '$nama_panggilan', '$tanggal_lahir', '$usia', '$kategori_usia', '$jenis_kelamin', '$nik_paspor', '$kebangsaan', '$pekerjaan', '$nama_wali', '$riwayat_alergi', '$riwayat_penyakit', '$riwayat_obat', '$pelayanan')";

    if (mysqli_query($conn, $query)) {

        $patient_id = mysqli_insert_id($conn);

        // ================= SIMPAN EMAIL =================
        foreach ($emails as $i => $email) {
            $email = mysqli_real_escape_string($conn, $email);
            $is_primary = ($i === array_key_first($emails)) ? 1 : 0;

            mysqli_query($conn, "
                INSERT INTO patient_emails (patient_id, email, is_primary)
                VALUES ($patient_id, '$email', $is_primary)
            ");
        }

        // ================= SIMPAN PHONE =================
        foreach ($phones as $i => $phone) {
            $phone = mysqli_real_escape_string($conn, $phone);
            $is_primary = ($i === array_key_first($phones)) ? 1 : 0;

            mysqli_query($conn, "
                INSERT INTO patient_phones (patient_id, phone, is_primary)
                VALUES ($patient_id, '$phone', $is_primary)
            ");
        }

        // ================= SIMPAN ADDRESS =================
        foreach ($addresses as $i => $address) {
            $address = mysqli_real_escape_string($conn, $address);
            $is_primary = ($i === array_key_first($addresses)) ? 1 : 0;

            mysqli_query($conn, "
                INSERT INTO patient_addresses (patient_id, alamat, is_primary)
                VALUES ($patient_id, '$address', $is_primary)
            ");
        }

        // ================= SIMPAN PELAYANAN =================
        if (!empty($_POST['vaksin'])) {
            foreach ($_POST['vaksin'] as $vaksin) {
                $vaksin = mysqli_real_escape_string($conn, $vaksin);
                mysqli_query($conn, "
                    INSERT INTO patient_services (patient_id, service_type, service_name)
                    VALUES ($patient_id, 'Vaksin', '$vaksin')
                ");
            }
        }

        if (!empty($_POST['vitamin'])) {
            foreach ($_POST['vitamin'] as $vitamin) {
                $vitamin = mysqli_real_escape_string($conn, $vitamin);
                mysqli_query($conn, "
                    INSERT INTO patient_services (patient_id, service_type, service_name)
                    VALUES ($patient_id, 'Vitamin', '$vitamin')
                ");
            }
        }

        if (!empty($_POST['antigen'])) {
            foreach ($_POST['antigen'] as $antigen) {
                $antigen = mysqli_real_escape_string($conn, $antigen);
                mysqli_query($conn, "
                    INSERT INTO patient_services (patient_id, service_type, service_name)
                    VALUES ($patient_id, 'Antigen', '$antigen')
                ");
            }
        }

        if (!empty($_POST['obat'])) {
            foreach ($_POST['obat'] as $obat) {
                $obat = mysqli_real_escape_string($conn, $obat);
                mysqli_query($conn, "
                    INSERT INTO patient_services (patient_id, service_type, service_name)
                    VALUES ($patient_id, 'Obat', '$obat')
                ");
            }
        }

        if (!empty($_POST['pcr'])) {
            foreach ($_POST['pcr'] as $pcr) {
                $pcr = mysqli_real_escape_string($conn, $pcr);
                mysqli_query($conn, "
                    INSERT INTO patient_services (patient_id, service_type, service_name)
                    VALUES ($patient_id, 'PCR', '$pcr')
                ");
            }
        }

        header("Location: calender.php?id_pasien=$patient_id");
        exit;

    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
