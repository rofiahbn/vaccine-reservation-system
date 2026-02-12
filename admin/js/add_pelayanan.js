/* ============================================
   ADD_PELAYANAN.JS - Form Tambah Layanan & Paket
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    
    // ========== INITIALIZE ==========
    const tipe = CURRENT_TIPE;
    
    // Load product options dari JSON
    let productOptions = [];
    try {
        productOptions = JSON.parse(document.getElementById('product-options-data').textContent);
    } catch(e) {
        console.error('Error parsing product options:', e);
    }
    
    // Load service options dari JSON
    let serviceOptions = [];
    try {
        serviceOptions = JSON.parse(document.getElementById('service-options-data').textContent);
    } catch(e) {
        console.error('Error parsing service options:', e);
    }
    
    // ========== UNTUK LAYANAN ==========
    if (tipe === 'pelayanan') {
        initServiceForm(productOptions);
    }
    
    // ========== UNTUK PAKET ==========
    if (tipe === 'paket') {
        initPackageForm(serviceOptions);
    }
    
    // ========== INIT HARGA PAKET ==========
    const hargaInput = document.querySelector('input[name="harga"]');
    if (hargaInput) {
        hargaInput.addEventListener('input', function() {
            const hargaPaketDisplay = document.getElementById('hargaPaketDisplay');
            if (hargaPaketDisplay) {
                const value = parseInt(this.value) || 0;
                hargaPaketDisplay.innerHTML = 'Rp ' + formatRupiah(value);
            }
        });
    }
});

/* ========== FUNGSI UNTUK LAYANAN ========== */
function initServiceForm(productOptions) {
    const container = document.getElementById('components-container');
    
    // Tambah 2 komponen default
    addComponent(productOptions, true); // Vaksin
    addComponent(productOptions, false, 'Jasa Tenaga Medis', 'jasa'); // Jasa
    
    // Event listener untuk tombol tambah
    document.getElementById('btnAddComponent').addEventListener('click', function() {
        addComponent(productOptions);
    });
}

function addComponent(productOptions, isVaksin = false, customName = '', customType = 'vaksin') {
    const container = document.getElementById('components-container');
    const index = container.children.length;
    
    const componentItem = document.createElement('div');
    componentItem.className = 'component-item';
    
    let selectOptions = '<option value="">-- Pilih Vaksin/Produk --</option>';
    
    // Tambah opsi produk
    productOptions.forEach(prod => {
        selectOptions += `<option value="${prod.id}" data-name="${prod.name}" data-price="${prod.price}">
            ${prod.name} - Rp ${formatRupiah(prod.price)}
        </option>`;
    });
    
    selectOptions += '<option value="custom">+ Tambah Komponen Manual</option>';
    
    // Isi HTML
    let html = `
        <div class="form-row">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Nama Komponen</label>
                <select name="component_id[]" class="form-control component-select" onchange="handleComponentChange(this)">
                    ${selectOptions}
                </select>
                <div class="component-custom-input">
                    <input type="text" name="component_custom[]" class="form-control" placeholder="Nama komponen">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0; width: 100px;">
                <label>Qty</label>
                <input type="number" name="component_qty[]" class="form-control" value="1" min="1" style="text-align: center;">
            </div>
        </div>
        <input type="hidden" name="component_type[]" value="${customType}">
    `;
    
    // Tambah remove button jika bukan komponen default
    if (index > 1) {
        html += `<button type="button" class="remove-btn" onclick="removeComponent(this)">
            <i class="fas fa-times"></i>
        </button>`;
    }
    
    componentItem.innerHTML = html;
    container.appendChild(componentItem);
    
    // Set value jika vaksin
    if (isVaksin && productOptions.length > 0) {
        const select = componentItem.querySelector('select[name="component_id[]"]');
        if (select) {
            select.value = productOptions[0].id;
        }
    }
    
    // Set custom name jika ada
    if (customName) {
        const customInput = componentItem.querySelector('.component-custom-input input');
        const select = componentItem.querySelector('select');
        if (customInput && select) {
            select.value = 'custom';
            select.style.display = 'none';
            customInput.value = customName;
            customInput.parentElement.classList.add('show');
        }
    }
}

