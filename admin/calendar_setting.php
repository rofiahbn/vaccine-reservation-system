<?php
session_start();
include "../config.php";

// Cek login admin bisa dilakukan di config.php

$current_page = 'calendar_setting.php';

// Ambil semua jadwal klinik
$query_jadwal = "SELECT * FROM jadwal_klinik ORDER BY hari_week ASC";
$result_jadwal = mysqli_query($conn, $query_jadwal);

// Ambil semua jadwal libur
$query_libur = "SELECT * FROM jadwal_libur ORDER BY tanggal ASC";
$result_libur = mysqli_query($conn, $query_libur);

$hari_names = [
    1 => 'Minggu',
    2 => 'Senin', 
    3 => 'Selasa',
    4 => 'Rabu',
    5 => 'Kamis',
    6 => 'Jumat',
    7 => 'Sabtu'
];

$jenis_libur = ['nasional', 'khusus', 'minggu'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                class="nav-item has-submenu 
                <?= in_array($current_page, ['products.php','products_pelayanan.php']) ? 'active open' : '' ?>" 
                onclick="toggleSubmenu(this)">
                <i class="fas fa-capsules"></i>
                <span>Produk</span>
                <i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu <?= in_array($current_page, ['products.php','products_pelayanan.php']) ? 'open' : '' ?>">
                <li>
                    <a href="products.php" 
                    class="<?= $current_page == 'products.php' ? 'active' : '' ?>">
                    Stok
                    </a>
                </li>
                <li>
                    <a href="products_pelayanan.php" 
                    class="<?= $current_page == 'products_pelayanan.php' ? 'active' : '' ?>">
                    Pelayanan/Paket
                    </a>
                </li>
            </ul>
            <a href="patients.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Pasien</span>
            </a>
            <a href="calendar_setting.php" class="nav-item <?= $current_page == 'calendar_setting.php' ? 'active' : '' ?>">
                <i class="fas fa-calendar"></i>
                <span>Kalender</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="#" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>
        <div class="calendar-container">
            <h1 class="page-title">Kalender</h1>

                <!-- Jadwal Khusus -->
                <form method="POST" action="save_jadwal_khusus.php">
                    <div class="section-card">
                        <h2 class="section-header">Jadwal Khusus</h2>
                        <p style="color: #6c757d; margin-bottom: 20px; font-size: 14px;">
                            Atur jam operasional untuk rentang tanggal tertentu (akan override jadwal rutin)
                        </p>
                        
                        <div id="jadwalKhususContainer">
                            <?php 
                            // Ambil jadwal khusus dari database (group by tanggal_mulai, tanggal_selesai, jam_buka, jam_tutup)
                            $query_khusus = "
                                SELECT 
                                    MIN(id) AS id,
                                    tanggal_mulai,
                                    tanggal_selesai,
                                    jam_buka,
                                    jam_tutup,
                                    keterangan,
                                    status
                                FROM jadwal_khusus
                                GROUP BY 
                                    tanggal_mulai,
                                    tanggal_selesai,
                                    jam_buka,
                                    jam_tutup,
                                    keterangan,
                                    status
                                ORDER BY tanggal_mulai ASC
                                ";
                            $result_khusus = mysqli_query($conn, $query_khusus);
                            
                            $khusus_array = [];
                            if ($result_khusus) {
                                while ($row = mysqli_fetch_assoc($result_khusus)) {
                                    $khusus_array[] = $row;
                                }
                            }
                            
                            if (empty($khusus_array)) {
                                // Default 1 row
                            ?>
                            <div class="khusus-row" data-khusus-row="1">
                                <div class="row-number">1</div>
                                
                                <div class="khusus-input-wrapper">
                                    <div class="input-group">
                                        <span class="input-label">Tanggal Mulai</span>
                                        <input type="date" class="date-input" name="khusus[1][tanggal_mulai]" required>
                                    </div>

                                    <div class="input-group">
                                        <span class="input-label">Tanggal Selesai</span>
                                        <input type="date" class="date-input" name="khusus[1][tanggal_selesai]" required>
                                    </div>
                                    
                                    <div class="input-group">
                                        <span class="input-label">Jam Buka</span>
                                        <input type="time" class="time-input" name="khusus[1][jam_buka]" value="09:00" required>
                                    </div>

                                    <div class="input-group">
                                        <span class="input-label">Jam Tutup</span>
                                        <input type="time" class="time-input" name="khusus[1][jam_tutup]" value="17:00" required>
                                    </div>

                                    <div class="input-group" style="flex: 1;">
                                        <span class="input-label">Keterangan (Opsional)</span>
                                        <input type="text" class="text-input" name="khusus[1][keterangan]" placeholder="Cth: Libur Lebaran - Buka Setengah Hari">
                                    </div>

                                    <div class="input-group">
                                        <span class="input-label">Status</span>
                                        <select class="select-input" name="khusus[1][status]" required>
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn-delete-row" onclick="deleteKhususRow(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                            </div>
                            <?php 
                            } else {
                                $khusus_num = 1;
                                foreach ($khusus_array as $khusus) {
                            ?>
                            <div class="khusus-row" data-khusus-row="<?= $khusus_num ?>">
                                <div class="row-number"><?= $khusus_num ?></div>
                                
                                <div class="khusus-input-wrapper">
                                    <div class="input-group">
                                        <span class="input-label">Tanggal Mulai</span>
                                        <input type="date" class="date-input" name="khusus[<?= $khusus_num ?>][tanggal_mulai]" value="<?= $khusus['tanggal_mulai'] ?>" required>
                                    </div>

                                    <div class="input-group">
                                        <span class="input-label">Tanggal Selesai</span>
                                        <input type="date" class="date-input" name="khusus[<?= $khusus_num ?>][tanggal_selesai]" value="<?= $khusus['tanggal_selesai'] ?>" required>
                                    </div>
                                    
                                    <div class="input-group">
                                        <span class="input-label">Jam Buka</span>
                                        <input type="time" class="time-input" name="khusus[<?= $khusus_num ?>][jam_buka]" value="<?= substr($khusus['jam_buka'], 0, 5) ?>" required>
                                    </div>

                                    <div class="input-group">
                                        <span class="input-label">Jam Tutup</span>
                                        <input type="time" class="time-input" name="khusus[<?= $khusus_num ?>][jam_tutup]" value="<?= substr($khusus['jam_tutup'], 0, 5) ?>" required>
                                    </div>

                                    <div class="input-group" style="flex: 1;">
                                        <span class="input-label">Keterangan (Opsional)</span>
                                        <input type="text" class="text-input" name="khusus[<?= $khusus_num ?>][keterangan]" value="<?= htmlspecialchars($khusus['keterangan']) ?>" placeholder="Cth: Libur Lebaran - Buka Setengah Hari">
                                    </div>

                                    <div class="input-group">
                                        <span class="input-label">Status</span>
                                        <select class="select-input" name="khusus[<?= $khusus_num ?>][status]" required>
                                            <option value="buka" <?= $khusus['status'] == 'buka' ? 'selected' : '' ?>>Buka</option>
                                            <option value="tutup" <?= $khusus['status'] == 'tutup' ? 'selected' : '' ?>>Tutup</option>
                                        </select>
                                    </div>

                                    <button type="button" class="btn-delete-row" onclick="deleteKhususRow(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>                              
                                
                                <input type="hidden" name="khusus[<?= $khusus_num ?>][id]" value="<?= $khusus['id'] ?>">
                            </div>
                            <?php 
                                    $khusus_num++;
                                }
                            }
                            ?>
                        </div>

                        <button type="button" class="btn-add-jadwal" onclick="addKhususRow()">
                            <i class="fas fa-plus"></i> Tambah Jadwal Khusus
                        </button>

                        <div class="section-actions">
                            <button type="button" class="btn-cancel" onclick="location.reload()">
                                Batalkan
                            </button>

                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>

                    </div>
                </form>

                <!-- Jadwal Klinik -->
                <form method="POST" action="save_jadwal_klinik.php">
                    <div class="section-card">
                        <h2 class="section-header">Jadwal Klinik</h2>
                        
                        <div id="jadwalKlinikContainer">
                            <?php 
                            $jadwal_array = [];
                            while ($row = mysqli_fetch_assoc($result_jadwal)) {
                                $jadwal_array[] = $row;
                            }
                            
                            if (empty($jadwal_array)) {
                                // Default 2 rows
                                for ($i = 1; $i <= 2; $i++) {
                            ?>
                            <div class="jadwal-row" data-row="<?= $i ?>">
                                <div class="row-number"><?= $i ?></div>
                                
                                <div class="day-selector">
                                    <button type="button" class="day-btn" data-day="2" data-row="<?= $i ?>">Senin</button>
                                    <button type="button" class="day-btn" data-day="3" data-row="<?= $i ?>">Selasa</button>
                                    <button type="button" class="day-btn" data-day="4" data-row="<?= $i ?>">Rabu</button>
                                    <button type="button" class="day-btn" data-day="5" data-row="<?= $i ?>">Kamis</button>
                                    <button type="button" class="day-btn" data-day="6" data-row="<?= $i ?>">Jumat</button>
                                    <button type="button" class="day-btn" data-day="7" data-row="<?= $i ?>">Sabtu</button>
                                    <button type="button" class="day-btn" data-day="1" data-row="<?= $i ?>">Minggu</button>
                                </div>

                                <div class="time-group">
                                    <div class="time-input-wrapper">
                                        <span class="time-label">Jam Buka</span>
                                        <input type="time" class="time-input" name="jadwal[<?= $i ?>][jam_buka]" value="09:00" required>
                                    </div>
                                    <div class="time-input-wrapper">
                                        <span class="time-label">Jam Tutup</span>
                                        <input type="time" class="time-input" name="jadwal[<?= $i ?>][jam_tutup]" value="17:00" required>
                                    </div>
                                    <button type="button" class="btn-delete-row" onclick="deleteJadwalRow(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <input type="hidden" name="jadwal[<?= $i ?>][hari_week]" class="selected-days" value="">
                            </div>
                            <?php 
                                }
                            } else {
                                // Load existing data
                                $row_num = 1;
                                foreach ($jadwal_array as $jadwal) {
                            ?>
                            <div class="jadwal-row" data-row="<?= $row_num ?>">
                                <div class="row-number"><?= $row_num ?></div>
                                
                                <div class="day-selector">
                                    <button type="button" class="day-btn <?= $jadwal['hari_week'] == 2 ? 'active' : '' ?>" data-day="2" data-row="<?= $row_num ?>">Senin</button>
                                    <button type="button" class="day-btn <?= $jadwal['hari_week'] == 3 ? 'active' : '' ?>" data-day="3" data-row="<?= $row_num ?>">Selasa</button>
                                    <button type="button" class="day-btn <?= $jadwal['hari_week'] == 4 ? 'active' : '' ?>" data-day="4" data-row="<?= $row_num ?>">Rabu</button>
                                    <button type="button" class="day-btn <?= $jadwal['hari_week'] == 5 ? 'active' : '' ?>" data-day="5" data-row="<?= $row_num ?>">Kamis</button>
                                    <button type="button" class="day-btn <?= $jadwal['hari_week'] == 6 ? 'active' : '' ?>" data-day="6" data-row="<?= $row_num ?>">Jumat</button>
                                    <button type="button" class="day-btn <?= $jadwal['hari_week'] == 7 ? 'active' : '' ?>" data-day="7" data-row="<?= $row_num ?>">Sabtu</button>
                                    <button type="button" class="day-btn <?= $jadwal['hari_week'] == 1 ? 'active' : '' ?>" data-day="1" data-row="<?= $row_num ?>">Minggu</button>
                                </div>

                                <div class="time-group">
                                    <div class="time-input-wrapper">
                                        <span class="time-label">Jam Buka</span>
                                        <input type="time" class="time-input" name="jadwal[<?= $row_num ?>][jam_buka]" value="<?= substr($jadwal['jam_buka'], 0, 5) ?>" required>
                                    </div>
                                    <div class="time-input-wrapper">
                                        <span class="time-label">Jam Tutup</span>
                                        <input type="time" class="time-input" name="jadwal[<?= $row_num ?>][jam_tutup]" value="<?= substr($jadwal['jam_tutup'], 0, 5) ?>" required>
                                    </div>
                                </div>

                                <button type="button" class="btn-delete-row" onclick="deleteJadwalRow(this)">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <input type="hidden" name="jadwal[<?= $row_num ?>][hari_week]" class="selected-days" value="<?= $jadwal['hari_week'] ?>">
                                <input type="hidden" name="jadwal[<?= $row_num ?>][id]" value="<?= $jadwal['id'] ?>">
                            </div>
                            <?php 
                                    $row_num++;
                                }
                            }
                            ?>
                        </div>

                        <button type="button" class="btn-add-jadwal" onclick="addJadwalRow()">
                            <i class="fas fa-plus"></i> Tambah Jadwal
                        </button>

                        <div class="section-actions">
                            <button type="button" class="btn-cancel" onclick="location.reload()">
                                Batalkan
                            </button>

                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>

                    </div>
                </form>

                <!-- Jadwal Libur -->
                <form method="POST" action="save_jadwal_libur.php">
                    <div class="section-card">
                        <h2 class="section-header">Jadwal Libur</h2>
                        
                        <div id="jadwalLiburContainer">
                            <?php 
                            $libur_array = [];
                            mysqli_data_seek($result_libur, 0);
                            while ($row = mysqli_fetch_assoc($result_libur)) {
                                $libur_array[] = $row;
                            }
                            
                            if (empty($libur_array)) {
                                // Default 1 row
                            ?>
                            <div class="libur-row" data-libur-row="1">
                                <div class="row-number">1</div>
                                
                                <div class="date-input-wrapper">
                                    <div class="input-group">
                                        <span class="input-label">Mulai</span>
                                        <input type="date" class="date-input" name="libur[1][mulai]" required>
                                    </div>
                                    
                                    <div class="input-group">
                                        <span class="input-label">Selesai</span>
                                        <input type="date" class="date-input" name="libur[1][selesai]" required>
                                    </div>

                                    <div class="input-group" style="flex: 1;">
                                        <span class="input-label">Keterangan</span>
                                        <input type="text" class="text-input" name="libur[1][keterangan]" placeholder="Cth: Tahun Baru Imlek" required>
                                    </div>

                                    <div class="input-group">
                                        <span class="input-label">Jenis</span>
                                        <select class="select-input" name="libur[1][jenis]" required>
                                            <option value="nasional">Nasional</option>
                                            <option value="khusus">Khusus</option>
                                            <option value="minggu">Minggu</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="button" class="btn-delete-row" onclick="deleteLiburRow(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <?php 
                            } else {
                                $libur_num = 1;
                                foreach ($libur_array as $libur) {
                            ?>
                            <div class="libur-row" data-libur-row="<?= $libur_num ?>">
                                <div class="row-number"><?= $libur_num ?></div>
                                
                                <div class="date-input-wrapper">
                                    <div class="input-group">
                                        <span class="input-label">Mulai</span>
                                        <input type="date" class="date-input" name="libur[<?= $libur_num ?>][mulai]" value="<?= $libur['tanggal'] ?>" required>
                                    </div>
                                    
                                    <div class="input-group">
                                        <span class="input-label">Selesai</span>
                                        <input type="date" class="date-input" name="libur[<?= $libur_num ?>][selesai]" value="<?= $libur['tanggal'] ?>" required>
                                    </div>

                                    <div class="input-group" style="flex: 1;">
                                        <span class="input-label">Keterangan</span>
                                        <input type="text" class="text-input" name="libur[<?= $libur_num ?>][keterangan]" value="<?= htmlspecialchars($libur['keterangan']) ?>" required>
                                    </div>

                                    <div class="input-group">
                                        <span class="input-label">Jenis</span>
                                        <select class="select-input" name="libur[<?= $libur_num ?>][jenis]" required>
                                            <option value="nasional" <?= $libur['jenis'] == 'nasional' ? 'selected' : '' ?>>Nasional</option>
                                            <option value="khusus" <?= $libur['jenis'] == 'khusus' ? 'selected' : '' ?>>Khusus</option>
                                            <option value="minggu" <?= $libur['jenis'] == 'minggu' ? 'selected' : '' ?>>Minggu</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="button" class="btn-delete-row" onclick="deleteLiburRow(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                
                                <input type="hidden" name="libur[<?= $libur_num ?>][id]" value="<?= $libur['id'] ?>">
                            </div>
                            <?php 
                                    $libur_num++;
                                }
                            }
                            ?>
                        </div>

                        <button type="button" class="btn-add-jadwal" onclick="addLiburRow()">
                            <i class="fas fa-plus"></i> Tambah Jadwal
                        </button>

                        <div class="section-actions">
                            <button type="button" class="btn-cancel" onclick="location.reload()">
                                Batalkan
                            </button>

                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </div>
                </form>
        </div>
    </div>

    <script src="js/sidebar-toggle.js"></script>
    <script>
        let jadwalRowCount = document.querySelectorAll('.jadwal-row').length;
        let khususRowCount = document.querySelectorAll('.khusus-row').length;
        let liburRowCount = document.querySelectorAll('.libur-row').length;

        // Day button toggle
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('day-btn')) {
                e.preventDefault();
                const btn = e.target;
                const row = btn.dataset.row;
                
                // Toggle active
                btn.classList.toggle('active');
                
                // Update hidden input
                updateSelectedDays(row);
            }
        });

        function updateSelectedDays(row) {
            const activeBtns = document.querySelectorAll(`.day-btn[data-row="${row}"].active`);
            const days = Array.from(activeBtns).map(btn => btn.dataset.day);
            const input = document.querySelector(`.jadwal-row[data-row="${row}"] .selected-days`);
            if (input) {
                input.value = days.join(',');
            }
        }

        function addJadwalRow() {
            jadwalRowCount++;
            const container = document.getElementById('jadwalKlinikContainer');
            
            const newRow = document.createElement('div');
            newRow.className = 'jadwal-row';
            newRow.dataset.row = jadwalRowCount;
            
            newRow.innerHTML = `
                <div class="row-number">${jadwalRowCount}</div>
                
                <div class="day-selector">
                    <button type="button" class="day-btn" data-day="2" data-row="${jadwalRowCount}">Senin</button>
                    <button type="button" class="day-btn" data-day="3" data-row="${jadwalRowCount}">Selasa</button>
                    <button type="button" class="day-btn" data-day="4" data-row="${jadwalRowCount}">Rabu</button>
                    <button type="button" class="day-btn" data-day="5" data-row="${jadwalRowCount}">Kamis</button>
                    <button type="button" class="day-btn" data-day="6" data-row="${jadwalRowCount}">Jumat</button>
                    <button type="button" class="day-btn" data-day="7" data-row="${jadwalRowCount}">Sabtu</button>
                    <button type="button" class="day-btn" data-day="1" data-row="${jadwalRowCount}">Minggu</button>
                </div>

                <div class="time-group">
                    <div class="time-input-wrapper">
                        <span class="time-label">Jam Buka</span>
                        <input type="time" class="time-input" name="jadwal[${jadwalRowCount}][jam_buka]" value="09:00" required>
                    </div>
                    <div class="time-input-wrapper">
                        <span class="time-label">Jam Tutup</span>
                        <input type="time" class="time-input" name="jadwal[${jadwalRowCount}][jam_tutup]" value="17:00" required>
                    </div>
                </div>

                <button type="button" class="btn-delete-row" onclick="deleteJadwalRow(this)">
                    <i class="fas fa-trash"></i>
                </button>

                <input type="hidden" name="jadwal[${jadwalRowCount}][hari_week]" class="selected-days" value="">
            `;
            
            container.appendChild(newRow);
            renumberJadwalRows();
        }

        function deleteJadwalRow(btn) {
            if (document.querySelectorAll('.jadwal-row').length <= 1) {
                alert('Minimal harus ada 1 jadwal klinik!');
                return;
            }
            
            if (confirm('Yakin hapus jadwal ini?')) {
                btn.closest('.jadwal-row').remove();
                renumberJadwalRows();
            }
        }

        function renumberJadwalRows() {
            const rows = document.querySelectorAll('.jadwal-row');
            rows.forEach((row, index) => {
                const num = index + 1;
                row.dataset.row = num;
                row.querySelector('.row-number').textContent = num;
                
                // Update input names
                const inputs = row.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (input.name) {
                        input.name = input.name.replace(/\[\d+\]/, `[${num}]`);
                    }
                });
                
                // Update day buttons
                const dayBtns = row.querySelectorAll('.day-btn');
                dayBtns.forEach(btn => {
                    btn.dataset.row = num;
                });
            });
            jadwalRowCount = rows.length;
        }

        function addLiburRow() {
            liburRowCount++;
            const container = document.getElementById('jadwalLiburContainer');
            
            const newRow = document.createElement('div');
            newRow.className = 'libur-row';
            newRow.dataset.liburRow = liburRowCount;
            
            newRow.innerHTML = `
                <div class="row-number">${liburRowCount}</div>
                
                <div class="date-input-wrapper">
                    <div class="input-group">
                        <span class="input-label">Mulai</span>
                        <input type="date" class="date-input" name="libur[${liburRowCount}][mulai]" required>
                    </div>
                    
                    <div class="input-group">
                        <span class="input-label">Selesai</span>
                        <input type="date" class="date-input" name="libur[${liburRowCount}][selesai]" required>
                    </div>

                    <div class="input-group" style="flex: 1;">
                        <span class="input-label">Keterangan</span>
                        <input type="text" class="text-input" name="libur[${liburRowCount}][keterangan]" placeholder="Cth: Tahun Baru Imlek" required>
                    </div>

                    <div class="input-group">
                        <span class="input-label">Jenis</span>
                        <select class="select-input" name="libur[${liburRowCount}][jenis]" required>
                            <option value="nasional">Nasional</option>
                            <option value="khusus">Khusus</option>
                            <option value="minggu">Minggu</option>
                        </select>
                    </div>
                </div>

                <button type="button" class="btn-delete-row" onclick="deleteLiburRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            
            container.appendChild(newRow);
            renumberLiburRows();
        }

        function deleteLiburRow(btn) {
            if (confirm('Yakin hapus jadwal libur ini?')) {
                btn.closest('.libur-row').remove();
                renumberLiburRows();
            }
        }

        function renumberLiburRows() {
            const rows = document.querySelectorAll('.libur-row');
            rows.forEach((row, index) => {
                const num = index + 1;
                row.dataset.liburRow = num;
                row.querySelector('.row-number').textContent = num;
                
                // Update input names
                const inputs = row.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (input.name) {
                        input.name = input.name.replace(/\[\d+\]/, `[${num}]`);
                    }
                });
            });
            liburRowCount = rows.length;
        }

        function addKhususRow() {
            khususRowCount++;
            const container = document.getElementById('jadwalKhususContainer');
            
            const newRow = document.createElement('div');
            newRow.className = 'khusus-row';
            newRow.dataset.khususRow = khususRowCount;
            
            newRow.innerHTML = `
                <div class="row-number">${khususRowCount}</div>
                
                <div class="khusus-input-wrapper">
                    <div class="input-group">
                        <span class="input-label">Tanggal Mulai</span>
                        <input type="date" class="date-input" name="khusus[${khususRowCount}][tanggal_mulai]" required>
                    </div>

                    <div class="input-group">
                        <span class="input-label">Tanggal Selesai</span>
                        <input type="date" class="date-input" name="khusus[${khususRowCount}][tanggal_selesai]" required>
                    </div>
                    
                    <div class="input-group">
                        <span class="input-label">Jam Buka</span>
                        <input type="time" class="time-input" name="khusus[${khususRowCount}][jam_buka]" value="09:00" required>
                    </div>

                    <div class="input-group">
                        <span class="input-label">Jam Tutup</span>
                        <input type="time" class="time-input" name="khusus[${khususRowCount}][jam_tutup]" value="17:00" required>
                    </div>

                    <div class="input-group" style="flex: 1;">
                        <span class="input-label">Keterangan (Opsional)</span>
                        <input type="text" class="text-input" name="khusus[${khususRowCount}][keterangan]" placeholder="Cth: Libur Lebaran - Buka Setengah Hari">
                    </div>

                    <div class="input-group">
                        <span class="input-label">Status</span>
                        <select class="select-input" name="khusus[${khususRowCount}][status]" required>
                            <option value="buka">Buka</option>
                            <option value="tutup">Tutup</option>
                        </select>
                    </div>
                </div>

                <button type="button" class="btn-delete-row" onclick="deleteKhususRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            
            container.appendChild(newRow);
            renumberKhususRows();
        }

        function deleteKhususRow(btn) {
            if (confirm('Yakin hapus jadwal khusus ini?')) {
                btn.closest('.khusus-row').remove();
                renumberKhususRows();
            }
        }

        function renumberKhususRows() {
            const rows = document.querySelectorAll('.khusus-row');
            rows.forEach((row, index) => {
                const num = index + 1;
                row.dataset.khususRow = num;
                row.querySelector('.row-number').textContent = num;
                
                // Update input names
                const inputs = row.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (input.name) {
                        input.name = input.name.replace(/\[\d+\]/, `[${num}]`);
                    }
                });
            });
            khususRowCount = rows.length;
        }

        // Form validation

        document.querySelector('form[action="save_jadwal_khusus.php"]')
        .addEventListener('submit', function(e){

            const rows = document.querySelectorAll('.khusus-row');

            for(let row of rows){

                const mulai = row.querySelector('[name*="[tanggal_mulai]"]').value;
                const selesai = row.querySelector('[name*="[tanggal_selesai]"]').value;
                const buka = row.querySelector('[name*="[jam_buka]"]').value;
                const tutup = row.querySelector('[name*="[jam_tutup]"]').value;
                const status = row.querySelector('[name*="[status]"]').value;

                if(mulai > selesai){
                    alert('Tanggal mulai tidak boleh lebih besar dari selesai');
                    e.preventDefault();
                    return;
                }

                if(status === 'buka'){
                    if(buka >= tutup){
                    alert('Jam buka harus lebih kecil dari jam tutup');
                    e.preventDefault();
                    return;
                    }
                }

            }

        });

        document.querySelector('form[action="save_jadwal_klinik.php"]')
        .addEventListener('submit', function(e){

        const rows = document.querySelectorAll('.jadwal-row');
        let error = false;

        rows.forEach(row=>{
            const days = row.querySelector('.selected-days').value;
            if(!days){
                error = true;
            }
        });

        if(error){
            e.preventDefault();
            alert('Pilih minimal 1 hari!');
        }

        });

        document.querySelector('form[action="save_jadwal_libur.php"]')
        .addEventListener('submit', function(e){

            const rows = document.querySelectorAll('.libur-row');

            for(let row of rows){

                const mulai = row.querySelector('[name*="[mulai]"]').value;
                const selesai = row.querySelector('[name*="[selesai]"]').value;
                const ket = row.querySelector('[name*="[keterangan]"]').value;

                if(mulai > selesai){
                    alert('Tanggal libur tidak valid');
                    e.preventDefault();
                    return;
                }

                if(!ket.trim()){
                    alert('Keterangan libur wajib diisi');
                    e.preventDefault();
                    return;
                }

            }

        });


    </script>
</body>
</html>