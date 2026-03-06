<?php 
include "config/database.php"; 
include "content/auth.php"; 
// Query untuk mengambil menu beserta informasi apakah sudah ada page-nya atau belum
$sql = "SELECT m.*, p.id as page_id 
        FROM menus m 
        LEFT JOIN pages p ON m.id = p.menu_id 
        ORDER BY m.parent_id ASC, m.order_priority ASC";
$result = mysqli_query($conn, $sql);
?>   

<?php include "content/sidebar.php"; ?>

<section id="header">Tampilan Website</section>
<section class="main-page">
     <div class="admin-container">
    <div class="header-action">
        <h2>Manajemen Menu & Halaman</h2>
        <button class="btn-add" onclick="window.location.href='tambah-menu'">+ Tambah Menu Baru</button>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Judul Menu</th>
                <th>Link / Slug</th>
                <th>Parent (Submenu)</th>
                <th>Urutan</th>
                <th>Status Konten</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
    <td><strong><?= $row['title'] ?></strong></td>
    <td><code style="color: #666;"><?= $row['link'] ?></code></td>
    <td>
        <?php if($row['parent_id'] == 0): ?>
            <span class="badge-main">Main Menu</span>
        <?php else: ?>
            <span style="font-size: 12px; color: #888;">Sub dari ID: <?= $row['parent_id'] ?></span>
        <?php endif; ?>
    </td>
    <td style="text-align: center;"><?= $row['order_priority'] ?></td>
    <td style="text-align: center;">
        <?php if($row['link'] == "#"): ?>
            <span class="status-badge status-dropdown">N/A (Dropdown)</span>
        <?php elseif($row['page_id']): ?>
            <span class="status-badge status-tersedia">Tersedia</span>
        <?php else: ?>
            <span class="status-badge status-kosong">Belum Ada Isi</span>
        <?php endif; ?>
    </td>
    <td style="text-align: left;">
    <div class="action-links">
        <a href="edit-menu?id=<?= $row['id'] ?>" class="btn-action btn-edit-menu">
            <i class="fas fa-bars"></i> Menu
        </a>
        
        <?php if($row['link'] != "#"): ?>
            <a href="edit-page?menu_id=<?= $row['id'] ?>" class="btn-action btn-edit-page">
                <i class="fas fa-file-alt"></i> Isi Page
            </a>
        <?php endif; ?>

        <a href="javascript:void(0)" 
           class="btn-action btn-delete-item" 
           onclick="konfirmasiHapusMenu(<?= $row['id'] ?>, '<?= addslashes($row['title']) ?>')">
            <i class="fas fa-trash"></i>
        </a>
    </div>
</td>
</tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
 


</section>
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function konfirmasiHapusMenu(id, title) {
    Swal.fire({
        title: 'Hapus Menu?',
        text: "Anda akan menghapus '" + title + "'. Konten halaman terkait juga akan hilang!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c', // Warna merah
        cancelButtonColor: '#bdc3c7',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Proses hapus menggunakan AJAX Fetch
            fetch('/delete_menu?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload(); // Refresh tabel setelah berhasil
                    });
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Terjadi kesalahan sistem atau koneksi.', 'error');
            });
        }
    });
}
</script>


</body>
</html>
