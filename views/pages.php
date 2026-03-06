<?php 
// Source - https://stackoverflow.com/q/1053424
// Posted by Abs, modified by community. See post 'Timeline' for change history
// Retrieved 2026-02-18, License - CC BY-SA 4.0

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "content/header.php";

$slug = isset($_GET['p']) ? mysqli_real_escape_string($conn, $_GET['p']) : 'home';

// Query JOIN ke menus untuk mendapatkan kategori/title menu asal
$query = mysqli_query($conn, "SELECT p.*, m.title as category_name FROM pages p 
                              JOIN menus m ON p.menu_id = m.id 
                              WHERE p.slug = '$slug'");
$page = mysqli_fetch_assoc($query);
?>

<div class="article-container">
    <?php if ($page): ?>
        <nav class="breadcrumb">
            <a href="index.php">Home</a> > <span><?= $page['category_name'] ?></span>
        </nav>

        <article class="main-article">
            <header class="article-header">
                <h1 class="entry-title"><?= $page['page_title'] ?></h1>
                <div class="entry-meta">
                    <span><i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($page['updated_at'])) ?></span>
                    <span><i class="fas fa-user"></i> Admin Vaksinin</span>
                </div>
            </header>

            <div class="entry-content">
                <?= $page['content'] ?>
            </div>
             
        </article>
    <?php else: ?>
        <div class="not-found">Halaman tidak ditemukan.</div>
    <?php endif; ?>
</div>

<?php include "content/footer.php"; ?>