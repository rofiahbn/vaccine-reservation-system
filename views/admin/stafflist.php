<?php 
include "config/database.php";  
include "content/auth.php";

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$bulan  = isset($_GET['bulan']) ? $_GET['bulan'] : date('n'); // format 1-12
$tahun  = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Query Filter (Contoh: Mengambil user dengan jabatan 'Dokter' atau role tertentu)
$sql = "SELECT * FROM staff  WHERE nama_lengkap  LIKE '%$search%'";
// Tambahkan filter lain jika diperlukan, misal: AND status = 1
$result = mysqli_query($conn, $sql);
?>  

<?php 
include "content/sidebar.php"; 
include "content/check_manage_staff.php";
?>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<section id="header">Staff List

   <a href="admin-addpegawai"> <div class="add-btn">Tambah Pegawai</div></a>
</section>
<section class="content-page">
     <form method="GET" action="" class="filter-wrapper">
    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" name="search" placeholder="Nama" value="<?= htmlspecialchars($search) ?>">
    </div>
    
    <div class="dropdown-container">
        <select name="bulan" onchange="this.form.submit()">
            <?php
            $daftar_bulan = [
                1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 
                5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus", 
                9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
            ];
            foreach ($daftar_bulan as $num => $nama_bulan) {
                $selected = ($num == $bulan) ? 'selected' : '';
                echo "<option value='$num' $selected>$nama_bulan</option>";
            }
            ?>
        </select>

        <select name="tahun" onchange="this.form.submit()">
            <option value="">Tahun</option>
            <?php 
            $thn_skrg = date('Y');
            for($i = $thn_skrg; $i >= $thn_skrg-5; $i--): ?>
                <option value="<?= $i ?>" <?= ($tahun == $i) ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
    </div>
</form>

<div class="table-container">
    <table class="staff-table">
        <thead>
            <tr>
                <th>Nama Pegawai</th>
                <th>NIP</th>
                <th>Jabatan</th>
                <th>Total Pasien dilayani</th>
                <th>Total Jam Kerja</th>
                <th>Total Gaji</th>
                <th>Aksi</th>
            </tr>
        </thead>
       <tbody>
<?php if(mysqli_num_rows($result) > 0): ?>
    <?php while($row = mysqli_fetch_assoc($result)): ?>

    <?php
        // Dummy total pasien (sementara)
        $total_pasien = 26;

        // Hitung total gaji
        $total_gaji = $row['gaji_pokok'] + 
                      ($row['fee_per_pasien'] * $total_pasien);
    ?>

    <tr>
        <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= ucfirst($row['role']) ?></td>
        <td><?= $total_pasien ?></td>
        <td>-</td>
        <td>Rp <?= number_format($total_gaji, 0, ',', '.') ?></td>

        <td>
            <div class="action-btns">
                
                    
                    <a href="edit-staff?id=<?= $row['id'] ?>" class="btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    
                    <button type="button" 
                            class="btn-hapus" 
                            onclick="hapusPegawai(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_lengkap']) ?>')"
                            style="border:none; cursor:pointer;">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
 
            </div>
        </td>
    </tr>

    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="7" style="text-align:center;">Data tidak ditemukan</td></tr>
<?php endif; ?>
</tbody>
    </table>
</div>


</section>
 <script>
function hapusPegawai(id, nama) {
    Swal.fire({
        title: 'Hapus Pegawai?',
        text: "Anda akan menghapus " + nama + ". Data yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f39c12', // Warna orange tema kamu
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Proses Hapus via Fetch AJAX
            fetch('proses_hapus_staff?id=' + id)
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => {
                        location.reload(); // Refresh tabel
                    });
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            });
        }
    })
}
</script>


</body>
</html>
