<?php
include "config/database.php";
include "content/sidebar.php";

$id = mysqli_real_escape_string($conn, $_GET['id']);
$role = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM roles WHERE id = '$id'"));

// Daftar fitur yang kamu sebutkan tadi
$daftar_fitur = [
    'manage_user'    => 'Setting Akun User',
    'web_interface'  => 'Admin Web Interface',
    'receive_order'  => 'Terima Orderan',
    'edit_calendar'  => 'Edit Kalender',
    'medical_action' => 'Tindakan Medis',
    'cashier'        => 'Bayar / Kasir'
];

// Pecah string privileges dari DB menjadi array agar bisa dicek (checked)
$akses_sekarang = explode(',', $role['privileges'] ?? '');
?>

<section id="header">Edit Hak Akses: <?= $role['role_name'] ?></section>
<section class="main-page">
    <div class="admin-container" style="max-width: 600px; margin: auto;">
        <form action="/proses-update-privileges" method="POST" class="admin-form">
            <input type="hidden" name="role_id" value="<?= $role['id'] ?>">

            <div class="privilege-list" style="background: #f9f9f9; padding: 20px; border-radius: 10px;">
                <?php foreach($daftar_fitur as $key => $label): ?>
                <div style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="fitur[]" value="<?= $key ?>" 
                        id="f_<?= $key ?>" 
                        style="width: 20px; height: 20px; cursor: pointer;"
                        <?= in_array($key, $akses_sekarang) ? 'checked' : '' ?>>
                    
                    <label for="f_<?= $key ?>" style="cursor: pointer; font-weight: 600; font-size: 16px;">
                        <?= $label ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <hr>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-add" style="flex: 2;">Simpan Perubahan Hak Akses</button>
                <button type="button" class="btn-add" onclick="history.back()" style="flex: 1; background: #6c757d;">Batal</button>
            </div>
        </form>
    </div>
</section>