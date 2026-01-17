// ==================== FORM LAYANAN ====================
function updateFormByService() {
    const layanan = document.getElementById('pelayananSelect').value;
    const labelNama = document.getElementById('labelNama');
    const inputNama = document.getElementById('namaLengkap');
    
    const fieldNIK = document.getElementById('fieldNIK');
    const fieldPaspor = document.getElementById('fieldPaspor');
    const inputNIK = document.getElementById('inputNIK');
    const inputPaspor = document.getElementById('inputPaspor');
    const nikRequired = document.getElementById('nikRequired');
    const pasporRequired = document.getElementById('pasporRequired');

    if (layanan === 'Umroh/Haji/Luar Negeri') {
        // Ubah label nama
        labelNama.innerHTML = 'Nama Lengkap Sesuai Paspor <span class="required">*</span>';
        inputNama.placeholder = 'Masukkan nama lengkap sesuai paspor (termasuk nama tambahan)';
        
        // Show Paspor, Hide NIK
        fieldPaspor.style.display = 'block';
        fieldNIK.style.display = 'none';
        
        // Paspor required, NIK optional
        inputPaspor.required = true;
        inputNIK.required = false;
        inputNIK.value = ''; // Clear NIK
        
        pasporRequired.style.display = 'inline';
        
    } else if (layanan === 'Vaksinasi Umum/Infus Vitamin') {
        // Ubah label nama
        labelNama.innerHTML = 'Nama Lengkap <span class="required">*</span>';
        inputNama.placeholder = 'Masukkan nama lengkap sesuai KTP';
        
        // Show NIK, Hide Paspor
        fieldNIK.style.display = 'block';
        fieldPaspor.style.display = 'none';
        
        // NIK required, Paspor optional
        inputNIK.required = true;
        inputPaspor.required = false;
        inputPaspor.value = ''; // Clear Paspor
        
        nikRequired.style.display = 'inline';
        
    } else {
        // Default: show both
        labelNama.innerHTML = 'Nama Lengkap <span class="required">*</span>';
        inputNama.placeholder = 'Masukkan nama lengkap';
        
        fieldNIK.style.display = 'block';
        fieldPaspor.style.display = 'none';
        
        inputNIK.required = false;
        inputPaspor.required = false;
    }
}

// ==================== HITUNG USIA ====================
function hitungUsia() {
    const inputTanggal = document.getElementById('tanggalLahir');
    
    if (!inputTanggal || !inputTanggal.value) {
        return;
    }

    const lahir = new Date(inputTanggal.value);
    const today = new Date();

    let usia = today.getFullYear() - lahir.getFullYear();
    const bulan = today.getMonth() - lahir.getMonth();

    if (bulan < 0 || (bulan === 0 && today.getDate() < lahir.getDate())) {
        usia--;
    }

    const kategori = usia < 18 ? 'Anak-anak' : 'Dewasa';

    const usiaText = document.getElementById('usiaText');
    const kategoriText = document.getElementById('kategoriText');
    const usiaInfo = document.getElementById('usiaInfo');
    const fieldNamaWali = document.getElementById('fieldNamaWali');
    const inputNamaWali = document.getElementById('inputNamaWali');

    if (usiaText) usiaText.textContent = usia;
    if (kategoriText) kategoriText.textContent = kategori;
    if (usiaInfo) usiaInfo.style.display = 'block';

    // Show Nama Wali jika anak-anak
    if (usia < 18) {
        fieldNamaWali.style.display = 'block';
        inputNamaWali.required = true;
    } else {
        fieldNamaWali.style.display = 'none';
        inputNamaWali.required = false;
        inputNamaWali.value = '';
    }
}

// ==================== DYNAMIC FIELDS ====================
function addField(type) {
    let container, inputHTML;
    
    if (type === 'email') {
        container = document.getElementById('emailContainer');
        inputHTML = '<input type="email" name="emails[]" placeholder="contoh@email.com">';
    } else if (type === 'phone') {
        container = document.getElementById('phoneContainer');
        inputHTML = '<input type="tel" name="phones[]" placeholder="08123456789">';
    }
    
    const div = document.createElement('div');
    div.className = 'dynamic-field';
    div.innerHTML = inputHTML + '<button type="button" class="btn btn-remove" onclick="removeField(this)">×</button>';
    container.appendChild(div);
}

function removeField(btn) {
    const container = btn.parentElement.parentElement;
    const fields = container.querySelectorAll('.dynamic-field');
    
    // Minimal harus ada 1 field
    if (fields.length > 1) {
        btn.parentElement.remove();
    } else {
        alert('Minimal harus ada 1 field yang diisi!');
    }
}

// ==================== SEARCH PATIENT ====================
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
                            <button class="btn btn-primary" onclick="fillPatientData(${patient.id})">
                                Gunakan Data Ini
                            </button>
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

