// add_participant.js
let selectedPatientId = null;
let selectedServices = [];
let parentBookingId = bookingId; // Gunakan variabel dari HTML

// Function to open add participant modal
function addParticipant() {
    
    // Tampilkan modal
    document.getElementById('addParticipantModal').style.display = 'flex';
    
    // Load daftar pasien
    loadPatients();
    
    // Reset form dan pilihan
    clearSelectedPatient();
    hideNewPatientForm();
}

// Load daftar pasien saat modal dibuka
function loadPatients(search = '') {
    const patientList = document.getElementById('patientList');
    if (!patientList) return;
    
    patientList.innerHTML = '<div class="loading-patients"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';
    
    let url = 'get_patients.php';
    if (search) {
        url += '?search=' + encodeURIComponent(search);
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                let html = '';
                data.data.forEach(patient => {
                    // Escape nama untuk keamanan di onclick
                    const escapedName = patient.nama_lengkap.replace(/'/g, "\\'");
                    html += `
                        <div class="patient-item" onclick="selectPatient(${patient.id}, '${escapedName}')">
                            <div class="patient-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="patient-info">
                                <div class="patient-name">${escapeHtml(patient.nama_lengkap)}</div>
                                <div class="patient-details">
                                    ${patient.usia} tahun, 
                                    ${patient.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'}
                                    ${patient.no_rekam_medis ? ' • RM: ' + patient.no_rekam_medis : ''}
                                </div>
                            </div>
                            <div class="patient-select">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                    `;
                });
                patientList.innerHTML = html;
            } else {
                patientList.innerHTML = `
                    <div class="empty-patients">
                        <i class="fas fa-users-slash"></i>
                        <p>Tidak ada pasien ditemukan</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (patientList) {
                patientList.innerHTML = '<div class="error-patients">Gagal memuat data</div>';
            }
        });
}

// Escape HTML untuk keamanan
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Search pasien dengan debounce
let searchTimeout;
function searchPatients() {
    clearTimeout(searchTimeout);
    const search = document.getElementById('searchPatient').value;
    searchTimeout = setTimeout(() => {
        loadPatients(search);
    }, 500);
}

// Pilih pasien existing
function selectPatient(patientId, patientName) {
    selectedPatientId = patientId;
    
    // Enable tombol add services (masih disabled karena belum pilih layanan)
    const btnAdd = document.getElementById('btnAddServices');
    if (btnAdd) {
        btnAdd.disabled = true; // Tetap disabled sampai pilih layanan
    }
    
    // Tampilkan info pasien terpilih
    const selectedInfo = document.getElementById('selectedPatientInfo');
    if (selectedInfo) {
        selectedInfo.innerHTML = `
            <div class="selected-patient-badge">
                <i class="fas fa-check-circle"></i>
                Pasien: <strong>${escapeHtml(patientName)}</strong>
                <button onclick="clearSelectedPatient()" class="btn-clear-selection" title="Hapus pilihan">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    }
    
    // Reset selected services
    selectedServices = [];
    
    // Tutup modal add participant dan buka modal pilih layanan
    closeAddParticipantModal();
    openServicesModal(patientId, patientName);
}

// Clear pilihan pasien
function clearSelectedPatient() {
    selectedPatientId = null;
    const selectedInfo = document.getElementById('selectedPatientInfo');
    if (selectedInfo) selectedInfo.innerHTML = '';
    
    const btnAdd = document.getElementById('btnAddServices');
    if (btnAdd) btnAdd.disabled = true;
    
    const servicesList = document.getElementById('servicesList');
    if (servicesList) {
        servicesList.innerHTML = '<div class="loading-services">Pilih pasien terlebih dahulu</div>';
    }
    
    selectedServices = [];
    updateSelectedServicesList();
}

// Load daftar layanan
// Load daftar layanan
function loadServices() {
    const servicesList = document.getElementById('servicesList');
    if (!servicesList) return;
    
    servicesList.innerHTML = '<div class="loading-services"><i class="fas fa-spinner fa-spin"></i> Memuat layanan...</div>';
    
    fetch('get_services.php')
        .then(response => response.json())
        .then(data => {
            console.log('Services data:', data); // Untuk debug
            
            if (data.success && data.data.length > 0) {
                let html = '';
                data.data.forEach(service => {
                    // Tentukan icon berdasarkan tipe
                    let icon = 'fa-syringe';
                    if (service.tipe === 'paket') {
                        icon = 'fa-box';
                    } else if (service.tipe === 'jasa') {
                        icon = 'fa-stethoscope';
                    }
                    
                    html += `
                        <div class="service-item" onclick="toggleService(${service.id}, '${escapeHtml(service.nama_layanan)}', ${service.harga || 0})">
                            <div class="service-info">
                                <div class="service-name">
                                    <i class="fas ${icon}" style="margin-right: 8px; color: #64748b;"></i>
                                    ${escapeHtml(service.nama_layanan)}
                                </div>
                                <div class="service-price">Rp ${formatRupiah(service.harga || 0)}</div>
                                ${service.kategori_usia ? `<div class="service-category">${service.kategori_usia}</div>` : ''}
                            </div>
                            <div class="service-checkbox" id="service-${service.id}">
                                <i class="far fa-square"></i>
                            </div>
                        </div>
                    `;
                });
                servicesList.innerHTML = html;
            } else {
                servicesList.innerHTML = '<div class="empty-services">Tidak ada layanan tersedia</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (servicesList) {
                servicesList.innerHTML = '<div class="error-services">Gagal memuat layanan</div>';
            }
        });
}

// Toggle pilih layanan
function toggleService(serviceId, serviceName, price) {
    const index = selectedServices.findIndex(s => s.id === serviceId);
    const checkbox = document.getElementById(`service-${serviceId}`);
    
    if (index === -1) {
        // Tambah ke selected
        selectedServices.push({
            id: serviceId,
            name: serviceName,
            price: price
        });
        if (checkbox) checkbox.innerHTML = '<i class="fas fa-check-square"></i>';
    } else {
        // Hapus dari selected
        selectedServices.splice(index, 1);
        if (checkbox) checkbox.innerHTML = '<i class="far fa-square"></i>';
    }
    
    updateSelectedServicesList();
    
    // Enable/disable tombol berdasarkan selectedServices
    const btnAdd = document.getElementById('btnAddServices');
    if (btnAdd) {
        btnAdd.disabled = selectedServices.length === 0;
    }
}

// Update daftar layanan yang dipilih
function updateSelectedServicesList() {
    const list = document.getElementById('selectedServicesList');
    const totalSpan = document.getElementById('totalPrice');
    if (!list || !totalSpan) return;
    
    let total = 0;
    
    if (selectedServices.length === 0) {
        list.innerHTML = '<div class="no-services">Belum ada layanan dipilih</div>';
        totalSpan.textContent = 'Rp 0';
        return;
    }
    
    let html = '';
    selectedServices.forEach(service => {
        total += service.price;
        html += `
            <div class="selected-service-item">
                <span>${escapeHtml(service.name)}</span>
                <span>Rp ${formatRupiah(service.price)}</span>
                <button onclick="removeService(${service.id})" class="btn-remove-service" title="Hapus">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    });
    
    list.innerHTML = html;
    totalSpan.textContent = `Rp ${formatRupiah(total)}`;
}

// Hapus service dari selected
function removeService(serviceId) {
    const index = selectedServices.findIndex(s => s.id === serviceId);
    if (index !== -1) {
        selectedServices.splice(index, 1);
        const checkbox = document.getElementById(`service-${serviceId}`);
        if (checkbox) {
            checkbox.innerHTML = '<i class="far fa-square"></i>';
        }
        updateSelectedServicesList();
        
        // Update tombol
        const btnAdd = document.getElementById('btnAddServices');
        if (btnAdd) {
            btnAdd.disabled = selectedServices.length === 0;
        }
    }
}

// Format Rupiah
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}

// Tambahkan layanan ke booking
function addServicesToBooking() {
    if (!selectedPatientId) {
        alert('Pilih pasien terlebih dahulu');
        return;
    }
    
    if (selectedServices.length === 0) {
        alert('Pilih minimal 1 layanan');
        return;
    }
    
    const btn = document.getElementById('btnAddServices');
    if (!btn) return;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menambahkan...';
    
    const data = {
        parent_booking_id: parentBookingId,
        patient_id: selectedPatientId,
        services: selectedServices.map(s => s.id)
    };
    
    fetch('add_participant.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Peserta berhasil ditambahkan!');
            location.reload();
        } else {
            alert('Gagal menambahkan peserta: ' + (data.message || ''));
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus-circle"></i> Tambahkan ke Booking';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus-circle"></i> Tambahkan ke Booking';
    });
}

// Close modal add participant
function closeAddParticipantModal() {
    const modal = document.getElementById('addParticipantModal');
    if (modal) modal.style.display = 'none';
    
    // Reset
    const searchInput = document.getElementById('searchPatient');
    if (searchInput) searchInput.value = '';
    
    clearSelectedPatient();
    hideNewPatientForm();
}

// Show new patient form
function showNewPatientForm() {
    console.log('Show new patient form dipanggil');
    
    const newForm = document.getElementById('newPatientForm');
    const selectionSection = document.querySelector('.selection-section');
    const newSection = document.querySelector('.new-patient-section');
    
    if (newForm) {
        newForm.style.display = 'block';
        console.log('Form ditampilkan');
    }
    if (selectionSection) selectionSection.style.display = 'none';
    if (newSection) newSection.style.display = 'none';
    
    // Reset form
    document.getElementById('newPatientFormElement').reset();
    document.getElementById('waliSection').style.display = 'none';
    document.getElementById('usiaDisplay').value = '';
    
    // PANGGIL LOAD PROVINSI DARI provinces.js
    if (typeof loadProvinsi === 'function') {
        console.log('Memanggil loadProvinsi dari provinces.js');
        loadProvinsi();
    } else {
        console.error('Function loadProvinsi tidak ditemukan!');
    }
}

// Hide new patient form
function hideNewPatientForm() {
    const newForm = document.getElementById('newPatientForm');
    const selectionSection = document.querySelector('.selection-section');
    const newSection = document.querySelector('.new-patient-section');
    
    if (newForm) newForm.style.display = 'none';
    if (selectionSection) selectionSection.style.display = 'block';
    if (newSection) newSection.style.display = 'block';
}

// Hitung usia otomatis
function hitungUsiaDetail(input) {
    if (!input.value) return;
    
    const tglLahir = new Date(input.value);
    const today = new Date();
    
    let tahun = today.getFullYear() - tglLahir.getFullYear();
    let bulan = today.getMonth() - tglLahir.getMonth();
    let hari = today.getDate() - tglLahir.getDate();
    
    // Koreksi jika hari negatif (belum ulang tahun di bulan ini)
    if (hari < 0) {
        bulan--;
        // Dapatkan jumlah hari di bulan sebelumnya
        const lastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
        hari += lastMonth.getDate();
    }
    
    // Koreksi jika bulan negatif
    if (bulan < 0) {
        tahun--;
        bulan += 12;
    }
    
    // Simpan di hidden input
    document.getElementById('usiaTahun').value = tahun;
    document.getElementById('usiaBulan').value = bulan;
    
    // Tampilkan di field usia dengan format "X tahun Y bulan"
    const usiaDisplay = document.getElementById('usiaDisplay');
    if (tahun === 0) {
        usiaDisplay.value = bulan + ' bulan';
    } else if (bulan === 0) {
        usiaDisplay.value = tahun + ' tahun';
    } else {
        usiaDisplay.value = tahun + ' tahun ' + bulan + ' bulan';
    }
    
    // CEK USIA UNTUK WALI - Anak dibawah 12 tahun
    const waliSection = document.getElementById('waliSection');
    const namaWali = document.getElementById('namaWali');
    
    if (tahun < 12) {
        // Tampilkan section wali
        waliSection.style.display = 'block';
        namaWali.setAttribute('required', 'required');
        console.log('Anak dibawah 12 tahun - tampilkan wali');
    } else {
        // Sembunyikan section wali
        waliSection.style.display = 'none';
        namaWali.removeAttribute('required');
        namaWali.value = ''; // Kosongkan nilai
        console.log('Diatas 12 tahun - wali tidak diperlukan');
    }
}

// Save new patient
function saveNewPatient(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());
    
    const btn = event.target.querySelector('.btn-save-patient');
    if (!btn) return;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    
    fetch('save_patient.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            selectedPatientId = data.patient_id;
            closeAddParticipantModal();
            // Buka modal pilih layanan
            openServicesModal(data.patient_id, data.nama_lengkap);
        } else {
            alert('Gagal menyimpan: ' + (data.message || ''));
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Simpan Pasien';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Simpan Pasien';
    });
}

// Open services modal
function openServicesModal(patientId, patientName) {
    selectedPatientId = patientId;
    
    const selectedInfo = document.getElementById('selectedPatientInfo');
    if (selectedInfo) {
        selectedInfo.innerHTML = `
            <div class="selected-patient-badge">
                <i class="fas fa-check-circle"></i>
                Pasien: <strong>${escapeHtml(patientName)}</strong>
            </div>
        `;
    }
    
    const servicesModal = document.getElementById('selectServicesModal');
    if (servicesModal) {
        servicesModal.style.display = 'flex';
    }
    
    loadServices();
}

// Close services modal
function closeServicesModal() {
    const modal = document.getElementById('selectServicesModal');
    if (modal) modal.style.display = 'none';
    clearSelectedPatient();
}

// Close modals when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addParticipantModal');
    const servicesModal = document.getElementById('selectServicesModal');
    
    if (event.target == addModal) {
        closeAddParticipantModal();
    }
    if (event.target == servicesModal) {
        closeServicesModal();
    }
}