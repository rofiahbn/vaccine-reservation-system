<?php
session_start();
include "../config.php";

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id == 0) {
    header('Location: dashboard.php');
    exit;
}

/* Ambil data booking + pasien */
$sql = "SELECT b.*, b.payment_status, 
               p.nama_lengkap, 
               p.no_rekam_medis
        FROM bookings b 
        JOIN patients p ON b.patient_id = p.id 
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if ($booking['tindakan_selesai'] == 0) {
    echo "<script>
        alert('Simpan tindakan terlebih dahulu sebelum melakukan pembayaran');
        window.location.href = 'booking_detail.php?id=$booking_id';
    </script>";
    exit;
}

// ambil data pembayaran terakhir jika sudah paid
$sql_pay = "SELECT * FROM payments 
            WHERE booking_id = ? 
            AND status = 'paid' 
            ORDER BY id DESC 
            LIMIT 1";
$stmt_pay = $conn->prepare($sql_pay);
$stmt_pay->bind_param("i", $booking_id);
$stmt_pay->execute();
$payment = $stmt_pay->get_result()->fetch_assoc();

/* Ambil no HP utama */
$sql_phone = "SELECT phone FROM patient_phones 
              WHERE patient_id = ? 
              ORDER BY is_primary DESC 
              LIMIT 1";
$stmt_ph = $conn->prepare($sql_phone);
$stmt_ph->bind_param("i", $booking['patient_id']);
$stmt_ph->execute();
$phone = $stmt_ph->get_result()->fetch_assoc()['phone'] ?? '-';

/* Ambil alamat utama */
$sql_addr = "SELECT * FROM patient_addresses 
             WHERE patient_id = ? 
             AND is_primary = 1 
             LIMIT 1";
$stmt_ad = $conn->prepare($sql_addr);
$stmt_ad->bind_param("i", $booking['patient_id']);
$stmt_ad->execute();
$address = $stmt_ad->get_result()->fetch_assoc();

/* Ambil layanan + harga */
$sql_services = "
    SELECT nama_layanan, harga, diskon, diskon_tipe, total 
    FROM booking_services 
    WHERE booking_id = ?
";
$stmt_s = $conn->prepare($sql_services);
$stmt_s->bind_param("i", $booking_id);
$stmt_s->execute();
$services = $stmt_s->get_result();

$payment_status = $booking['payment_status'] ?? 'unpaid';

/* Hitung subtotal */
$subtotal = 0;
$data_services = [];

while ($row = $services->fetch_assoc()) {

    $row['jumlah'] = 1;

    // kalau sudah dibayar → ambil dari DB
    if ($payment_status == 'paid') {

        $row['diskon'] = $row['diskon'] ?? 0;
        $row['total']  = $row['total'] ?? $row['harga'];

    } else {

        // MODE BELUM BAYAR (default)
        $row['diskon'] = 0;
        $row['total'] = $row['harga'];
    }

    $subtotal += $row['harga'];
    $data_services[] = $row;
}

