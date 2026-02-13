// ============================================
// SERVICE.JS - Dengan Tabs Layanan & Paket
// ============================================

let selectedProducts = (typeof editSelectedProducts !== 'undefined') 
    ? editSelectedProducts 
    : [];

let activeTab = 'layanan';

// Render tab navigation
function renderTabs() {
    const container = document.getElementById('productTabsContainer');
    if (!container) return;
    
    const tabsHtml = `
        <div class="product-tabs">
            <button class="tab-btn ${activeTab === 'layanan' ? 'active' : ''}" onclick="switchTab('layanan')">
                <i class="fas fa-stethoscope"></i> Layanan
                <span class="tab-count">${typeof rawLayanan !== 'undefined' ? rawLayanan.length : 0}</span>
            </button>
            <button class="tab-btn ${activeTab === 'paket' ? 'active' : ''}" onclick="switchTab('paket')">
                <i class="fas fa-boxes"></i> Paket
                <span class="tab-count">${typeof rawPaket !== 'undefined' ? rawPaket.length : 0}</span>
            </button>
        </div>
    `;
    
    container.innerHTML = tabsHtml;
}

// Switch tab
function switchTab(tab) {
    activeTab = tab;
    renderTabs();
    renderCategories();
    
    const searchInput = document.getElementById('searchLayanan');
    if (searchInput) {
        searchInput.placeholder = tab === 'layanan' 
            ? '🔍 Cari layanan...' 
            : '🔍 Cari paket...';
        searchInput.value = '';
    }
    
    updateBadges();
}

