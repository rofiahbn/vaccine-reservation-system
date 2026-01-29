<?php
session_start();
include "../config.php";
require_once "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ✅ CEK PARAMETER EMAIL & WA
$send_email = isset($_GET['email']) && $_GET['email'] == '1';
$send_wa = isset($_GET['wa']) && $_GET['wa'] == '1';

if ($booking_id == 0) {
    die("Booking ID tidak valid");
}

if (!$send_email && !$send_wa) {
    echo "<script>
        alert('Pilih minimal satu metode pengiriman');
        window.location.href = 'pembayaran.php?id=$booking_id';
    </script>";
    exit;
}

/* Ambil data booking + pasien */
$sql = "SELECT b.*, 
               p.nama_lengkap, 
               p.no_rekam_medis,
               p.id as patient_id
        FROM bookings b 
        JOIN patients p ON b.patient_id = p.id 
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

/* Ambil data pembayaran */
$sql_pay = "SELECT * FROM payments 
            WHERE booking_id = ? 
            AND status = 'paid' 
            ORDER BY id DESC 
            LIMIT 1";
$stmt_pay = $conn->prepare($sql_pay);
$stmt_pay->bind_param("i", $booking_id);
$stmt_pay->execute();
$payment = $stmt_pay->get_result()->fetch_assoc();

/* Ambil email */
$sql_email = "SELECT email FROM patient_emails 
              WHERE patient_id = ? 
              ORDER BY is_primary DESC 
              LIMIT 1";
$stmt_em = $conn->prepare($sql_email);
$stmt_em->bind_param("i", $booking['patient_id']);
$stmt_em->execute();
$email = $stmt_em->get_result()->fetch_assoc()['email'] ?? null;

/* Ambil no HP */
$sql_phone = "SELECT phone FROM patient_phones 
              WHERE patient_id = ? 
              ORDER BY is_primary DESC 
              LIMIT 1";
$stmt_ph = $conn->prepare($sql_phone);
$stmt_ph->bind_param("i", $booking['patient_id']);
$stmt_ph->execute();
$phone = $stmt_ph->get_result()->fetch_assoc()['phone'] ?? null;

// Cek file PDF sudah ada atau belum
$filename = 'Faktur_' . $booking['nomor_antrian'] . '.pdf';
$pdf_path = __DIR__ . '/../uploads/invoice/' . $filename;

// Kalau belum ada, generate dulu dengan memanggil logic cetak_pembayaran.php
if (!file_exists($pdf_path)) {
    // Generate PDF (ambil logic dari cetak_pembayaran.php)
    include 'generate_pdf_logic.php'; // Kita buat file ini nanti
}

// ================= KIRIM EMAIL =================
$email_sent = false;
$email_error = '';

if ($email && $send_email) {
    try {
        $mail = new PHPMailer(true);
        
        // ✅ SMTP Settings (GMAIL)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rofiah9a@gmail.com';      // ⚠️ GANTI INI
        $mail->Password   = 'xxxx xxxx xxxx xxxx';    // ⚠️ GANTI INI (16 karakter dari Google)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('noreply@vaksinin.id', 'Vaksinin');
        $mail->addAddress($email, $booking['nama_lengkap']);
        
        // Attachment
        $mail->addAttachment($pdf_path, $filename);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Invoice Pembayaran - Vaksinin';
        $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Halo, " . htmlspecialchars($booking['nama_lengkap']) . "</h2>
            <p>Terima kasih telah melakukan pembayaran di <strong>Vaksinin</strong>.</p>
            <p>Terlampir adalah invoice pembayaran Anda dengan nomor antrian: <strong>" . $booking['nomor_antrian'] . "</strong></p>
            <br>
            <p>Jika ada pertanyaan, silakan hubungi kami di:</p>
            <ul>
                <li>Email: vaksinin.id@gmail.com</li>
                <li>Telepon: +62 821 3737 2757</li>
            </ul>
            <br>
            <p>Salam sehat,<br><strong>Tim Vaksinin</strong></p>
        </body>
        </html>
        ";
        
        $mail->send();
        $email_sent = true;
        
    } catch (Exception $e) {
        $email_error = $mail->ErrorInfo;
        error_log("Email error: " . $email_error);
    }
}

