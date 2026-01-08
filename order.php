<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pasien Vaksinasi</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="layout.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-logo">
            <img src="logo-vaksinin.jpeg" alt="Vaksinin">
        </div>

        <ul class="nav-menu">
            <li><a href="order.php">Home</a></li>
            <li><a href="#">Layanan</a></li>
            <li><a href="#">Jadwal Vaksin</a></li>
            <li><a href="#">Dokter</a></li>
            <li><a href="#">Profil</a></li>
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Kontak</a></li>
        </ul>
    </nav>

    <header class="main-header">
        <div class="hero">
            <div class="hero-content">
                <span class="hero-badge">Pendaftaran online resmi melalui Vaksinin.id</span>
                <h1>Lindungi Diri dan<br>Keluarga dengan Vaksinasi</h1>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="search-section">
            <h2>Cari dan Temukan Datamu</h2>
            <p>Cukup masukkan nama dan NIK Anda. Jika sudah pernah mendaftar, sistem akan menemukan data Anda secara otomatis agar proses lebih cepat dan praktis</p>
            
            <div class="search-simple">
                <input type="text" id="searchName" class="search-input-main" placeholder="Nama">
                <input type="text" id="searchNIK" class="search-input-main" placeholder="NIK">
                <button type="button" class="btn-search-main" onclick="searchPatient()">
                    Cari
                </button>
            </div>

            <div id="searchResults" style="display:none;"></div>
        </div>

    <h1>Formulir Pendaftaran Pasien</h1>
    <p class="subtitle">Isi dan lengkapi data dibawah ini untuk melanjutkan proses pendaftaran</p>

    <form id="registrationForm" method="POST" action="save_patient.php">

    <div class="participant">

        <div class="form-section">

            <div class="form-group">
                <label>Nama Lengkap <span class="required">*</span></label>
                <input type="text" name="participants[0][nama_lengkap]" required placeholder="Masukkan nama lengkap sesuai KTP/Paspor">
            </div>

            <div class="row">
                <div class="form-group">
                    <label>Nama Panggilan</label>
                    <input type="text" name="participants[0][nama_panggilan]" placeholder="Nama Panggilan">
                </div>

                <div class="form-group">
                    <label>Tanggal Lahir <span class="required">*</span></label>
                    <input type="date" name="participants[0][tanggal_lahir]" class="tanggalLahir" required onchange="hitungUsia(this)">
                </div>
            </div>

            <div class="info-box usiaInfo" style="display:none;">
                Usia: <strong class="usiaText">-</strong> tahun (<span class="kategoriText">-</span>)
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label>Jenis kelamin <span class="required">*</span></label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="participants[0][jenis_kelamin]" value="L" required> Laki-laki 
                        </label>
                        <label>
                            <input type="radio" name="participants[0][jenis_kelamin]" value="P" required> Perempuan 
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>NIK/No.Paspor <span class="required">*</span></label>
                    <input type="text" name="participants[0][nik_paspor]" required placeholder="16 digit NIK atau No. Paspor">
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <label>Kebangsaan</label>
                    <input type="text" name="participants[0][kebangsaan]" value="Indonesia" placeholder="Kebangsaan">
                </div>
                
                <div class="form-group">
                    <label>Pekerjaan</label>
                    <input type="text" name="participants[0][pekerjaan]" placeholder="Pekerjaan saat ini">
                </div>
            </div>

            <div class="form-group">
                <label>Nama Wali <small>(untuk pasien anak-anak)</small></label>
                <input type="text" name="participants[0][nama_wali]" placeholder="Nama orang tua/wali">
            </div>
        </div>

        <!-- Kontak -->
        <div class="form-section">
            
            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <div id="emailContainer">
                    <div class="dynamic-field">
                        <input type="email" name="participants[0][emails][]" required placeholder="contoh@email.com">
                    </div>
                </div>
                <button type="button" class="btn btn-add" onclick="addField('email')">
                    + Tambah Email
                </button>
            </div>
            
            <div class="form-group">
                <label>Nomor HP <span class="required">*</span></label>
                <div id="phoneContainer">
                    <div class="dynamic-field">
                        <input type="tel" name="participants[0][phones][]" required placeholder="08123456789">
                    </div>
                </div>
                <button type="button" class="btn btn-add" onclick="addField('phone')">
                    + Tambah Nomor HP
                </button>
            </div>
            
            <div class="form-group">
                <label>Alamat Lengkap <span class="required">*</span></label>
                <div id="addressContainer">
                    <div class="dynamic-field">
                        <textarea name="participants[0][addresses][]" required placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota, Provinsi, Kode Pos"></textarea>
                    </div>
                </div>
                <button type="button" class="btn btn-add" onclick="addField('address')">
                    + Tambah Alamat
                </button>
            </div>
        </div>

        <!-- Riwayat Kesehatan -->
        <div class="form-section">
            <h2 class="section-title">Riwayat Kesehatan</h2>
            
            <div class="form-group">
                <label>Riwayat Alergi</label>
                <textarea name="participants[0][riwayat_alergi]" placeholder="Contoh: Alergi obat penisilin, alergi makanan laut, dll. Kosongkan jika tidak ada."></textarea>
            </div>
            
            <div class="form-group">
                <label>Riwayat Penyakit Dahulu</label>
                <textarea name="participants[0][riwayat_penyakit]" placeholder="Contoh: Diabetes, hipertensi, asma, dll. Kosongkan jika tidak ada."></textarea>
            </div>
            
            <div class="form-group">
                <label>Riwayat Pemakaian Obat</label>
                <textarea name="participants[0][riwayat_obat]" placeholder="Obat yang sedang dikonsumsi rutin. Kosongkan jika tidak ada."></textarea>
            </div>

            <div class="form-group">
                <label>Pilih Pelayanan <span class="required">*</span></label>
                <select name="participants[0][pelayanan]"  id="pelayananSelect" required onchange="tampilkanPilihanPelayanan()">
                    <option value="">-- Pilih Pelayanan --</option>
                    <option value="Vaksin">Vaksin</option>
                    <option value="Vitamin">Vitamin</option>
                    <option value="Antigen">Antigen</option>
                    <option value="Obat">Obat</option>
                    <option value="PCR">PCR</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Container untuk pilihan vaksin/vitamin/dll -->
    <div id="pilihanPelayananContainer" style="display:none;"></div>

    <!-- Container untuk peserta tambahan -->
    <div id="additionalParticipants"></div>

    <!-- Button Tambah Peserta -->
    <button type="button" class="btn btn-add-participant" onclick="addParticipant()">
         Tambah Pendaftar
    </button>

    <button type="submit" class="btn btn-primary">Lanjut ke Pemilihan Jadwal →</button>