if ($payment_status == 'paid' && $payment) {

    $subtotal = $payment['subtotal'];
    $diskon   = $payment['diskon'];
    $total    = $payment['total'];

} else {

    $diskon = 0;
    $total  = $subtotal;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Proses Pembayaran</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/pembayaran.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- ================= SIDEBAR ================= -->
    <div class="sidebar">
        <div class="logo">
            <img src="vaksinin-logo.png" alt="Vaksinin">
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </nav>
    </div>

    <!-- ================= MAIN CONTENT ================= -->
    <div class="main-content">

        <!-- HEADER -->
        <div class="detail-header">
            <button onclick="window.location.href='booking_detail.php?id=<?= $booking_id ?>'" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
            <h1>Proses Pembayaran</h1>
        </div>

    <div class="payment-layout">

        <!-- KIRI -->
        <div class="payment-left">
            <h3>Pembayaran</h3>
            <p><b>Nama Pasien :</b> <?= htmlspecialchars($booking['nama_lengkap']) ?></p>
            <p><b>No Rekam Medis :</b> <?= $booking['no_rekam_medis'] ?></p>
            <p><b>No Antrian :</b> <?= $booking['nomor_antrian'] ?></p>
            <p><b>No HP :</b> <?= htmlspecialchars($phone) ?></p>

            <?php if ($address): ?>
                <p><b>Alamat :</b> 
                    <?= htmlspecialchars($address['alamat']) ?>, 
                    <?= htmlspecialchars($address['kota']) ?>, 
                    <?= htmlspecialchars($address['provinsi']) ?>
                </p>
            <?php else: ?>
                <p><b>Alamat :</b> -</p>
            <?php endif; ?>
            
            <p><b>Tanggal Pelayanan :</b> <?= date('d F Y', strtotime($booking['tanggal_booking'])) ?></p>
            <br><br>

            <table border="1" width="100%" cellpadding="8">
                <tr>
                    <th>No</th>
                    <th>Deskripsi</th>
                    <th>Jml</th>
                    <th>Harga</th>
                    <th>Diskon</th>
                    <th>Total</th>
                </tr>

                <?php foreach ($data_services as $i => $srv): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($srv['nama_layanan']) ?></td>
                    <td><?= $srv['jumlah'] ?></td>
                    <td>Rp <?= number_format($srv['harga'],0,',','.') ?></td>

                    <!-- KOLOM DISKON (WAJIB DITUTUP) -->
                    <td id="diskon-<?= $i ?>">
                        <div style="text-align:center;">

                            <?php if ($payment_status == 'paid'): ?>

                                <?php if ($srv['diskon'] > 0): ?>

                                    <?php if ($srv['diskon_tipe'] == 'persen'): ?>
                                        <div><?= round(($srv['diskon'] / $srv['harga']) * 100) ?>% (Rp <?= number_format($srv['diskon'],0,',','.') ?>)</div>
                                    <?php else: ?>
                                        <div>Rp <?= number_format($srv['diskon'],0,',','.') ?></div>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <div>-</div>
                                <?php endif; ?>

                            <?php else: ?>

                                <!-- MODE BELUM BAYAR -->
                                <div>0</div>
                                <button 
                                    type="button" 
                                    class="btn-diskon" 
                                    onclick="openDiskon(<?= $i ?>, <?= $srv['harga'] ?>)">
                                    Tambahkan Diskon
                                </button>

                            <?php endif; ?>

                        </div>
                    </td>

                    <!-- KOLOM TOTAL -->
                    <td id="total-<?= $i ?>">
                        Rp <?= number_format($srv['total'],0,',','.') ?>
                    </td>
                </tr>
                <?php endforeach; ?>

            </table>

        </div>

        <!-- KANAN -->
        <div class="payment-right">

            <div class="card">
                <h4>Status</h4>

                <?php if ($payment_status == 'paid'): ?>
                    <span class="badge badge-success">Sudah Bayar</span>
                <?php else: ?>
                    <span class="badge badge-warning">Belum Bayar</span>
                    <br><br>
                <?php endif; ?>
            </div>

            <div class="card total-card">
                <h4>Total Pembayaran</h4>

                <div class="total-line">
                    <span class="label">Subtotal</span>
                    <span class="colon">:</span>
                    <span class="value" id="subtotalText">Rp <?= number_format($subtotal,0,',','.') ?></span>
                </div>

                <div class="total-line">
                    <span class="label">Diskon</span>
                    <span class="colon">:</span>
                    <span class="value" id="diskonText">Rp <?= number_format($diskon,0,',','.') ?></span>
                </div>

                <hr>

                <div class="total-final-line">
                    <span class="label">Total</span>
                    <span class="colon">:</span>
                    <strong class="final-value" id="totalText">Rp <?= number_format($total,0,',','.') ?></strong>
                </div>
            </div>


            <div class="card"> 
                <h4>Metode Pembayaran</h4>
                <select id="metode_pembayaran">
                    <option value="tunai">Tunai</option>
                    <option value="transfer">Transfer</option>
                    <option value="qris">QRIS</option>
                </select>
            </div>

            <div class="pay-action">

                <?php if ($payment_status == 'paid'): ?>

                    <!-- MODE SUDAH BAYAR -->
                    <button class="btn-cetak" onclick="cetakPembayaran()">
                        Cetak Pembayaran
                    </button>

                    <button class="btn-invoice" onclick="kirimInvoice()">
                        Kirim Ulang Invoice
                    </button>

                <?php else: ?>

                    <!-- MODE BELUM BAYAR -->
                    <button class="btn-bayar-big" onclick="openBayar()">
                        Bayar
                    </button>

                <?php endif; ?>

                </div>

        </div>
        
    </div>
</div>

<div id="popupBayar" class="popup-overlay" style="display:none;">
    <div class="popup-box">

        <h2>Pembayaran</h2>
        <p>
            Pastikan data pembayaran sudah sesuai.  
            “Konfirmasi Pembayaran” untuk melanjutkan
        </p>

        <form action="proses_bayar.php" method="GET">
            
            <input type="hidden" name="id" value="<?= $booking_id ?>">
            <input type="hidden" name="metode" id="metodeInput">

            <input type="hidden" name="subtotal" id="sendSubtotal">
            <input type="hidden" name="diskon" id="sendDiskon">
            <input type="hidden" name="total" id="sendTotal">
            <input type="hidden" name="detail_diskon" id="sendDetailDiskon">

            <div style="margin:20px 0;">
                <label>
                    <input type="checkbox" name="invoice_email"> Email
                </label>
                &nbsp;&nbsp;
                <label>
                    <input type="checkbox" name="invoice_wa"> Whatsapp / No. Telpon
                </label>
            </div>

            <button type="submit" class="btn-primary">
                Konfirmasi Pembayaran
            </button>

            <button type="button" class="btn-danger" onclick="closePopup()">
                Batal
            </button>

        </form>

    </div>
</div>

<div id="popupDiskon" class="popup-overlay" style="display:none;">
    <div class="popup-box diskon-box">

        <!-- tombol close -->
        <button class="popup-close" onclick="closeDiskonPopup()">×</button>

        <h2>Diskon</h2>
        <p>Silahkan pilih dan isi nominal diskon yang berikan</p>

        <div class="diskon-form">

            <div class="diskon-row">
                <label>
                    <input type="radio" id="diskonPersen" name="tipeDiskon">
                    Persen (%)
                </label>
                <input type="number" id="inputPersen" placeholder="1 - 100">
            </div>

            <div class="diskon-row">
                <label>
                    <input type="radio" id="diskonNilai" name="tipeDiskon">
                    Nilai
                </label>
                <input type="number" id="inputNilai" placeholder="Rp">
            </div>

        </div>

        <button class="btn-primary" onclick="applyDiskon()">Selesai</button>
        <button class="btn-danger" onclick="closeDiskonPopup()">Batal</button>

    </div>
</div>

<div id="popupSelesai" class="popup-overlay" style="display:none;">
    <div class="popup-box">

        <h2>Pembayaran Selesai</h2>
        <p>
            Pembayaran telah selesai, silahkan kembali  
            ke halaman utama
        </p>

        <button class="btn-primary" onclick="cetakPembayaran()">
            Cetak Pembayaran
        </button>

        <button class="btn-danger" onclick="window.location.href='pembayaran.php?id=<?= $booking_id ?>'">
            Selesai
        </button>

    </div>
</div>

<script>
let currentRow = null;
let currentHarga = 0;

// simpan semua diskon
let diskonData = {};   // { rowIndex: { persen: 10, nominal: 20000 } }

function openBayar() {
    const metode = document.getElementById('metode_pembayaran').value;

    if (!metode) {
        alert("Pilih metode pembayaran dulu");
        return;
    }

    // ambil nilai panel kanan
    document.getElementById('sendSubtotal').value = finalSubtotal;
    document.getElementById('sendDiskon').value   = finalDiskon;
    document.getElementById('sendTotal').value    = finalTotal;

    // kirim detail diskon per item (JSON)
    document.getElementById('sendDetailDiskon').value = JSON.stringify(diskonData);

    // isi metode ke hidden input
    document.getElementById('metodeInput').value = metode;

    document.getElementById('popupBayar').style.display = 'flex';
}

function closePopup() {
    document.getElementById('popupBayar').style.display = 'none';
}

function openDiskon(rowIndex, harga) {
    currentRow = rowIndex;
    currentHarga = harga;

    // reset radio
    document.getElementById('diskonPersen').checked = false;
    document.getElementById('diskonNilai').checked = false;

    // reset input angka
    document.getElementById('inputPersen').value = '';
    document.getElementById('inputNilai').value = '';

    document.getElementById('popupDiskon').style.display = 'flex';
}

function applyDiskon() {
    const persenChecked = document.getElementById('diskonPersen').checked;
    const nilaiChecked  = document.getElementById('diskonNilai').checked;

    let diskon = 0;
    let persen = 0;

    if (persenChecked) {
        persen = parseInt(document.getElementById('inputPersen').value || 0);

        if (persen <= 0 || persen > 100) {
            alert("Persen harus antara 1 - 100");
            return;
        }

        // hitung nominal dari persen
        diskon = Math.round(currentHarga * persen / 100);

    } else if (nilaiChecked) {
        diskon = parseInt(document.getElementById('inputNilai').value || 0);

        if (diskon <= 0) {
            alert("Isi nilai diskon dulu");
            return;
        }

        if (diskon > currentHarga) {
            alert("Diskon tidak boleh lebih besar dari harga");
            return;
        }

        persen = Math.round((diskon / currentHarga) * 100);

    } else {
        alert("Pilih jenis diskon dulu");
        return;
    }

    // simpan ke diskonData
    diskonData[currentRow] = {
        tipe: persenChecked ? 'persen' : 'nilai',
        persen: persen,
        nominal: diskon
    };

    // total baru per item
    const total = currentHarga - diskon;

    // update kolom diskon (TAMPIL PERSEN + NOMINAL)
    let displayText = '';

    if (persenChecked) {
        // kalau tipe persen → tampil persen + nominal
        displayText = `${persen}% (Rp ${diskon.toLocaleString()})`;
    } else {
        // kalau tipe nilai → tampil nominal aja
        displayText = `Rp ${diskon.toLocaleString()}`;
    }

    document.getElementById('diskon-' + currentRow).innerHTML = `
        <div style="text-align:center;">
            <div>${displayText}</div>
            <button 
                type="button" 
                class="btn-diskon" 
                onclick="openDiskon(${currentRow}, ${currentHarga})">
                Edit Diskon
            </button>
        </div>
    `;

    // update kolom total
    document.getElementById('total-' + currentRow).innerText =
        "Rp " + total.toLocaleString();

    // update panel kanan
    updateTotalRightPanel();

    // tutup popup
    closeDiskonPopup();
}

let finalSubtotal = 0;
let finalDiskon = 0;
let finalTotal = 0;

function updateTotalRightPanel() {
    const table = document.querySelector(".payment-left table");
    let subtotal = 0;
    let totalDiskon = 0;

    // loop semua baris item
    for (let i = 1; i < table.rows.length; i++) {
        const row = table.rows[i];

        // ambil harga asli
        const hargaText = row.cells[3].innerText.replace(/[^\d]/g, '');
        const harga = parseInt(hargaText || 0);

        subtotal += harga;

        // ambil diskon dari diskonData
        const data = diskonData[i-1];
        if (data) {
            totalDiskon += data.nominal;
        }
    }

    const finalTotal = subtotal - totalDiskon;

    // cek apakah ADA diskon persen
    let adaPersen = false;
    let totalPersen = 0;
    let persenCount = 0;

    for (let key in diskonData) {
        if (diskonData[key].tipe === 'persen') {
            adaPersen = true;
            totalPersen += diskonData[key].persen;
            persenCount++;
        }
    }

    // hitung persen tampilan (rata-rata persen item yg persen)
    let persenTampil = 0;
    if (adaPersen && persenCount > 0) {
        persenTampil = Math.round(totalPersen / persenCount);
    }

    // update subtotal
    document.getElementById('subtotalText').innerText =
        "Rp " + subtotal.toLocaleString();

    // update diskon
    if (totalDiskon > 0) {

        if (adaPersen) {
            // tampil persen + nominal
            document.getElementById('diskonText').innerText =
                `${persenTampil}% (Rp ${totalDiskon.toLocaleString()})`;
        } else {
            // SEMUA NILAI → tampil nominal saja
            document.getElementById('diskonText').innerText =
                `Rp ${totalDiskon.toLocaleString()}`;
        }

    } else {
        document.getElementById('diskonText').innerText = "Rp 0";
    }

    // update total akhir
    document.getElementById('totalText').innerText =
        "Rp " + finalTotal.toLocaleString();
}

function closeDiskonPopup() {
    document.getElementById('popupDiskon').style.display = 'none';
}

function openPopupSelesai() {
    document.getElementById('popupSelesai').style.display = 'flex';
}

function cetakPembayaran() {
    window.open('cetak_pembayaran.php?id=<?= $booking_id ?>', '_blank');
}

function kirimInvoice() {
    alert("Invoice berhasil dikirim ulang ke email / WhatsApp pasien");
    // nanti bisa diarahkan ke file kirim_invoice.php
}

</script>

<?php if ($payment_status == 'paid' && isset($_GET['success'])): ?>
<script>
    window.onload = function() {
        openPopupSelesai();
    }
</script>
<?php endif; ?>

</body>
</html>
