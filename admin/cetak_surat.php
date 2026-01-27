<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../config.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;

header('Content-Type: application/json');

$booking_id    = $_POST['booking_id'];
$patient_id    = $_POST['patient_id'];
$jenis_surat   = $_POST['jenis_surat'];
$dokter_id     = $_POST['dokter_id'];
$posisi        = $_POST['posisi'];
$tanggal_surat = date("Y-m-d");

$lama_istirahat = $_POST['lama_istirahat'] ?? null;
$tgl_awal       = $_POST['tgl_awal'] ?? null;
$tgl_akhir      = $_POST['tgl_akhir'] ?? null;
$pf_lain        = $_POST['pf_lain'] ?? null;

$jenis_vaksin   = $_POST['jenis_vaksin'] ?? null;
$batch_vaksin   = $_POST['batch_vaksin'] ?? null;
$expired_vaksin = $_POST['expired_vaksin'] ?? null;

$html = $_POST['html_surat'] ?? '';

// ================= NORMALISASI DATA KOSONG JADI NULL =================
if ($lama_istirahat === "") $lama_istirahat = null;
if ($tgl_awal === "")        $tgl_awal = null;
if ($tgl_akhir === "")       $tgl_akhir = null;
if ($expired_vaksin === "")  $expired_vaksin = null;
if ($pf_lain === "")         $pf_lain = null;
if ($jenis_vaksin === "")    $jenis_vaksin = null;
if ($batch_vaksin === "")    $batch_vaksin = null;

if (!$html) {
    echo json_encode(['success' => false, 'message' => 'HTML surat kosong']);
    exit;
}

/* ================= WRAP HTML DENGAN CSS ================= */
$fullHtml = "
<html>
<head>
    <meta charset='UTF-8'>
    <link rel='stylesheet' href='../admin/css/surat.css'>
    <style>
        @page { size: A4; margin: 0; }
        body { margin: 0; padding: 0; }
    </style>
</head>
<body>
    $html
</body>
</html>
";

/* ================= GENERATE PDF ================= */
$dompdf = new Dompdf();
$dompdf->loadHtml($fullHtml);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/* ================= SIMPAN FILE ================= */
$nama_file = "surat_" . time() . "_" . $booking_id . ".pdf";
$folder = "../uploads/surat/";

if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

$path = $folder . $nama_file;
file_put_contents($path, $dompdf->output());

/* ================= SIMPAN KE DATABASE ================= */
$stmt = $conn->prepare("
INSERT INTO surat
(booking_id, patient_id, jenis_surat, dokter_id, posisi, tanggal_surat,
 lama_istirahat, tgl_awal, tgl_akhir, pf_lain,
 jenis_vaksin, batch_vaksin, expired_vaksin, file_pdf, created_at)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())
");

// ✅ CEK SETELAH PREPARE
if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'PREPARE GAGAL: ' . $conn->error
    ]);
    exit;
}

// ✅ BIND PARAM YANG BENAR (dokter_id = integer)
$stmt->bind_param(
    "iisissssssssss",  // i=int, s=string
    $booking_id,       // i
    $patient_id,       // i
    $jenis_surat,      // s
    $dokter_id,        // i ← PERBAIKAN INI
    $posisi,           // s
    $tanggal_surat,    // s
    $lama_istirahat,   // s (nullable)
    $tgl_awal,         // s (nullable)
    $tgl_akhir,        // s (nullable)
    $pf_lain,          // s (nullable)
    $jenis_vaksin,     // s (nullable)
    $batch_vaksin,     // s (nullable)
    $expired_vaksin,   // s (nullable)
    $nama_file         // s
);

if (!$stmt->execute()) {
    echo json_encode([
        'success' => false,
        'message' => 'Execute gagal: ' . $stmt->error
    ]);
    exit;
}

/* ================= RETURN URL PDF ================= */
echo json_encode([
    'success' => true,
    'file' => "uploads/surat/" . $nama_file
]);