</form>

        <script src="script.js?v=<?php echo time(); ?>"></script>
        <script src="participant.js?v=<?php echo time(); ?>"></script>
        <script src="service.js?v=<?php echo time(); ?>"></script>

    </div>
<footer class="footer">
    <div class="footer-container">
        <div class="nav-logo-footer">
            <img src="logo-vaksinin.jpeg" alt="Vaksinin">
        </div>

        <div class="footer-section">
            <h3>Jam Operasional</h3>
            <h4>Home Service</h4>
            <p>Dengan bantuan helper</p>
            <h4>Klinik</h4>
            <p>Senin - Sabtu : 08:00 – 17:00</p>
            <p>Minggu : 08:00 – 18:30</p>
            <div class="note-footer">
                <p>Hari libur nasional dan hari besar lainnya : <b>Tutup</b></p>
            </div>
        </div>
        
        <div class="footer-section">
            <h3>Hubungi Kami</h3>
            <h4>Klinik Vaksinin</h4>
            <p>Komplek Ruko Sentra Menteng Blok M No. 981<br>
            Jl. MH. Thamrin, Bintaro Sektor 7<br>
            Kel. Pondok Jaya, Kec. Pondok Aren,<br>
            Kota Tangerang Selatan, Banten 15220</p>
            <a href="https://goo.gl/maps/f2suTc2vR7JC1Me47" class="map-link" target="_blank">
                Lihat di Google Maps
            </a>
        </div>
    </div>
    
    <!-- CONTACT ICONS DI TENGAH BAWAH -->
    <div class="contact-icons-wrapper">
        <div class="contact-icons-container">
            <div class="contact-icons-row">
                <div class="contact-icon-item">
                    <i class="fab fa-whatsapp"></i>
                    <span class="number">082137372757</span>
                </div>
                <div class="contact-icon-item">
                    <i class="fas fa-phone"></i>
                    <span class="number">02122214342</span>
                </div>
                <div class="contact-icon-item">
                    <i class="fab fa-instagram"></i>
                    <a href="https://instagram.com/vaksinin.id" target="_blank">@vaksinin.id</a>
                </div>
                <div class="contact-icon-item">
                    <i class="fab fa-facebook"></i>
                    <a href="https://facebook.com/vaksinin.id" target="_blank">vaksinin.id</a>
                </div>
                <div class="contact-icon-item">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:vaksinin.id@gmail.com">vaksinin.id@gmail.com</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="copyright">
        <p>© 2024 Vaksinin.id - Seluruh hak cipta dilindungi undang-undang</p>
        <p>Lindungi Diri dan Keluarga dengan Vaksinasi</p>
    </div>
</footer>
</body>
</html>