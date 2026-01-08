function hitungUsia(inputElement) {
    console.log('hitungUsia called');
    
    if (!inputElement || !inputElement.value) {
        console.log('No date value');
        return;
    }

    const lahir = new Date(inputElement.value);
    const today = new Date();

    let usia = today.getFullYear() - lahir.getFullYear();
    const bulan = today.getMonth() - lahir.getMonth();

    if (bulan < 0 || (bulan === 0 && today.getDate() < lahir.getDate())) {
        usia--;
    }

    const kategori = usia < 18 ? 'Anak' : 'Dewasa';

    // CARI ELEMENT PERTAMA SAJA (UNTUK PESERTA 1)
    const usiaText = document.querySelector('.usiaText');
    const kategoriText = document.querySelector('.kategoriText');
    const usiaInfo = document.querySelector('.usiaInfo');

    console.log('Found elements:', {
        usiaText: !!usiaText,
        kategoriText: !!kategoriText,
        usiaInfo: !!usiaInfo
    });

    if (usiaText) usiaText.textContent = usia;
    if (kategoriText) kategoriText.textContent = kategori;
    if (usiaInfo) {
        usiaInfo.style.display = 'block';
        console.log('Age:', usia, 'Category:', kategori);
    }
}

function addField(type) {
    let container, inputHTML;
    
    if (type === 'email') {
        container = document.getElementById('emailContainer');
        inputHTML = '<input type="email" name="emails[]" placeholder="contoh@email.com">';
    } else if (type === 'phone') {
        container = document.getElementById('phoneContainer');
        inputHTML = '<input type="tel" name="phones[]" placeholder="08123456789">';
    } else if (type === 'address') {
        container = document.getElementById('addressContainer');
        inputHTML = '<textarea name="addresses[]" placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota, Provinsi, Kode Pos"></textarea>';
    }
    
    const div = document.createElement('div');
    div.className = 'dynamic-field';
    div.innerHTML = inputHTML + '<button type="button" class="btn btn-remove" onclick="removeField(this)">×</button>';
    container.appendChild(div);
}

function removeField(btn) {
    btn.parentElement.remove();
}

function searchPatient() {
    const name = document.getElementById('searchName').value.trim();
    const nik = document.getElementById('searchNIK').value.trim();

    if (!name || !nik) {
        alert('Masukkan Nama dan NIK untuk mencari data pasien');
        return;
    }

    const resultsDiv = document.getElementById('searchResults');
    resultsDiv.style.display = 'block';
    resultsDiv.innerHTML = '<div class="loading">🔍 Mencari data pasien...</div>';

    const params = new URLSearchParams();
    if (name) params.append('name', name);
    if (nik) params.append('nik', nik);

    fetch('search_patient.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            if (data.success && data.patients.length > 0) {
                let html = '<div class="search-results">';
                data.patients.forEach(patient => {
                    html += `
                        <div class="patient-card">
                            <h4>${patient.nama_lengkap}</h4>
                            <p>📋 No. RM: ${patient.no_rekam_medis}</p>
                            <p>📅 ${patient.tanggal_lahir} (${patient.usia} tahun)</p>
                            <p>📱 ${patient.phone || '-'}</p>
                            <span class="badge" onclick="selectPatient(${patient.id})">Klik untuk melanjutkan</span>
                        </div>
                    `;
                });
                html += '</div>';
                resultsDiv.innerHTML = html;
            } else {
                resultsDiv.innerHTML = '<div class="no-results">😔 Tidak ditemukan data pasien dengan kriteria tersebut.<br>Silakan daftar sebagai pasien baru di bawah.</div>';
            }
        })
        .catch(error => {
            resultsDiv.innerHTML = '<div class="no-results">❌ Terjadi kesalahan saat mencari data</div>';
        });
}

function selectPatient(patientId) {
    window.location.href = 'calender.php?id_pasien=' + patientId;
}

// Validasi form sebelum submit
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validasi tanggal lahir - pakai class bukan id!
            const tglLahir = document.querySelector('.tanggalLahir');
            if (!tglLahir || !tglLahir.value) {
                e.preventDefault();
                alert('Tanggal lahir harus diisi!');
                return;
            }
            
            // Validasi minimal 1 kontak
            const emails = document.querySelectorAll('input[name^="participants"][name$="[emails][]"]');
            const phones = document.querySelectorAll('input[name^="participants"][name$="[phones][]"]');
            const addresses = document.querySelectorAll('textarea[name^="participants"][name$="[addresses][]"]');
            
            let emailValid = false;
            let phoneValid = false;
            let addressValid = false;
            
            emails.forEach(email => {
                if (email.value.trim() !== '') emailValid = true;
            });
            
            phones.forEach(phone => {
                if (phone.value.trim() !== '') phoneValid = true;
            });
            
            addresses.forEach(address => {
                if (address.value.trim() !== '') addressValid = true;
            });
            
            if (!emailValid || !phoneValid || !addressValid) {
                e.preventDefault();
                alert('Minimal harus ada 1 email, 1 nomor HP, dan 1 alamat yang diisi!');
            }
        });
    }
});