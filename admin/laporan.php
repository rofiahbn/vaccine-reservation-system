<?php
session_start();
include "../config.php";

$current_page = 'laporan.php';

// Ambil filter bulan & tahun dari URL
$bulan_filter = isset($_GET['bulan']) ? $_GET['bulan'] : date('n');
$tahun_filter = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Get current tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'pendapatan';

// Date filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// ================= LAPORAN PENDAPATAN =================
if ($active_tab == 'pendapatan') {
    
    // Total Pendapatan Bulan Ini (booking selesai DAN sudah dibayar)
    $sql_pendapatan = "SELECT COALESCE(SUM(bs.harga), 0) as total 
                    FROM bookings b
                    JOIN booking_services bs ON b.id = bs.booking_id
                    WHERE b.status = 'completed' 
                    AND b.payment_status = 'paid'
                    AND MONTH(b.tanggal_booking) = $bulan_filter 
                    AND YEAR(b.tanggal_booking) = $tahun_filter";
    $result_pendapatan = $conn->query($sql_pendapatan);

    if ($result_pendapatan) {
        $row = $result_pendapatan->fetch_assoc();
        $total_pendapatan = $row['total'] ?? 0;
    } else {
        $total_pendapatan = 0;
        error_log("Error SQL pendapatan: " . $conn->error);
    }
    
    // Total Transaksi (semua booking yang confirmed atau completed)
    $sql_transaksi = "SELECT COUNT(DISTINCT b.id) as total 
                    FROM bookings b
                    WHERE b.status IN ('confirmed', 'completed')
                    AND MONTH(b.tanggal_booking) = $bulan_filter
                    AND YEAR(b.tanggal_booking) = $tahun_filter";
    $result_transaksi = $conn->query($sql_transaksi);

    if ($result_transaksi) {
        $row = $result_transaksi->fetch_assoc();
        $total_transaksi = $row['total'] ?? 0;
    } else {
        $total_transaksi = 0;
        error_log("Error SQL transaksi: " . $conn->error);
    }
    
    // Layanan Terlaris (berdasarkan jumlah pemesanan - termasuk paket)
    $sql_terlaris = "SELECT 
                        s.nama_layanan,
                        COUNT(bs.id) as total_pemesanan
                    FROM booking_services bs
                    JOIN services s ON bs.service_id = s.id
                    JOIN bookings b ON bs.booking_id = b.id
                    WHERE b.status = 'completed' 
                    AND b.payment_status = 'paid'
                    AND MONTH(b.tanggal_booking) = $bulan_filter 
                    AND YEAR(b.tanggal_booking) = $tahun_filter
                    GROUP BY s.id, s.nama_layanan
                    ORDER BY total_pemesanan DESC
                    LIMIT 1";
    $result_terlaris = $conn->query($sql_terlaris);

    if ($result_terlaris) {
        $layanan_terlaris = $result_terlaris->fetch_assoc();
        $layanan_terlaris_nama = $layanan_terlaris['nama_layanan'] ?? '-';
        $layanan_terlaris_jumlah = $layanan_terlaris['total_pemesanan'] ?? 0;
    } else {
        $layanan_terlaris_nama = '-';
        $layanan_terlaris_jumlah = 0;
        error_log("Error SQL terlaris: " . $conn->error);
    }
    
    // Tren Pendapatan Harian (30 hari terakhir) - booking selesai DAN sudah dibayar
    $sql_tren = "SELECT 
                DATE(b.tanggal_booking) as tanggal, 
                COALESCE(SUM(bs.harga), 0) as total
            FROM bookings b
            JOIN booking_services bs ON b.id = bs.booking_id
            WHERE b.status = 'completed' 
            AND b.payment_status = 'paid'
            AND MONTH(b.tanggal_booking) = $bulan_filter
            AND YEAR(b.tanggal_booking) = $tahun_filter
            GROUP BY DATE(b.tanggal_booking)
            ORDER BY tanggal ASC";
    $result_tren = $conn->query($sql_tren);
    $tren_data = [];

    if ($result_tren) {
        while ($row = $result_tren->fetch_assoc()) {
            $tren_data[] = $row;
        }
    } else {
        error_log("Error SQL tren: " . $conn->error);
    }
}

