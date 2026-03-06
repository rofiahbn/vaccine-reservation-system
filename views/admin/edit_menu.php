<?php
include "config/database.php";
include "content/auth.php";

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
$query = mysqli_query($conn, "SELECT * FROM menus WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='/web-interface';</script>";
    exit;
}

// Ambil daftar menu utama untuk pilihan Parent
$parents = mysqli_query($conn, "SELECT id, title FROM menus WHERE parent_id = 0 AND id != '$id' ORDER BY order_priority ASC");

include "content/sidebar.php";
?>

<section id="header">Edit Struktur Menu</section>
<section class="main-page">
    <div class="admin-container" style="max-width: 700px; margin: auto;">
        <div class="header-action">
            <h2>Perbarui Menu: <?= $data['title'] ?></h2>
            <button class="btn-add" onclick="window.location.href='/web-interface'" style="background: #6c757d;">Batal</button>
        </div>

        <form action="/proses-update-menu" method="POST" class="admin-form">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">
            
            <div class="form-group">
                <label>Nama Menu</label>
                <input type="text" name="title" class="form-control" value="<?= $data['title'] ?>" required>
            </div>

            <div class="form-group">
                <label>Induk Menu (Parent)</label>
                <select name="parent_id" class="form-control">
                    <option value="0" <?= $data['parent_id'] == 0 ? 'selected' : '' ?>>-- Menu Utama --</option>
                    <?php while($p = mysqli_fetch_assoc($parents)): ?>
                        <option value="<?= $p['id'] ?>" <?= $data['parent_id'] == $p['id'] ? 'selected' : '' ?>>
                            <?= $p['title'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Link / Slug URL</label>
                <input type="text" name="link" class="form-control" value="<?= $data['link'] ?>" required>
            </div>

            <div class="form-group">
                <label>Urutan Tampil</label>
                <input type="number" name="order_priority" class="form-control" value="<?= $data['order_priority'] ?>">
            </div>

            <hr>
            <button type="submit" class="btn-add" style="width: 100%; padding: 12px; background: #1976d2;">Update Menu</button>
        </form>
    </div>
</section>

<style>
    .admin-form { display: flex; flex-direction: column; gap: 15px; }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-control { padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
    label { font-weight: 600; color: #444; }
</style>