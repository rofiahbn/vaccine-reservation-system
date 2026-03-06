<?php
include "config/database.php";
include "content/auth.php";

// Ambil daftar menu utama (parent_id = 0) untuk pilihan dropdown Induk
$parents = mysqli_query($conn, "SELECT id, title FROM menus WHERE parent_id = 0 ORDER BY order_priority ASC");

include "content/sidebar.php";
?>

<section id="header">Tambah Menu Baru</section>
<section class="main-page">
    <div class="admin-container" style="max-width: 700px; margin: auto;">
        <div class="header-action">
            <h2>Buat Struktur Menu</h2>
            <button class="btn-add" onclick="window.location.href='/web-interface'" style="background: #6c757d;">Kembali</button>
        </div>

        <form action="/proses-tambah-menu" method="POST" class="admin-form">
            <div class="form-group">
                <label>Nama Menu</label>
                <input type="text" name="title" class="form-control" placeholder="Contoh: Vaksin Anak" required>
            </div>

            <div class="form-group">
                <label>Induk Menu (Parent)</label>
                <select name="parent_id" class="form-control">
                    <option value="0">-- Jadikan Menu Utama --</option>
                    <?php while($p = mysqli_fetch_assoc($parents)): ?>
                        <option value="<?= $p['id'] ?>"><?= $p['title'] ?></option>
                    <?php endwhile; ?>
                </select>
                <small style="color: #888;">Pilih jika ingin menu ini menjadi submenu.</small>
            </div>

            <div class="form-group">
                <label>Link / Slug URL</label>
                <input type="text" name="link" class="form-control" placeholder="contoh: vaksin-anak.php atau #" required>
                <small style="color: #888;">Gunakan <b>#</b> jika hanya ingin menjadi dropdown tanpa halaman.</small>
            </div>

            <div class="form-group">
                <label>Urutan Tampil</label>
                <input type="number" name="order_priority" class="form-control" value="1" min="1">
            </div>

            <hr>
            <button type="submit" class="btn-add" style="width: 100%; padding: 12px;">Simpan Menu & Buat Halaman</button>
        </form>
    </div>
</section>

<style>
    .admin-form { display: flex; flex-direction: column; gap: 15px; }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-control { padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
    .form-control:focus { border-color: #f39c12; outline: none; box-shadow: 0 0 5px rgba(243,156,18,0.2); }
    label { font-weight: 600; color: #444; }
</style>