// ================= LAPORAN INVOICE =================
if ($active_tab == 'invoice') {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
    
    // Query dasar dengan filter status dan tanggal
    $sql_invoice = "SELECT 
                        b.id, 
                        b.nomor_antrian, 
                        p.nama_lengkap, 
                        b.tanggal_booking, 
                        b.payment_status,
                        b.status,
                        (SELECT COALESCE(SUM(harga), 0) FROM booking_services WHERE booking_id = b.id) as total_tagihan,
                        (SELECT GROUP_CONCAT(DISTINCT s.nama_layanan SEPARATOR ', ') 
                         FROM booking_services bs 
                         JOIN services s ON bs.service_id = s.id 
                         WHERE bs.booking_id = b.id) as layanan
                    FROM bookings b
                    JOIN patients p ON b.patient_id = p.id
                    WHERE b.status IN ('confirmed', 'completed')
                    AND MONTH(b.tanggal_booking) = $bulan_filter 
                    AND YEAR(b.tanggal_booking) = $tahun_filter";
    
    // Tambahkan search jika ada
    if (!empty($search)) {
        $sql_invoice .= " AND (b.id LIKE '%$search%' OR p.nama_lengkap LIKE '%$search%' OR b.nomor_antrian LIKE '%$search%')";
    }
    
    // Tambahkan filter status lunas/belum lunas
    if ($status_filter == 'lunas') {
        $sql_invoice .= " AND (b.payment_status = 'paid' AND b.status = 'completed')";
    } elseif ($status_filter == 'belum_lunas') {
        $sql_invoice .= " AND (b.payment_status != 'paid' OR b.status != 'completed')";
    }
    
    $sql_invoice .= " ORDER BY b.tanggal_booking DESC LIMIT 100";
    
    $result_invoice = $conn->query($sql_invoice);
    
    if ($result_invoice) {
        $invoices = $result_invoice;
    } else {
        $invoices = null;
        error_log("Error query invoice: " . $conn->error);
    }
}

