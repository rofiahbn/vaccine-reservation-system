// Buka popup
function openCetakSuratPopup() {
    document.getElementById('popupCetakSurat').classList.add('active');
    loadExistingSurat();
}

// Tutup popup
function closeCetakSuratPopup() {
    document.getElementById('popupCetakSurat').classList.remove('active');
}

// Load surat yang sudah ada
function loadExistingSurat() {
    fetch('get_surat_list.php?booking_id=' + bookingId)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('suratList');
            
            if (data.success && data.surat.length > 0) {
                let html = '';
                data.surat.forEach(s => {
                    const jenisLabel = {
                        'vaksin': 'Sertifikat Vaksin',
                        'sakit': 'Surat Keterangan Sakit',
                        'sehat': 'Surat Keterangan Sehat'
                    };
                    
                    html += `
                        <div class="surat-item">
                            <div class="surat-info">
                                <strong>${jenisLabel[s.jenis_surat] || s.jenis_surat}</strong>
                                <small>${s.tanggal_surat} • ${s.dokter_nama}</small>
                            </div>
                            <button class="btn-lihat-surat" onclick="window.open('../uploads/surat/${s.file_pdf}', '_blank')">
                                <i class="fas fa-print"></i> Cetak
                            </button>
                        </div>
                    `;
                });
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="empty-surat">Belum ada surat yang dibuat untuk booking ini</div>';
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('suratList').innerHTML = '<div class="empty-surat">Gagal memuat data</div>';
        });
}

// Buat surat baru - langsung redirect ke proses_tindakan
function buatSuratBaru() {
    window.location.href = `proses_tindakan.php?id=${bookingId}`;
}