// Escape string
function escapeJS(str) {
    if (!str) return '';
    return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

// Icon berdasarkan kategori
function getCategoryIcon(category, tipe) {
    if (tipe === 'paket') return 'box';
    const icons = { 
        'Anak': 'child', 
        'Dewasa': 'user', 
        'Semua Usia': 'users' 
    };
    return icons[category] || 'tag';
}

// Cek apakah kategori harus di-expand
function shouldExpandCategory(category, products, tipe) {
    return products.some(p => 
        selectedProducts.some(sp => sp.id == p.id && sp.tipe === tipe)
    );
}

// Render kategori accordion
function renderCategories() {
    const accordion = document.getElementById('categoryAccordion');
    if (!accordion) {
        console.warn('service.js: categoryAccordion element not found');
        return;
    }
    
    // ✅ Check if data exists
    const currentData = activeTab === 'layanan' ? productDataLayanan : productDataPaket;
    const dataType = activeTab === 'layanan' ? 'Layanan' : 'Paket';
    
    // ⚠️ Data belum ready
    if (typeof currentData === 'undefined') {
        console.warn(`service.js: ${dataType} data not ready yet`);
        accordion.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Memuat ${dataType}...</p>
            </div>
        `;
        return;
    }
    
    if (!currentData || Object.keys(currentData).length === 0) {
        accordion.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-${activeTab === 'layanan' ? 'stethoscope' : 'boxes'}"></i>
                <p>Belum ada ${dataType} tersedia</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    let sortedCategories = Object.keys(currentData).sort();
    
    if (activeTab === 'layanan') {
        const kategoriOrder = ['Anak', 'Dewasa', 'Semua Usia'];
        sortedCategories = Object.keys(currentData).sort((a, b) => {
            const indexA = kategoriOrder.indexOf(a);
            const indexB = kategoriOrder.indexOf(b);
            if (indexA === -1 && indexB === -1) return a.localeCompare(b);
            if (indexA === -1) return 1;
            if (indexB === -1) return -1;
            return indexA - indexB;
        });
    }
    
    sortedCategories.forEach(category => {
        const categoryId = `cat-${activeTab}-${category.replace(/\s+/g, '-')}`;
        const iconId = `icon-${activeTab}-${category.replace(/\s+/g, '-')}`;
        const products = currentData[category] || [];
        
        const selectedCount = products.filter(p => 
            selectedProducts.some(sp => sp.id == p.id && sp.tipe === activeTab)
        ).length;
        
        const isExpanded = shouldExpandCategory(category, products, activeTab);
        
        html += `
            <div class="category-item" data-category="${category}" data-tab="${activeTab}">
                <div class="category-header" onclick="toggleCategory('${category}', '${activeTab}')">
                    <div class="category-title">
                        <i class="fas fa-${getCategoryIcon(category, activeTab)}"></i>
                        <span>${category}</span>
                        ${selectedCount > 0 ? `<span class="category-badge">${selectedCount}</span>` : ''}
                    </div>
                    <i class="fas fa-chevron-down" id="${iconId}" style="transform: ${isExpanded ? 'rotate(180deg)' : 'rotate(0deg)'};"></i>
                </div>
                <div class="category-content" id="${categoryId}" style="display: ${isExpanded ? 'block' : 'none'};">
        `;
        
        products.forEach(product => {
            const isChecked = selectedProducts.some(p => p.id == product.id && p.tipe === activeTab);
            const productId = `prod-${activeTab}-${product.id}`;
            
            html += `
                <div class="product-item">
                    <label class="product-checkbox" for="${productId}">
                        <input 
                            type="checkbox" 
                            id="${productId}"
                            value="${product.id}" 
                            data-tipe="${activeTab}"
                            data-price="${product.price}"
                            ${isChecked ? 'checked' : ''}
                            onchange="toggleProduct(${product.id}, '${escapeJS(product.name)}', ${product.price}, '${activeTab}')"
                        >
                        <span class="checkmark"></span>
                        <span class="product-name">${product.name}</span>
                    </label>
                    <div class="product-meta">
                        ${product.kode_paket ? `<span class="product-code">${product.kode_paket}</span>` : ''}
                        ${product.kode_layanan ? `<span class="product-code">${product.kode_layanan}</span>` : ''}
                        <span class="product-price">Rp ${formatRupiah(product.price)}</span>
                    </div>
                </div>
            `;
        });
        
        html += `</div></div>`;
    });
    
    accordion.innerHTML = html;
}

// Toggle kategori
function toggleCategory(category, tipe) {
    const categoryId = `cat-${tipe}-${category.replace(/\s+/g, '-')}`;
    const content = document.getElementById(categoryId);
    const icon = document.getElementById(`icon-${tipe}-${category.replace(/\s+/g, '-')}`);
    
    if (!content || !icon) return;
    
    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

// Toggle product selection
function toggleProduct(id, name, price, tipe) {
    const index = selectedProducts.findIndex(p => p.id == id && p.tipe === tipe);
    
    if (index > -1) {
        selectedProducts.splice(index, 1);
    } else {
        selectedProducts.push({ 
            id: parseInt(id), 
            name: name, 
            price: parseInt(price), 
            tipe: tipe 
        });
    }
    
    // Update UI
    updateCategoryBadge(id, tipe);
    updateBadges();
    updateHiddenInput();
    updateButtonState();
    renderCategories();
}

// Update button state
function updateButtonState() {
    const btnSelesai = document.getElementById('btnSelesai');
    const btnTambah = document.getElementById('btnTambahPeserta');
    const btnAddMore = document.getElementById('btnAddMore');
    const btnFinish = document.getElementById('btnFinish');
    
    const hasSelection = 
        selectedProducts.length > 0 &&
        document.getElementById("selectedDate")?.value &&
        document.getElementById("selectedTime")?.value;
    
    if (btnSelesai) btnSelesai.disabled = !hasSelection;
    if (btnTambah) btnTambah.disabled = !hasSelection;
    if (btnAddMore) btnAddMore.disabled = !hasSelection;
    if (btnFinish) btnFinish.disabled = !hasSelection;
}

// Update category badge
function updateCategoryBadge(productId, tipe) {
    const currentData = tipe === 'layanan' ? productDataLayanan : productDataPaket;
    
    for (let category in currentData) {
        const products = currentData[category];
        if (products.some(p => p.id == productId)) {
            const categoryItem = document.querySelector(`.category-item[data-category="${category}"][data-tab="${tipe}"]`);
            if (!categoryItem) continue;
            
            const titleDiv = categoryItem.querySelector('.category-title');
            const oldBadge = categoryItem.querySelector('.category-badge');
            
            const selectedCount = products.filter(p => 
                selectedProducts.some(sp => sp.id == p.id && sp.tipe === tipe)
            ).length;
            
            if (selectedCount > 0) {
                if (oldBadge) {
                    oldBadge.textContent = selectedCount;
                } else {
                    const badge = document.createElement('span');
                    badge.className = 'category-badge';
                    badge.textContent = selectedCount;
                    titleDiv.appendChild(badge);
                }
            } else {
                if (oldBadge) oldBadge.remove();
            }
            break;
        }
    }
}

// Update badges
function updateBadges() {
    const badgesContainer = document.getElementById('selectedBadges');
    const totalInfo = document.getElementById('totalInfo');
    const totalCount = document.getElementById('totalCount');
    
    if (!badgesContainer) return;
    
    if (selectedProducts.length === 0) {
        badgesContainer.style.display = 'none';
        if (totalInfo) totalInfo.style.display = 'none';
        if (totalCount) totalCount.textContent = '0';
        return;
    }
    
    badgesContainer.style.display = 'flex';
    if (totalInfo) totalInfo.style.display = 'block';
    if (totalCount) totalCount.textContent = selectedProducts.length;
    
    let badgesHTML = '<span class="badges-label">Dipilih:</span>';
    
    selectedProducts.forEach(product => {
        const badgeClass = product.tipe === 'paket' ? 'badge-paket' : 'badge-layanan';
        badgesHTML += `
            <span class="product-badge ${badgeClass}">
                ${product.name}
                <button type="button" onclick="removeProduct(${product.id}, '${product.tipe}')" title="Hapus">×</button>
            </span>
        `;
    });
    
    badgesContainer.innerHTML = badgesHTML;
}

// Remove product
function removeProduct(id, tipe) {
    selectedProducts = selectedProducts.filter(p => !(p.id == id && p.tipe === tipe));
    
    updateCategoryBadge(id, tipe);
    updateBadges();
    updateHiddenInput();
    updateButtonState();
    renderCategories();
}

// Update hidden input
function updateHiddenInput() {
    const input = document.getElementById('selectedProductsInput');
    if (input) {
        input.value = JSON.stringify(selectedProducts);
    }
}

// Format Rupiah
function formatRupiah(angka) {
    if (!angka) return '0';
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Search functionality
function initSearchListener() {
    const searchInput = document.getElementById('searchLayanan');
    if (!searchInput) return;
    
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        let hasAnyMatch = false;
        
        document.querySelectorAll('.category-item').forEach(item => {
            let hasMatch = false;
            const content = item.querySelector('.category-content');
            const icon = item.querySelector('.category-header i.fa-chevron-down');
            
            item.querySelectorAll('.product-item').forEach(product => {
                const nameEl = product.querySelector('.product-name');
                if (!nameEl) return;
                
                const text = nameEl.textContent.toLowerCase();
                if (query === '' || text.includes(query)) {
                    product.style.display = 'flex';
                    if (query !== '') hasMatch = true;
                } else {
                    product.style.display = 'none';
                }
            });
            
            if (query === '') {
                item.style.display = 'block';
            } else if (hasMatch) {
                item.style.display = 'block';
                if (content) {
                    content.style.display = 'block';
                    if (icon) icon.style.transform = 'rotate(180deg)';
                }
                hasAnyMatch = true;
            } else {
                item.style.display = 'none';
            }
        });
        
        const emptyMsg = document.getElementById('empty-search-message');
        if (query !== '' && !hasAnyMatch) {
            if (!emptyMsg) {
                const msg = document.createElement('div');
                msg.id = 'empty-search-message';
                msg.className = 'empty-state';
                msg.style.padding = '30px';
                msg.innerHTML = `
                    <i class="fas fa-search"></i>
                    <p>Tidak ada layanan/paket yang cocok dengan "${query}"</p>
                `;
                document.getElementById('categoryAccordion').appendChild(msg);
            }
        } else if (emptyMsg) {
            emptyMsg.remove();
        }
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('service.js: DOMContentLoaded');
    
    // ✅ Wait for data to be ready
    let retryCount = 0;
    const maxRetries = 50; // 5 seconds (50 * 100ms)
    
    const checkDataReady = setInterval(() => {
        retryCount++;
        
        // ✅ Check if data exists
        if (typeof productDataLayanan !== 'undefined' && 
            typeof productDataPaket !== 'undefined') {
            
            clearInterval(checkDataReady);
            console.log('service.js: Data loaded, initializing UI...');
            console.log('Layanan categories:', Object.keys(productDataLayanan).length);
            console.log('Paket categories:', Object.keys(productDataPaket).length);
            
            // Initialize UI
            renderTabs();
            renderCategories();
            initSearchListener();
            
            setTimeout(() => {
                updateBadges();
                updateHiddenInput();
                updateButtonState();
            }, 100);
            
            return;
        }
        
        // ⚠️ Timeout after max retries
        if (retryCount >= maxRetries) {
            clearInterval(checkDataReady);
            console.error('service.js: Failed to load product data after 5 seconds');
            
            // Show error message to user
            const accordion = document.getElementById('categoryAccordion');
            if (accordion) {
                accordion.innerHTML = `
                    <div class="empty-state" style="background: #fee; border: 1px solid #fcc;">
                        <i class="fas fa-exclamation-triangle" style="color: #c33;"></i>
                        <p style="color: #c33;">Gagal memuat data layanan. Silakan refresh halaman.</p>
                        <button onclick="location.reload()" class="btn btn-primary" style="margin-top: 10px;">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                `;
            }
        }
    }, 100);
});