// ================= LAPORAN STOK =================
if ($active_tab == 'stok') {
    $jenis_filter = isset($_GET['jenis']) ? $_GET['jenis'] : '';
    
    $where = ["1=1"];
    if (!empty($jenis_filter)) {
        $where[] = "p.jenis = '$jenis_filter'";
    }
    $where_sql = implode(" AND ", $where);
    
    $sql_stok = "SELECT 
                    p.id,
                    p.nama_produk,
                    p.jenis,
                    p.minimal_stok,
                    COALESCE(SUM(ps.stock), 0) as stok_sisa
                 FROM products p
                 LEFT JOIN product_stock ps ON p.id = ps.product_id
                 WHERE $where_sql
                 GROUP BY p.id, p.nama_produk, p.jenis, p.minimal_stok
                 ORDER BY p.nama_produk ASC";
    
    $result_stok = $conn->query($sql_stok);
    
    if (!$result_stok) {
        $result_stok = null;
        error_log("Error SQL stok: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan dan Analisa - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">
    <link rel="stylesheet" href="css/laporan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="vaksinin-logo.png" alt="Vaksinin" class="logo-full">
            <img src="v-logo.png" alt="V" class="logo-icon">
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="javascript:void(0)" 
                class="nav-item has-submenu <?= in_array($current_page, ['products.php','products_pelayanan.php','products_jasa.php']) ? 'active open' : '' ?>" 
                onclick="toggleSubmenu(this)">
                <i class="fas fa-capsules"></i>
                <span>Produk</span>
                <i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu <?= in_array($current_page, ['products.php','products_pelayanan.php','products_jasa.php']) ? 'open' : '' ?>">
                <li><a href="products.php" class="<?= $current_page == 'products.php' ? 'active' : '' ?>">Stok</a></li>
                <li><a href="products_pelayanan.php" class="<?= $current_page == 'products_pelayanan.php' ? 'active' : '' ?>">Pelayanan/Paket</a></li>
                <li><a href="products_jasa.php" class="<?= $current_page == 'products_jasa.php' ? 'active' : '' ?>">Jasa</a></li>
            </ul>
            <a href="patients.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Pasien</span>
            </a>
            <a href="staff.php" class="nav-item">
                <i class="fas fa-user-md"></i>
                <span>Staff</span>
            </a>
            <a href="calendar_setting.php" class="nav-item">
                <i class="fas fa-calendar"></i>
                <span>Kalender</span>
            </a>
            <a href="laporan.php" class="nav-item <?= $current_page == 'laporan.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Laporan</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1>Laporan dan Analisa</h1>
            <div class="header-actions">
                <div class="date-filter">
                    <select class="filter-select bulan" id="bulan">
                        <option value="1" <?= $bulan_filter == 1 ? 'selected' : '' ?>>Januari</option>
                        <option value="2" <?= $bulan_filter == 2 ? 'selected' : '' ?>>Februari</option>
                        <option value="3" <?= $bulan_filter == 3 ? 'selected' : '' ?>>Maret</option>
                        <option value="4" <?= $bulan_filter == 4 ? 'selected' : '' ?>>April</option>
                        <option value="5" <?= $bulan_filter == 5 ? 'selected' : '' ?>>Mei</option>
                        <option value="6" <?= $bulan_filter == 6 ? 'selected' : '' ?>>Juni</option>
                        <option value="7" <?= $bulan_filter == 7 ? 'selected' : '' ?>>Juli</option>
                        <option value="8" <?= $bulan_filter == 8 ? 'selected' : '' ?>>Agustus</option>
                        <option value="9" <?= $bulan_filter == 9 ? 'selected' : '' ?>>September</option>
                        <option value="10" <?= $bulan_filter == 10 ? 'selected' : '' ?>>Oktober</option>
                        <option value="11" <?= $bulan_filter == 11 ? 'selected' : '' ?>>November</option>
                        <option value="12" <?= $bulan_filter == 12 ? 'selected' : '' ?>>Desember</option>
                    </select>
                    
                    <select class="filter-select tahun" id="tahun">
                        <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                            <option value="<?= $i ?>" <?= $tahun_filter == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    
                    <button class="btn-filter-apply" onclick="applyFilter()">
                        <i class="fas fa-filter"></i> Terapkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="report-tabs">
            <a href="?tab=pendapatan" class="report-tab <?= $active_tab == 'pendapatan' ? 'active' : '' ?>">
                Laporan Pendapatan
            </a>
            <a href="?tab=invoice" class="report-tab <?= $active_tab == 'invoice' ? 'active' : '' ?>">
                Laporan Invoice
            </a>
            <a href="?tab=stok" class="report-tab <?= $active_tab == 'stok' ? 'active' : '' ?>">
                Laporan Stok
            </a>
        </div>

        <?php if ($active_tab == 'pendapatan'): ?>
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Pendapatan (Bulan Ini)</div>
                    <div class="stat-value">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Transaksi</div>
                    <div class="stat-value"><?= $total_transaksi ?> Transaksi</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Layanan Terlaris</div>
                    <div class="stat-value"><?= htmlspecialchars($layanan_terlaris_nama) ?></div>
                    <div style="font-size: 14px; color: #64748b; margin-top: 4px;">
                        <?= $layanan_terlaris_jumlah ?> kali dipesan
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="chart-container">
                <div class="chart-header">Tren Pendapatan harian</div>
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        <?php endif; ?>

        <?php if ($active_tab == 'invoice'): ?>
            <div class="report-table-container">
                <div class="filter-bar">
                    <input type="text" 
                           class="search-input" 
                           placeholder="🔍 Cari No. Rm / Nama /" 
                           value="<?= htmlspecialchars($search ?? '') ?>"
                           onchange="window.location.href='?tab=invoice&search='+this.value">
                    <select class="filter-select" onchange="window.location.href='?tab=invoice&status_filter='+this.value">
                        <option value="">Status Pembayaran</option>
                        <option value="lunas" <?= ($_GET['status_filter'] ?? '') == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                        <option value="belum_lunas" <?= ($_GET['status_filter'] ?? '') == 'belum_lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                    </select>
                </div>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>No. Rm</th>
                            <th>Nama Pasien</th>
                            <th>Tanggal</th>
                            <th>Layanan / Paket</th>
                            <th>Total Tagihan</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($invoices && $invoices->num_rows > 0): ?>
                            <?php while ($invoice = $invoices->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($invoice['nomor_antrian'] ?? $invoice['id']) ?></td>
                                    <td><?= htmlspecialchars($invoice['nama_lengkap']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($invoice['tanggal_booking'])) ?></td>
                                    <td><?= htmlspecialchars($invoice['layanan']) ?></td>
                                    <td><?= number_format($invoice['total_tagihan'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php 
                                        // Ambil nilai dengan default jika tidak ada
                                        $payment_status = $invoice['payment_status'] ?? 'unpaid';
                                        $booking_status = $invoice['status'] ?? 'pending';
                                        
                                        // Cek apakah LUNAS (payment_status = 'paid' DAN status = 'completed')
                                        if ($payment_status == 'paid' && $booking_status == 'completed') {
                                            $status_class = 'status-lunas';
                                            $status_text = 'Lunas';
                                        } else {
                                            // Selain itu, semua dianggap BELUM LUNAS
                                            $status_class = 'status-belum-lunas';
                                            $status_text = 'Belum Lunas';
                                        }
                                        ?>
                                        <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                                    </td>
                                    <td>
                                        <button class="btn-icon" onclick="printInvoice(<?= $invoice['id'] ?>)" title="Print Invoice">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <button class="btn-icon" onclick="sendInvoice(<?= $invoice['id'] ?>)" title="Send">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                    Belum ada data invoice
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($active_tab == 'stok'): ?>
            <div class="report-table-container">
                <div class="filter-bar">
                    <select class="filter-select" onchange="window.location.href='?tab=stok&jenis='+this.value">
                        <option value="">Jenis</option>
                        <option value="Vaksin" <?= $jenis_filter == 'Vaksin' ? 'selected' : '' ?>>Vaksin</option>
                        <option value="Vitamin" <?= $jenis_filter == 'Vitamin' ? 'selected' : '' ?>>Vitamin</option>
                        <option value="Obat" <?= $jenis_filter == 'Obat' ? 'selected' : '' ?>>Obat</option>
                    </select>
                </div>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Jenis</th>
                            <th>Stok Sisa</th>
                            <th>Minimal Stok</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_stok && $result_stok->num_rows > 0): ?>
                            <?php while ($stok = $result_stok->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($stok['nama_produk']) ?></td>
                                    <td><?= htmlspecialchars($stok['jenis']) ?></td>
                                    <td><?= $stok['stok_sisa'] ?? 0 ?></td>
                                    <td><?= $stok['minimal_stok'] ?? 10 ?></td>
                                    <td>
                                        <?php 
                                        $stok_sisa = $stok['stok_sisa'] ?? 0;
                                        $minimal = $stok['minimal_stok'] ?? 10;
                                        
                                        if ($stok_sisa <= 0) {
                                            $status_class = 'status-habis';
                                            $status_text = 'Stok Habis';
                                        } elseif ($stok_sisa <= $minimal) {
                                            $status_class = 'status-menipis';
                                            $status_text = 'Stok Menipis';
                                        } else {
                                            $status_class = 'status-tersedia';
                                            $status_text = 'Tersedia';
                                        }
                                        ?>
                                        <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">
                                    Belum ada data stok
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    
    <script src="js/sidebar-toggle.js"></script>
    <script src="js/laporan.js"></script>

    <?php if ($active_tab == 'pendapatan'): ?>
    <script>
        // Render chart saat halaman load
        document.addEventListener('DOMContentLoaded', function() {
            const trendData = <?= json_encode($tren_data) ?>;
            if (typeof renderRevenueChart === 'function') {
                renderRevenueChart(trendData);
            } else {
                console.error('Fungsi renderRevenueChart tidak ditemukan');
                // Coba lagi setelah beberapa saat
                setTimeout(function() {
                    if (typeof renderRevenueChart === 'function') {
                        renderRevenueChart(trendData);
                    } else {
                        console.error('Masih tidak ditemukan');
                    }
                }, 500);
            }
        });
    </script>
    <?php endif; ?>
    
</body>
</html>