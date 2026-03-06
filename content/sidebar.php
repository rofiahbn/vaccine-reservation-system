 
<!DOCTYPE html>
<html lang="id">
<head> 
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Klinik Vaksinin - Dashboard</title>

<link rel="icon" type="image/x-icon" href="/img/icon.png"> 
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="css/style-admin.css">

    <link rel="stylesheet" href="system/admin/css/admin.css">

    <link rel="stylesheet" href="system/admin/css/sidebar-toggle.css">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


    <script src="system/admin/js/sidebar-toggle.js"></script>
</head>
<body>
 <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="img/vaksinin-logo-no BG-orange-putih.png" alt="Vaksinin" class="logo-full">
             
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="javascript:void(0)" 
                class="nav-item has-submenu <?= in_array($current_page, ['products.php','products_pelayanan.php']) ? 'active open' : '' ?>" 
                onclick="toggleSubmenu(this)">
                <i class="fas fa-capsules"></i>
                <span>Produk</span> 
                <i class="fas fa-chevron-down arrow"></i>
            </a>
                
            <ul class="submenu <?= in_array($current_page, ['products.php','products_pelayanan.php']) ? 'open' : '' ?>">
                <li>
                    <a href="products.php" class="<?= $current_page == 'products.php' ? 'active' : '' ?>">
                        Vaksin & Obat
                    </a>
                </li>
                <li>
                    <a href="products_jasa.php" class="<?= $current_page == 'products_jasa.php' ? 'active' : '' ?>">
                    Jasa
                    </a>
                </li>
                <li>
                    <a href="products_pelayanan.php" class="<?= $current_page == 'products_pelayanan.php' ? 'active' : '' ?>">
                        Pelayanan/Paket
                    </a>
                </li>
            </ul>
            <a href="patients.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Pasien</span>
            </a> 
            <a href="calendar_setting.php" class="nav-item">
                <i class="fas fa-calendar"></i>
                <span>Kalender</span>
            </a>
			<a href="laporan.php" class="nav-item ">
                <i class="fas fa-chart-bar"></i>
                <span>Laporan</span>
            </a>
            
        </nav>
        <div class="sidebar-footer">
		 
		 <a href="/edit-staff?id=<?= $current_user_id ?>" class="nav-item">
                <i class="fa-solid fa-user"></i>
                <span>Profil</span>
         </a>	
		 <a href="stafflist" class="nav-item">
               <i class="fa-solid fa-address-card"></i>
                <span>Staff</span>
         </a>
		<a href="web-interface" class="nav-item">
                <i class="fa-solid fa-globe"></i>
                <span>Tampilan Website</span>
         </a>	
		<a href="/role-list" class="nav-item">
               <i class="fas fa-lock"></i>
                <span>Roles Setting</span>
         </a>
		 
		 
            <a href="logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
	 
