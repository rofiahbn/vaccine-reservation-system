// ===============================================
// PRODUCTS JASA - JavaScript
// ===============================================

let searchTimeout;

// Search with debounce
function handleSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        handleFilter();
    }, 500);
}

// Apply filter
function handleFilter() {
    const search = document.getElementById('searchInput').value;
    const kategori = document.getElementById('kategoriFilter').value;
    window.location.href = `products_jasa.php?search=${encodeURIComponent(search)}&kategori=${encodeURIComponent(kategori)}`;
}

// Reset filters
function resetFilters() {
    window.location.href = 'products_jasa.php';
}

// ===============================================
// MODAL FUNCTIONS
// ===============================================

function tambahJasa() {
    document.getElementById('modalTitle').textContent = 'Tambah Jasa Baru';
    document.getElementById('jasaForm').reset();
    document.getElementById('jasaId').value = '';
    document.getElementById('jasaModal').style.display = 'flex';
}

function editJasa(id) {
    window.location.href = 'edit_jasa.php?id=' + id;
}

function deleteJasa(id, name) {
    if (confirm(`Hapus jasa "${name}"?\nData yang dihapus tidak dapat dikembalikan.`)) {
        fetch('delete_service.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(`Jasa berhasil dihapus`, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Gagal menghapus jasa: ' + (data.message || ''), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan', 'error');
        });
    }
}

function simpanJasa(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());
    data.tipe = 'jasa'; // Set tipe sebagai jasa
    
    const submitBtn = event.target.querySelector('.btn-simpan');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    submitBtn.disabled = true;
    
    fetch('save_service.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(
                data.data.id ? 'Jasa berhasil diperbarui' : 'Jasa baru berhasil ditambahkan', 
                'success'
            );
            tutupModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('Gagal menyimpan data: ' + (data.message || ''), 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// ===============================================
// NOTIFICATION
// ===============================================

function showNotification(message, type = 'info') {
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    
    notification.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// ===============================================
// EVENT LISTENERS
// ===============================================

// Auto-filter saat ketik
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleFilter();
            }
        });
        
        searchInput.addEventListener('input', handleSearch);
    }
});