function fillPatientData(patientId) {
    // Fetch patient data dan auto-fill form
    fetch('get_patient.php?id=' + patientId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const p = data.patient;
                
                // Fill form fields
                document.getElementById('pelayananSelect').value = p.pelayanan || '';
                updateFormByService();
                
                document.querySelector('input[name="nama_lengkap"]').value = p.nama_lengkap || '';
                document.querySelector('input[name="nama_panggilan"]').value = p.nama_panggilan || '';
                document.getElementById('tanggalLahir').value = p.tanggal_lahir || '';
                hitungUsia();
                
                if (p.jenis_kelamin) {
                    document.querySelector(`input[name="jenis_kelamin"][value="${p.jenis_kelamin}"]`).checked = true;
                }
                
                document.getElementById('inputNIK').value = p.nik || '';
                document.getElementById('inputPaspor').value = p.paspor || '';
                document.querySelector('input[name="kebangsaan"]').value = p.kebangsaan || 'Indonesia';
                document.querySelector('input[name="pekerjaan"]').value = p.pekerjaan || '';
                document.getElementById('inputNamaWali').value = p.nama_wali || '';
                
                // Kontak
                if (data.emails && data.emails.length > 0) {
                    const emailContainer = document.getElementById('emailContainer');
                    emailContainer.innerHTML = '';
                    data.emails.forEach(email => {
                        const div = document.createElement('div');
                        div.className = 'dynamic-field';
                        div.innerHTML = `<input type="email" name="emails[]" value="${email}" placeholder="contoh@email.com">
                            <button type="button" class="btn btn-remove" onclick="removeField(this)">×</button>`;
                        emailContainer.appendChild(div);
                    });
                }
                
                if (data.phones && data.phones.length > 0) {
                    const phoneContainer = document.getElementById('phoneContainer');
                    phoneContainer.innerHTML = '';
                    data.phones.forEach(phone => {
                        const div = document.createElement('div');
                        div.className = 'dynamic-field';
                        div.innerHTML = `<input type="tel" name="phones[]" value="${phone}" placeholder="08123456789">
                            <button type="button" class="btn btn-remove" onclick="removeField(this)">×</button>`;
                        phoneContainer.appendChild(div);
                    });
                }
                
                if (data.address) {
                    document.querySelector('textarea[name="alamat"]').value = data.address.alamat || '';
                    
                    // Set provinsi dulu
                    if (data.address.provinsi) {
                        document.getElementById('provinsiSelect').value = data.address.provinsi;
                        loadKota(); // Load kota sesuai provinsi
                        
                        // Set kota setelah kota ter-load
                        setTimeout(() => {
                            if (data.address.kota) {
                                document.getElementById('kotaSelect').value = data.address.kota;
                            }
                        }, 100);
                    }
                }
                
                // Riwayat
                document.querySelector('textarea[name="riwayat_alergi"]').value = p.riwayat_alergi || '';
                document.querySelector('textarea[name="riwayat_penyakit"]').value = p.riwayat_penyakit || '';
                document.querySelector('textarea[name="riwayat_obat"]').value = p.riwayat_obat || '';
                
                // Scroll ke form
                document.getElementById('registrationForm').scrollIntoView({ behavior: 'smooth' });
                
                alert('✅ Data pasien berhasil dimuat! Silakan pilih jadwal dan klik Selesai.');
            }
        })
        .catch(error => {
            alert('❌ Gagal memuat data pasien');
        });
}

// ==================== KALENDER ====================
let selectedDateValue = null;

