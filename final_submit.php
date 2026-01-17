<?php
session_start();
include "config.php";

// Cek apakah ada peserta di session
if (!isset($_SESSION['participants']) || empty($_SESSION['participants'])) {
    $_SESSION['error_message'] = 'Tidak ada data peserta yang akan disimpan';
    header('Location: order.php');
    exit;
}

$participants = $_SESSION['participants'];
$success_bookings = [];
$failed_bookings = [];

// Start transaction
mysqli_begin_transaction($conn);

try {
    foreach ($participants as $index => $p) {
        // 1. GENERATE NOMOR REKAM MEDIS
        // GENERATE NOMOR REKAM MEDIS UNIK
            $prefix_rm = 'RM' . date('Ymd');

            // Ambil RM terakhir hari ini
            $query_rm = "SELECT MAX(no_rekam_medis) as last_rm 
                        FROM patients 
                        WHERE no_rekam_medis LIKE CONCAT(?, '%')";

            $stmt_rm = mysqli_prepare($conn, $query_rm);
            mysqli_stmt_bind_param($stmt_rm, 's', $prefix_rm);
            mysqli_stmt_execute($stmt_rm);
            $result_rm = mysqli_stmt_get_result($stmt_rm);
            $row_rm = mysqli_fetch_assoc($result_rm);

            if ($row_rm['last_rm']) {
                $last_number = (int) substr($row_rm['last_rm'], -4);
                $next_number = $last_number + 1;
            } else {
                $next_number = 1;
            }

            $no_rekam_medis = $prefix_rm . str_pad($next_number, 4, '0', STR_PAD_LEFT);


        $nik    = !empty($p['nik']) ? $p['nik'] : null;
        $paspor = !empty($p['paspor']) ? $p['paspor'] : null;
        
        // 2. INSERT KE TABLE PATIENTS
        $query_patient = "INSERT INTO patients 
            (no_rekam_medis, nama_lengkap, nama_panggilan, tanggal_lahir, usia, kategori_usia, 
            jenis_kelamin, nik, paspor, kebangsaan, pekerjaan, nama_wali, 
            riwayat_alergi, riwayat_penyakit, riwayat_obat, pelayanan, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = mysqli_prepare($conn, $query_patient);
        
        mysqli_stmt_bind_param($stmt, 'ssssisssssssssss',
            $no_rekam_medis,
            $p['nama_lengkap'],
            $p['nama_panggilan'],
            $p['tanggal_lahir'],
            $p['usia'],
            $p['kategori_usia'],
            $p['jenis_kelamin'],
            $nik,
            $paspor,
            $p['kebangsaan'],
            $p['pekerjaan'],
            $p['nama_wali'],
            $p['riwayat_alergi'],
            $p['riwayat_penyakit'],
            $p['riwayat_obat'],
            $p['pelayanan']
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal menyimpan data pasien: " . mysqli_error($conn));
        }
        
        $patient_id = mysqli_insert_id($conn);
        
        // 3. INSERT KE TABLE PATIENT_EMAILS
        if (!empty($p['emails'])) {
            foreach ($p['emails'] as $idx => $email) {
                $is_primary = ($idx === 0) ? 1 : 0;
                
                $query_email = "INSERT INTO patient_emails (patient_id, email, is_primary) VALUES (?, ?, ?)";
                $stmt_email = mysqli_prepare($conn, $query_email);
                mysqli_stmt_bind_param($stmt_email, 'isi', $patient_id, $email, $is_primary);
                
                if (!mysqli_stmt_execute($stmt_email)) {
                    throw new Exception("Gagal menyimpan email: " . mysqli_error($conn));
                }
            }
        }
        
        // 4. INSERT KE TABLE PATIENT_PHONES
        if (!empty($p['phones'])) {
            foreach ($p['phones'] as $idx => $phone) {
                $is_primary = ($idx === 0) ? 1 : 0;
                
                $query_phone = "INSERT INTO patient_phones (patient_id, phone, is_primary) VALUES (?, ?, ?)";
                $stmt_phone = mysqli_prepare($conn, $query_phone);
                mysqli_stmt_bind_param($stmt_phone, 'isi', $patient_id, $phone, $is_primary);
                
                if (!mysqli_stmt_execute($stmt_phone)) {
                    throw new Exception("Gagal menyimpan nomor telepon: " . mysqli_error($conn));
                }
            }
        }
        
        // 5. INSERT KE TABLE PATIENT_ADDRESSES
        $is_primary = 1;
        $query_address = "INSERT INTO patient_addresses (patient_id, alamat, provinsi, kota, is_primary) 
                         VALUES (?, ?, ?, ?, ?)";
        $stmt_address = mysqli_prepare($conn, $query_address);
        mysqli_stmt_bind_param($stmt_address, 'isssi', 
            $patient_id, 
            $p['alamat'], 
            $p['provinsi'], 
            $p['kota'], 
            $is_primary
        );
        
        if (!mysqli_stmt_execute($stmt_address)) {
            throw new Exception("Gagal menyimpan alamat: " . mysqli_error($conn));
        }
        
        // 6. CEK SLOT MASIH TERSEDIA
        $query_check = "SELECT COUNT(*) as total FROM bookings 
                       WHERE tanggal_booking = ? AND waktu_booking = ?";
        $stmt_check = mysqli_prepare($conn, $query_check);
        mysqli_stmt_bind_param($stmt_check, 'ss', $p['tanggal_booking'], $p['waktu_booking']);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $row_check = mysqli_fetch_assoc($result_check);
        
        if ($row_check['total'] > 0) {
            throw new Exception("Slot tanggal " . $p['tanggal_booking'] . " jam " . $p['waktu_booking'] . " sudah penuh. Silakan pilih jadwal lain.");
        }
        
        // 7. GENERATE NOMOR ANTRIAN
        // Format: YYYYMMDD-001, YYYYMMDD-002, dst
        $tanggal_booking = $p['tanggal_booking'];
        $prefix_antrian = date('Ymd', strtotime($tanggal_booking));
        
        $query_antrian = "SELECT MAX(CAST(SUBSTRING(nomor_antrian, 10) AS UNSIGNED)) as max_no 
                         FROM bookings 
                         WHERE tanggal_booking = ?";
        $stmt_antrian = mysqli_prepare($conn, $query_antrian);
        mysqli_stmt_bind_param($stmt_antrian, 's', $tanggal_booking);
        mysqli_stmt_execute($stmt_antrian);
        $result_antrian = mysqli_stmt_get_result($stmt_antrian);
        $row_antrian = mysqli_fetch_assoc($result_antrian);
        
        $next_no = ($row_antrian['max_no'] ?? 0) + 1;
        $nomor_antrian = $prefix_antrian . '-' . str_pad($next_no, 3, '0', STR_PAD_LEFT);
        
        // 8. INSERT KE TABLE BOOKINGS
        $status = 'pending'; // Status awal
        $catatan = 'Pendaftaran online';
        
        $query_booking = "INSERT INTO bookings 
                         (patient_id, nomor_antrian, tanggal_booking, waktu_booking, status, catatan, created_at, updated_at)
                         VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt_booking = mysqli_prepare($conn, $query_booking);
        mysqli_stmt_bind_param($stmt_booking, 'isssss',
            $patient_id,
            $nomor_antrian,
            $p['tanggal_booking'],
            $p['waktu_booking'],
            $status,
            $catatan
        );
        
        if (!mysqli_stmt_execute($stmt_booking)) {
            throw new Exception("Gagal membuat booking: " . mysqli_error($conn));
        }
        
        $booking_id = mysqli_insert_id($conn);
        
        // Simpan info sukses
        $success_bookings[] = [
            'nama' => $p['nama_lengkap'],
            'no_rekam_medis' => $no_rekam_medis,
            'nomor_antrian' => $nomor_antrian,
            'tanggal_booking' => $p['tanggal_booking'],
            'waktu_booking' => $p['waktu_booking'],
            'patient_id' => $patient_id,
            'booking_id' => $booking_id
        ];
    }
    
    // Commit transaction jika semua berhasil
    mysqli_commit($conn);
    
    // Simpan data sukses ke session untuk ditampilkan di success page
    $_SESSION['success_bookings'] = $success_bookings;
    
    // Hapus data participants dari session
    unset($_SESSION['participants']);
    
    // Redirect ke success page
    header('Location: booking_success.php');
    exit;
    
} catch (Exception $e) {
    // Rollback jika ada error
    mysqli_rollback($conn);
    
    // Simpan error message
    $_SESSION['error_message'] = $e->getMessage();
    
    // Redirect kembali ke konfirmasi
    header('Location: booking_confirmation.php');
    exit;
}
?>