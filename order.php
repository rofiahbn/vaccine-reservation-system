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

    <form id="registrationForm" method="POST" action="save_patient.php">

        <div class="form-section">
            <h2 class="section-title">Data Pribadi</h2>

            <div class="form-group">
                <label>Nama Lengkap <span class="required">*</span></label>
                <input type ="text" name="nama_lengkap" required placeholder="Masukkan nama lengkap sesuai KTP/Paspor">
            </div>

            <div class="row">
                <div class="form-group">
                    <label>Nama Panggilan</label>
                    <input type="text" name="nama_panggilan" placeholder="Nama Panggilan">
                </div>

                <div class="form-group">
                    <label>Tanggal Lahir <span class="required">*</span></label>
                    <input type="date" name="tanggal_lahir" id="tanggalLahir" required onchange="hitungUsia()">
                </div>

                <div class="info-box" id="usiaInfo" style="display:none;">
                    Usia: <strong id="usiaText">-</strong> tahun (<span id="kategoriText">-</span>)
                </div>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label>Jenis kelamin <span class="required">*</span></label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="jenis_kelamin" value="L" required> Laki-laki 
                        </label>
                        <label>
                            <input type="radio" name="jenis_kelamin" value="L" required> Perempuan 
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>NIK/No.Paspor <span class="required">*</span></label>
                    <input type="text" name="nik_paspor" required placeholder="16 digit NIK atau No. Paspor">
                </div>
            </div>

            <div class="row">
                    <div class="form-group">
                        <label>Kebangsaan <span class="required">*</span></label>
                        <input type="text" name="kebangsaan"  placeholder="Kebangsaan">
                    </div>
                    
                    <div class="form-group">
                        <label>Pekerjaan <span class="required">*</span></label>
                        <input type="text" name="pekerjaan" placeholder="Pekerjaan saat ini">
                    </div>
                </div>

            <div class="form-group">
                    <label>Nama Wali <span class="required">*</span></label>
                    <input type="text" name="nama_wali" placeholder="Nama orang tua/wali">
                </div>
            </div>

        <!-- Kontak -->
            <div class="form-section">
                <h2 class="section-title">Informasi Kontak</h2>
                
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <div id="emailContainer">
                        <div class="dynamic-field">
                            <input type="email" name="emails[]" required placeholder="contoh@email.com">
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
                            <input type="tel" name="phones[]" required placeholder="08123456789">
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
                            <textarea name="addresses[]" required placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota, Provinsi, Kode Pos"></textarea>
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
                    <textarea name="riwayat_alergi" placeholder="Contoh: Alergi obat penisilin, alergi makanan laut, dll. Kosongkan jika tidak ada."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Riwayat Penyakit Dahulu</label>
                    <textarea name="riwayat_penyakit" placeholder="Contoh: Diabetes, hipertensi, asma, dll. Kosongkan jika tidak ada."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Riwayat Pemakaian Obat</label>
                    <textarea name="riwayat_obat" placeholder="Obat yang sedang dikonsumsi rutin. Kosongkan jika tidak ada."></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Lanjut ke Pemilihan Jadwal →</button>
        </form>

        <script src="script.js"></script>

    </div>

</body>
</html>