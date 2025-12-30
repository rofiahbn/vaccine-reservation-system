<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pasien Vaksinasi</title>

    <link rel="stylesheet" href="style.css">

</head>
<body>
    <div class="container">
        <h1>Formulir Pendaftaran Pasien</h1>
        <p class="subtitle">Lengkapi data berikut untuk mendaftar vaksinasi</p>

        <!-- Search Section for Existing Patient -->
        <div class="search-section">
            <h2>Cari dan Temukan Datamu</h2>
            <p>Cukup masukkan nama Anda. Jika sudah pernah mendaftar, sistem akan menemukan data Anda secara otomatis agar proses lebih cepat dan praktis</p>
            
            <div class="search-simple">
                <input type="text" id="searchName" class="search-input-main" placeholder="Nama">
                <input type="text" id="searchNIK" class="search-input-main" placeholder="NIK">
                <button type="button" class="btn-search-main" onclick="searchPatient()">
                    Cari
                </button>
            </div>

            <div id="searchResults" style="display:none;"></div>
        </div>

    <form id="registrationForm" method="POST" action="save_patient.php">

    <div class="participant">
        <h2 class="participant-title">Data Peserta 1</h2>

        <div class="form-section">
            <h2 class="section-title">Data Pribadi</h2>

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
            <h2 class="section-title">Informasi Kontak</h2>
            
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

</body>
</html>