function handleComponentChange(select) {
    const row = select.closest('.form-row');
    const customInputContainer = row.querySelector('.component-custom-input');
    const typeInput = row.parentNode.querySelector('input[name="component_type[]"]');
    
    if (select.value === 'custom') {
        // Tampilkan input manual
        if (customInputContainer) {
            customInputContainer.classList.add('show');
        }
        select.style.display = 'none';
        if (typeInput) typeInput.value = 'custom';
    }
}

function removeComponent(btn) {
    const container = document.getElementById('components-container');
    if (container.children.length > 1) {
        btn.closest('.component-item').remove();
    } else {
        alert('Minimal harus ada 1 komponen');
    }
}

/* ========== FUNGSI UNTUK PAKET ========== */
function initPackageForm(serviceOptions) {
    const container = document.getElementById('package-items-container');
    
    // Tambah 1 item default
    addPackageItem(serviceOptions);
    
    // Event listener untuk tombol tambah
    document.getElementById('btnAddPackageItem').addEventListener('click', function() {
        addPackageItem(serviceOptions);
    });
    
    // Hitung total harga awal
    calculateTotalPrice();
}

function addPackageItem(serviceOptions) {
    const container = document.getElementById('package-items-container');
    const index = container.children.length;
    
    const packageItem = document.createElement('div');
    packageItem.className = 'package-item';
    
    let selectOptions = '<option value="">-- Pilih Layanan --</option>';
    
    serviceOptions.forEach(service => {
        selectOptions += `<option value="${service.id}" data-price="${service.price}">
            ${service.name} - Rp ${formatRupiah(service.price)}
        </option>`;
    });
    
    let html = `
        <div class="form-row">
            <div class="form-group" style="margin-bottom: 0; flex: 2;">
                <label>Pilih Layanan</label>
                <select name="package_service_id[]" class="form-control" required onchange="calculateTotalPrice()">
                    ${selectOptions}
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; width: 120px;">
                <label>Kunjungan ke-</label>
                <input type="number" name="package_visit_order[]" class="form-control" value="${index + 1}" min="1" style="text-align: center;" onchange="calculateTotalPrice()">
            </div>
            <div class="form-group" style="margin-bottom: 0; width: 100px;">
                <label>Jumlah</label>
                <input type="number" name="package_qty[]" class="form-control" value="1" min="1" style="text-align: center;" onchange="calculateTotalPrice()">
            </div>
        </div>
    `;
    
    // Tambah remove button jika bukan item pertama
    if (index > 0) {
        html += `<button type="button" class="remove-btn" onclick="removePackageItem(this)">
            <i class="fas fa-times"></i>
        </button>`;
    }
    
    packageItem.innerHTML = html;
    container.appendChild(packageItem);
}

function removePackageItem(btn) {
    const container = document.getElementById('package-items-container');
    if (container.children.length > 1) {
        btn.closest('.package-item').remove();
        calculateTotalPrice();
    } else {
        alert('Minimal harus ada 1 item paket');
    }
}

function calculateTotalPrice() {
    let total = 0;
    const items = document.querySelectorAll('.package-item');
    
    items.forEach(item => {
        const select = item.querySelector('select[name="package_service_id[]"]');
        const qty = item.querySelector('input[name="package_qty[]"]')?.value || 1;
        
        if (select && select.selectedOptions && select.selectedOptions[0]) {
            const price = select.selectedOptions[0].dataset.price;
            if (price) {
                total += parseInt(price) * parseInt(qty);
            }
        }
    });
    
    const totalNormalPrice = document.getElementById('totalNormalPrice');
    if (totalNormalPrice) {
        totalNormalPrice.innerHTML = 'Rp ' + formatRupiah(total);
    }
    
    // Update harga paket
    const hargaInput = document.querySelector('input[name="harga"]');
    const hargaPaketDisplay = document.getElementById('hargaPaketDisplay');
    if (hargaInput && hargaPaketDisplay) {
        const hargaPaket = parseInt(hargaInput.value) || 0;
        hargaPaketDisplay.innerHTML = 'Rp ' + formatRupiah(hargaPaket);
    }
}

/* ========== HELPER FUNCTIONS ========== */
function formatRupiah(angka) {
    if (angka === undefined || angka === null) return '0';
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Export functions ke global scope
window.handleComponentChange = handleComponentChange;
window.removeComponent = removeComponent;
window.removePackageItem = removePackageItem;
window.calculateTotalPrice = calculateTotalPrice;