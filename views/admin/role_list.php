<?php
include "config/database.php";
include "content/sidebar.php";

$query = mysqli_query($conn, "SELECT * FROM roles ORDER BY id ASC");
?>

<section id="header">Manajemen Role & Hak Akses</section>
<section class="main-page">
    <div class="admin-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Role</th>
                    <th>Privileges (Akses)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($query)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><strong><?= $row['role_name'] ?></strong></td>
                    <td>
                        <small style="color: #666; font-style: italic;">
                            <?= !empty($row['privileges']) ? str_replace(',', ', ', $row['privileges']) : 'Belum ada akses' ?>
                        </small>
                    </td>
                    <td>
                        <a href="/edit-role-access?id=<?= $row['id'] ?>" class="btn-action" style="background: #1976d2;">
                            <i class="fas fa-lock"></i> Atur Izin
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>