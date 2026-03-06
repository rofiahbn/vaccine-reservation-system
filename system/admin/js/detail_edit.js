/**
 * detail_edit.js - Fungsi untuk mengelola edit booking dan dropdown
 */

// Fungsi untuk menampilkan/sembunyikan dropdown
function toggleEditDropdown() {
    const btn = document.getElementById('btnEditMain');
    const dropdown = document.getElementById('editOptions');
    const editWrapper = document.querySelector('.edit-wrapper');
    
    if (!btn || !dropdown || !editWrapper) return;
    
    const isVisible = dropdown.style.display === 'block';
    
    // Sembunyikan semua dropdown lain (jika ada)
    document.querySelectorAll('.edit-dropdown').forEach(d => {
        d.style.display = 'none';
    });
    
    // Toggle class untuk arrow
    if (isVisible) {
        btn.classList.remove('dropdown-open');
        dropdown.style.display = 'none';
    } else {
        btn.classList.add('dropdown-open');
        
        // Tampilkan dropdown
        dropdown.style.display = 'block';
        dropdown.style.zIndex = '1050';
        
        // Pastikan posisi benar
        dropdown.style.position = 'absolute';
        dropdown.style.top = '100%';
        dropdown.style.right = '0';
        dropdown.style.left = 'auto';
        dropdown.style.marginTop = '10px';
        
        // Cek apakah dropdown keluar layar
        const dropdownRect = dropdown.getBoundingClientRect();
        const windowWidth = window.innerWidth;
        
        if (dropdownRect.right > windowWidth) {
            dropdown.style.right = 'auto';
            dropdown.style.left = '0';
        }
    }
}

// Tutup dropdown saat klik di luar
function setupDropdownClose() {
    document.addEventListener('click', function(e) {
        const editWrapper = document.querySelector('.edit-wrapper');
        const dropdown = document.getElementById('editOptions');
        const btn = document.getElementById('btnEditMain');
        
        if (!editWrapper || !dropdown || !btn) return;
        
        // Jika klik di luar wrapper dan dropdown
        if (!editWrapper.contains(e.target)) {
            dropdown.style.display = 'none';
            btn.classList.remove('dropdown-open');
        }
    });
    
    // Tutup dropdown saat tekan ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const dropdown = document.getElementById('editOptions');
            const btn = document.getElementById('btnEditMain');
            
            if (dropdown) {
                dropdown.style.display = 'none';
            }
            if (btn) {
                btn.classList.remove('dropdown-open');
            }
        }
    });
}

// Hover effect untuk tombol edit
function setupEditButtonHover() {
    const btn = document.getElementById('btnEditMain');
    if (!btn) return;
    
    // Hover effect
    btn.addEventListener('mouseenter', function() {
        if (!this.disabled) {
            this.style.backgroundColor = '#45a049';
        }
    });
    
    btn.addEventListener('mouseleave', function() {
        if (!this.disabled) {
            this.style.backgroundColor = '#4CAF50';
        }
    });
}

// Animasi untuk dropdown items
function setupDropdownItemsAnimation() {
    const dropdownItems = document.querySelectorAll('.edit-dropdown-item');
    
    dropdownItems.forEach((item) => {
        // Efek hover
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
            this.style.transition = 'transform 0.2s ease, background 0.2s ease';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
        
        // Tambahkan efek klik
        item.addEventListener('click', function() {
            // Tambah efek active
            this.style.backgroundColor = '#e6f7ff';
            
            // Reset semua item lain
            dropdownItems.forEach(otherItem => {
                if (otherItem !== this) {
                    otherItem.style.backgroundColor = '';
                }
            });
        });
    });
}

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    console.log("detail_edit.js loaded");
    
    // Setup tombol edit
    const editBtn = document.getElementById('btnEditMain');
    if (editBtn) {
        editBtn.addEventListener('click', toggleEditDropdown);
        setupEditButtonHover();
    }
    
    // Setup untuk menutup dropdown
    setupDropdownClose();
    
    // Setup animasi dropdown items
    setupDropdownItemsAnimation();
    
    // Handler untuk resize window - reposisi dropdown
    window.addEventListener('resize', function() {
        const dropdown = document.getElementById('editOptions');
        const btn = document.getElementById('btnEditMain');
        
        if (dropdown && dropdown.style.display === 'block') {
            // Reposisi dropdown
            dropdown.style.display = 'none';
            if (btn) btn.classList.remove('dropdown-open');
            
            // Tampilkan lagi dengan posisi baru
            setTimeout(() => {
                if (btn) btn.classList.add('dropdown-open');
                dropdown.style.display = 'block';
            }, 10);
        }
    });
});