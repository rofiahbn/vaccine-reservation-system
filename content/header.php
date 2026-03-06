<?php
include "config/database.php"; // Pastikan path koneksi benar

// 1. Logika SEO Dinamis
// Ambil parameter 'p' dari URL, jika kosong arahkan ke 'home'
$slug = isset($_GET['p']) ? mysqli_real_escape_string($conn, $_GET['p']) : 'home';

// Query ambil data halaman berdasarkan slug
$queryPage = mysqli_query($conn, "SELECT * FROM pages WHERE slug = '$slug'");
$pageData  = mysqli_fetch_assoc($queryPage);

// Jika slug tidak ditemukan di tabel pages, gunakan default
$meta_title = !empty($pageData['meta_title']) ? $pageData['meta_title'] : "Klinik Vaksinin - Solusi Vaksin Keluarga";
$meta_desc  = !empty($pageData['meta_description']) ? $pageData['meta_description'] : "Layanan vaksinasi terpercaya untuk anak dan dewasa.";

// 2. Query Menu (Kode kamu yang sudah bagus)
$sql = "SELECT * FROM menus ORDER BY order_priority ASC"; // Urutkan berdasarkan priority
$result = mysqli_query($conn, $sql);
$all_menus = [];
while ($row = mysqli_fetch_assoc($result)) {
    $all_menus[] = $row;
}

// Fungsi buildMenu tetap sama (Rekursif)
function buildMenu($menus, $parent_id = 0) {
    $html = "";
    foreach ($menus as $menu) {
        if ($menu['parent_id'] == $parent_id) {
            $has_child = false;
            foreach ($menus as $child) {
                if ($child['parent_id'] == $menu['id']) {
                    $has_child = true;
                    break;
                }
            }

            // Modifikasi link agar mengarah ke parameter p=slug
            // Asumsi: kolom 'link' di tabel menus berisi slug yang sama dengan tabel pages
            $link = ($menu['link'] == "#") ? "#" : "pages?p=" . str_replace(".php", "", $menu['link']);

            if ($has_child) {
                $html .= '<li class="has-dropdown">';
                $html .= '<a href="' . $link . '" class="dropdown-toggle">' . $menu['title'] . '</a>';
                $html .= '<ul class="dropdown">';
                $html .= buildMenu($menus, $menu['id']);
                $html .= '</ul>';
                $html .= '</li>';
            } else {
                $html .= '<li><a href="' . $link . '">' . $menu['title'] . '</a></li>';
            }
        }
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="id">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?= $meta_title; ?></title>
    <meta name="description" content="<?= $meta_desc; ?>">
    
    <link rel="icon" type="image/x-icon" href="img/icon.png">
    <link rel="stylesheet" href="css/style.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	
	
</head>
<body>

<header class="navbar">
    <a href="/"><img class="logo" src="img/logoVaksinin.png" alt="Logo Vaksinin" /></a>

    <button class="hamburger" id="hamburger">&#9776;</button>

    <nav class="nav" id="nav">
	
        <ul class="menu">
		<li><a href="/">Home</a></li>
            <?php echo buildMenu($all_menus); ?>
        </ul>
        <a href="order" class="btn2">Pesan Sekarang</a>
    </nav>
</header>
<img src="img/icon.png" id="call-us-icon" />