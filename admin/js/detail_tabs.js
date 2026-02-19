/**
 * detail_tabs.js - Fungsi untuk mengelola tab peserta
 */

// Fungsi untuk ganti tab peserta
function showParticipant(index) {
    try {
        console.log("Menampilkan peserta ke-" + (index + 1));
        
        // Semua panel peserta
        const panels = document.querySelectorAll('.participant-panel');
        const tabs = document.querySelectorAll('.participant-tab:not(.add)'); // exclude tombol tambah
        
        // Nonaktifkan semua
        panels.forEach(panel => {
            panel.classList.remove('active');
        });
        
        tabs.forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Aktifkan yang dipilih
        if (panels[index]) {
            panels[index].classList.add('active');
        }
        
        if (tabs[index]) {
            tabs[index].classList.add('active');
        }
        
        console.log("Panel aktif:", panels[index]?.id);
    } catch (error) {
        console.error("Error di showParticipant:", error);
    }
}

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    console.log("Halaman detail dimuat");
    
    // Pastikan tab pertama aktif
    const firstPanel = document.querySelector('.participant-panel');
    if (firstPanel && !firstPanel.classList.contains('active')) {
        firstPanel.classList.add('active');
    }
    
    // Tambah event listener untuk semua tab
    const tabs = document.querySelectorAll('.participant-tab:not(.add)');
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', function() {
            showParticipant(index);
        });
    });
    
    // Event listener untuk tombol tambah peserta
    const addBtn = document.querySelector('.participant-tab.add');
    if (addBtn) {
        addBtn.addEventListener('click', addParticipant);
    }
});