// ================= KIRIM WHATSAPP =================
$wa_sent = false;
$wa_error = '';

if ($phone && $send_wa) {
    // Format nomor HP
    $phone_formatted = preg_replace('/[^0-9]/', '', $phone);
    
    if (substr($phone_formatted, 0, 1) == '0') {
        $phone_formatted = '62' . substr($phone_formatted, 1);
    } elseif (substr($phone_formatted, 0, 2) != '62') {
        $phone_formatted = '62' . $phone_formatted;
    }
    
    // Pesan WhatsApp
    $message = "Halo *" . $booking['nama_lengkap'] . "*,\n\n" .
               "Terima kasih telah melakukan pembayaran di *Vaksinin*.\n\n" .
               "📋 No. Antrian: *" . $booking['nomor_antrian'] . "*\n\n" .
               "Invoice pembayaran Anda telah dikirim melalui email. " .
               "Jika belum menerima, silakan hubungi kami.\n\n" .
               "Salam sehat,\n" .
               "*Tim Vaksinin* 💉";
    
    // OPSI 1: Redirect ke WhatsApp (USER KLIK SEND MANUAL)
    // Simpan flag untuk redirect setelah email terkirim
    $wa_sent = true; // Anggap sukses karena akan redirect
    
    /* OPSI 2: GUNAKAN API FONNTE (OTOMATIS, BERBAYAR)
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
            'target' => $phone_formatted,
            'message' => $message,
            'countryCode' => '62'
        ),
        CURLOPT_HTTPHEADER => array(
            'Authorization: YOUR_FONNTE_TOKEN'  // Ganti dengan token Fonnte
        ),
    ));
    
    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);
    
    if ($httpcode == 200) {
        $wa_sent = true;
    } else {
        $wa_error = $response;
        error_log("WhatsApp error: " . $response);
    }
    */
}

// ================= RESPONSE =================
if ($email_sent || $wa_sent) {
    $sent_to = [];
    if ($email_sent) $sent_to[] = "Email ($email)";
    if ($wa_sent) $sent_to[] = "WhatsApp ($phone)";
    
    // Kalau WhatsApp pakai redirect manual, redirect ke WA setelah email sukses
    if ($wa_sent && $phone) {
        $phone_formatted = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone_formatted, 0, 1) == '0') {
            $phone_formatted = '62' . substr($phone_formatted, 1);
        }
        $wa_message = urlencode(
            "Halo *" . $booking['nama_lengkap'] . "*,\n\n" .
            "Terima kasih telah melakukan pembayaran di *Vaksinin*.\n\n" .
            "📋 No. Antrian: *" . $booking['nomor_antrian'] . "*\n\n" .
            "Invoice pembayaran Anda telah dikirim melalui email.\n\n" .
            "Salam sehat,\n*Tim Vaksinin* 💉"
        );
        
        echo "<script>
            alert('Invoice berhasil dikirim ke " . implode(" dan ", $sent_to) . "');
            window.open('https://wa.me/$phone_formatted?text=$wa_message', '_blank');
            window.location.href = 'pembayaran.php?id=$booking_id';
        </script>";
    } else {
        echo "<script>
            alert('Invoice berhasil dikirim ke " . implode(" dan ", $sent_to) . "');
            window.location.href = 'pembayaran.php?id=$booking_id';
        </script>";
    }
} else {
    $error_msg = [];
    if ($email_error) $error_msg[] = "Email: $email_error";
    if ($wa_error) $error_msg[] = "WA: $wa_error";
    
    $error_text = !empty($error_msg) ? implode(", ", $error_msg) : "Email/nomor HP tidak terdaftar";
    
    echo "<script>
        alert('Gagal mengirim invoice. $error_text');
        window.location.href = 'pembayaran.php?id=$booking_id';
    </script>";
}
?>