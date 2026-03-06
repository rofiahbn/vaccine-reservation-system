<?php
include "config/database.php";
include "content/auth.php";

$menu_id = isset($_GET['menu_id']) ? mysqli_real_escape_string($conn, $_GET['menu_id']) : '';
$query = mysqli_query($conn, "SELECT p.*, m.title as menu_name FROM pages p JOIN menus m ON p.menu_id = m.id WHERE p.menu_id = '$menu_id'");
$data = mysqli_fetch_assoc($query);

include "content/sidebar.php";
?>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<section id="header">Edit Konten Halaman</section>
<section class="main-page">
    <div class="admin-container">
        <div class="header-action">
            <h2>Halaman: <?= $data['menu_name'] ?></h2>
            <button class="btn-add" onclick="window.location.href='/web-interface'" style="background: #6c757d;">Kembali</button>
        </div>

        <form action="/proses-update-page" method="POST">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Judul Besar Halaman (H1)</label>
                <input type="text" name="page_title" class="form-control" value="<?= $data['page_title'] ?>" required>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label>Isi Konten</label>
                <textarea id="myEditor" name="content"><?= $data['content'] ?></textarea>
            </div>

            <button type="submit" class="btn-add" style="width: 100%; background: #f39c12;">Simpan Perubahan Halaman</button>
        </form>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    tinymce.init({
        selector: '#myEditor',
        license_key: 'gpl', // Tambahkan ini untuk versi Open Source
        height: 500,
        plugins: 'image link table lists code media wordcount',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | image link table | code',
        
        /* Handler Upload Gambar */
        images_upload_url: '/upload-gambar-page',
        automatic_uploads: true,
        
        /* Agar gambar yang diupload jalurnya benar */
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true
    });
</script>