<?php
$conn = mysqli_connect("localhost", "u8150211_vaksinin", "u8150211_vaksinin", "u8150211_vaksinin");

// Ambil semua data menu
$sql = "SELECT * FROM menus ORDER BY id ASC";
$result = mysqli_query($conn, $sql);

$all_menus = [];
while ($row = mysqli_fetch_assoc($result)) {
    $all_menus[] = $row;
}

// Fungsi untuk menyusun menu secara dinamis
function buildMenu($menus, $parent_id = 0) {
    $html = "";
    foreach ($menus as $menu) {
        if ($menu['parent_id'] == $parent_id) {
            
            // Cek apakah menu ini punya anak (submenu)
            $has_child = false;
            foreach ($menus as $child) {
                if ($child['parent_id'] == $menu['id']) {
                    $has_child = true;
                    break;
                }
            }

            if ($has_child) {
                // Jika punya anak, tambahkan class dropdown
                $html .= '<li class="has-dropdown">';
                $html .= '<a href="' . $menu['link'] . '" class="dropdown-toggle">' . $menu['title'] . '</a>';
                $html .= '<ul class="dropdown">';
                $html .= buildMenu($menus, $menu['id']); // Panggil fungsi ini lagi (Rekursif)
                $html .= '</ul>';
                $html .= '</li>';
            } else {
                // Jika menu biasa
                $html .= '<li><a href="' . $menu['link'] . '">' . $menu['title'] . '</a></li>';
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
<title>Klinik Vaksinin - Dashboard</title>

<link rel="icon" type="image/x-icon" href="/img/icon.png">
<link rel="stylesheet" href="css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body>
<a href="#"><img src="img/vaksinin-warior.png" id="chat-us" /></a>
<header class="navbar">
  <img class="logo" src="img/logoVaksinin.png" />

  <button class="hamburger" id="hamburger">&#9776;</button>

  <nav class="nav" id="nav">
    <ul class="menu">
      <?php echo buildMenu($all_menus); ?>
    </ul>
    
    <a href="#" class="btn">Pesan Sekarang</a>
  </nav>
</header> 
<div class="login-card">
    <h2>Lupa Password</h2>

   <form id="formLogin">
       <div class="form-group">
        <label>Email</label>
<input type="email" name="email" required><br><br>
<button type="submit">Kirim Link Reset</button>
        </div>
</form>
 
</div>
<div id="footer">
    <div id="operational" >
        <h1>Jam Operasional</h1>
        <h2>Home Service</h2>
        <p>Dengan Perjanjian</p>
        <h2>Klinik</h2>
        <p>Senin - Sabu : 09.00 - 17.00</p>
        <p>Minggu       : 09.00 - 16.30 </p>
        <p> Hari libur nasional dan cuti bersama:<br/>tutup</p>
        
    </div>
    
    <div id="alamat">
        <h1>Hubungi Kami</h1>
        <h2>Klinik Vaksinin</h2>
        <p>Komplek Ruko Sentra Menteng Blok MN 88 I Jl. Moh. Husni Thamrin, Bintaro Sektor 7 Kel. Pondok Jaya, Kec. Pondok Aren, Kota Tangerang Selatan, Banten 15220</p>
        <br/>
        <p>https://maps.app.goo.gl/Hc3H8Xe7RzLvLMR6A</p>
    </div>
    
    <div id="maps" >
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.9113192216073!2d106.71921788476418!3d-6.275389945203535!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f1ff936d9b19%3A0xd5c17ad1727bba4b!2sVaksinin%20Bintaro!5e0!3m2!1sid!2sid!4v1770553198277!5m2!1sid!2sid" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
</div>

<script src="https://widget.tagembed.com/embed.min.js" type="text/javascript"></script>
<script>
  const hamburger = document.getElementById('hamburger');
  const nav = document.getElementById('nav');

  // Toggle Menu Utama di Mobile
  hamburger.addEventListener('click', () => {
    nav.classList.toggle('active');
  });

  // Toggle Dropdown di Mobile
  document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', function (e) {
      if (window.innerWidth <= 768) {
        e.preventDefault(); // Mencegah link pindah halaman
        const parent = this.parentElement;
        parent.classList.toggle('active');
      }
    });
  });
</script> 

</body>
</html>