function selectDate(element, day) {
    document.querySelectorAll('.day').forEach(d => {
        d.classList.remove('selected');
    });

    element.classList.add('selected');

    const tanggal = `${tahunNow}-${String(bulanNow).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    selectedDateValue = tanggal;

    document.getElementById('selectedDate').value = tanggal;
    document.getElementById('dateText').textContent = `${day} ${namaBulanNow} ${tahunNow}`;
    document.getElementById('dateDisplay').style.display = 'block';

    // Reset jam
    document.getElementById('selectedTime').value = '';
    document.getElementById('btnSelesai').disabled = true;

    // Generate time slots
    generateTimeSlots(tanggal);
}

function prevMonth() {
    let bulan = bulanNow;
    let tahun = tahunNow;

    bulan--;
    if (bulan < 1) {
        bulan = 12;
        tahun--;
    }

    document.getElementById('inputBulan').value = bulan;
    document.getElementById('inputTahun').value = tahun;
    document.getElementById('monthForm').submit();
}

function nextMonth() {
    let bulan = bulanNow;
    let tahun = tahunNow;

    bulan++;
    if (bulan > 12) {
        bulan = 1;
        tahun++;
    }

    document.getElementById('inputBulan').value = bulan;
    document.getElementById('inputTahun').value = tahun;
    document.getElementById('monthForm').submit();
}

function generateTimeSlots(tanggal) {
    const container = document.getElementById('slotsContainer');
    container.innerHTML = '<div class="loading">⏳ Memuat jadwal tersedia...</div>';

    // Fetch available slots dari server
    fetch(`check_slots.php?tanggal=${tanggal}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                container.innerHTML = '<div class="no-results">❌ Gagal memuat jadwal</div>';
                return;
            }

            // Cek hari libur
            if (data.is_holiday) {
                container.innerHTML = `<div class="no-results">🏖️ Hari Libur: ${data.holiday_name}<br>Klinik tutup.</div>`;
                document.getElementById('timeSlots').style.display = 'block';
                return;
            }

            // Cek klinik tutup
            if (data.is_closed) {
                container.innerHTML = '<div class="no-results">🔒 Klinik tutup di hari ini</div>';
                document.getElementById('timeSlots').style.display = 'block';
                return;
            }

            container.innerHTML = '';
            
            // Generate slots dari data API
            const allSlots = data.all_slots || [];
            const bookedSlots = data.booked || [];

            if (allSlots.length === 0) {
                container.innerHTML = '<div class="no-results">Tidak ada slot tersedia</div>';
                document.getElementById('timeSlots').style.display = 'block';
                return;
            }

            allSlots.forEach(label => {
                const slot = document.createElement('div');
                slot.classList.add('time-slot');
                slot.textContent = label;

                // Cek apakah slot sudah penuh (1 slot = 1 booking)
                if (bookedSlots.includes(label)) {
                    slot.classList.add('full');
                    slot.title = 'Slot sudah penuh';
                } else {
                    slot.onclick = () => selectTime(slot, label);
                }

                container.appendChild(slot);
            });

            document.getElementById('timeSlots').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="no-results">❌ Gagal memuat jadwal</div>';
        });
}

function selectTime(element, time) {
    document.querySelectorAll('.time-slot').forEach(s => {
        s.classList.remove('selected');
    });

    element.classList.add('selected');
    document.getElementById('selectedTime').value = time;

    // Enable button Selesai
    document.getElementById('btnSelesai').disabled = false;
}

// ==================== FORM VALIDATION ====================
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validasi layanan dipilih
            const layanan = document.getElementById('pelayananSelect').value;
            if (!layanan) {
                e.preventDefault();
                alert('Pilih layanan terlebih dahulu!');
                return;
            }
            
            // Validasi tanggal lahir
            const tglLahir = document.getElementById('tanggalLahir');
            if (!tglLahir || !tglLahir.value) {
                e.preventDefault();
                alert('Tanggal lahir harus diisi!');
                return;
            }
            
            // Validasi identitas sesuai layanan
            const inputNIK = document.getElementById('inputNIK');
            const inputPaspor = document.getElementById('inputPaspor');
            
            if (layanan === 'Umroh/Haji/Luar Negeri') {
                if (!inputPaspor.value.trim()) {
                    e.preventDefault();
                    alert('Nomor Paspor harus diisi untuk layanan Umroh/Haji/Luar Negeri!');
                    return;
                }
            } else if (layanan === 'Vaksinasi Umum/Infus Vitamin') {
                if (!inputNIK.value.trim()) {
                    e.preventDefault();
                    alert('NIK harus diisi untuk layanan Vaksinasi Umum/Infus Vitamin!');
                    return;
                }
                
                // Validasi format NIK (16 digit)
                if (inputNIK.value.trim().length !== 16) {
                    e.preventDefault();
                    alert('NIK harus 16 digit!');
                    return;
                }
            }
            
            // Validasi kontak minimal 1
            const emails = document.querySelectorAll('input[name="emails[]"]');
            const phones = document.querySelectorAll('input[name="phones[]"]');
            
            let emailValid = false;
            let phoneValid = false;
            
            emails.forEach(email => {
                if (email.value.trim() !== '') emailValid = true;
            });
            
            phones.forEach(phone => {
                if (phone.value.trim() !== '') phoneValid = true;
            });
            
            if (!emailValid || !phoneValid) {
                e.preventDefault();
                alert('Minimal harus ada 1 email dan 1 nomor HP yang diisi!');
                return;
            }
            
            // Validasi provinsi & kota
            const provinsi = document.getElementById('provinsiSelect').value;
            const kota = document.getElementById('kotaSelect').value;
            
            if (!provinsi || !kota) {
                e.preventDefault();
                alert('Provinsi dan Kota/Kabupaten harus dipilih!');
                return;
            }
            
            // Validasi jadwal dipilih
            const tanggalBooking = document.getElementById('selectedDate').value;
            const waktuBooking = document.getElementById('selectedTime').value;
            
            if (!tanggalBooking || !waktuBooking) {
                e.preventDefault();
                alert('Pilih tanggal dan jam booking terlebih dahulu!');
                return;
            }
        });
    }
    
    // Load provinsi saat halaman load
    if (typeof loadProvinsi === 'function') {
        loadProvinsi();
    }
});