// service.js
document.addEventListener('DOMContentLoaded', function() {
    // Gunakan sortedCategories dari PHP
    if (typeof sortedCategories !== 'undefined') {
        renderServiceCategories(sortedCategories);
        initializeSearch();
        loadSelectedBadges();
    }
});

function renderServiceCategories(categories) {
    const accordion = document.getElementById('categoryAccordion');
    if (!accordion) return;
    
    accordion.innerHTML = '';
    
    Object.keys(categories).forEach((category, index) => {
        const services = categories[category];
        
        // Buat kategori accordion
        const categoryDiv = document.createElement('div');
        categoryDiv.className = 'category-item';
        
        // Header kategori
        const header = document.createElement('div');
        header.className = 'category-header';
        header.onclick = function() {
            this.classList.toggle('active');
            const content = this.nextElementSibling;
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
        };
        
        header.innerHTML = `
            <span class="category-title">${category}</span>
            <span class="category-count">${services.length} layanan</span>
            <span class="category-icon">▼</span>
        `;
        
        // Content kategori
        const content = document.createElement('div');
        content.className = 'category-content';
        content.style.display = index === 0 ? 'block' : 'none'; // Buka kategori pertama
        
        // List layanan
        services.forEach(service => {
            const serviceItem = document.createElement('div');
            serviceItem.className = 'service-item';
            serviceItem.setAttribute('data-id', service.id);
            serviceItem.setAttribute('data-name', service.nama_layanan);
            serviceItem.setAttribute('data-price', service.harga);
            serviceItem.setAttribute('data-type', service.tipe);
            serviceItem.setAttribute('data-kategori-usia', service.kategori_usia);
            
            // Badge untuk tipe
            let typeBadge = '';
            if (service.tipe === 'paket') {
                typeBadge = '<span class="badge badge-paket">Paket</span>';
            }
            
            // Format harga
            const priceFormatted = service.harga > 0 
                ? 'Rp ' + new Intl.NumberFormat('id-ID').format(service.harga) 
                : 'Hubungi admin';
            
            // Deskripsi singkat
            const deskripsi = service.deskripsi 
                ? `<p class="service-description">${service.deskripsi.substring(0, 100)}${service.deskripsi.length > 100 ? '...' : ''}</p>`
                : '';
            
            serviceItem.innerHTML = `
                <div class="service-checkbox">
                    <input type="checkbox" id="service_${service.id}" value="${service.id}">
                </div>
                <div class="service-details">
                    <label for="service_${service.id}" class="service-name">
                        ${service.nama_layanan} ${typeBadge}
                        ${service.kode_layanan ? `<small class="service-code">${service.kode_layanan}</small>` : ''}
                    </label>
                    ${deskripsi}
                    <span class="service-price">${priceFormatted}</span>
                </div>
            `;
            
            content.appendChild(serviceItem);
        });
        
        categoryDiv.appendChild(header);
        categoryDiv.appendChild(content);
        accordion.appendChild(categoryDiv);
    });
    
    // Event listener untuk checkbox
    attachCheckboxListeners();
}

function attachCheckboxListeners() {
    const checkboxes = document.querySelectorAll('.service-item input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedProducts();
        });
    });
}

function initializeSearch() {
    const searchInput = document.getElementById('searchLayanan');
    if (!searchInput) return;
    
    searchInput.addEventListener('keyup', function() {
        const keyword = this.value.toLowerCase();
        const serviceItems = document.querySelectorAll('.service-item');
        
        serviceItems.forEach(item => {
            const name = item.querySelector('.service-name').innerText.toLowerCase();
            if (name.includes(keyword)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
        
        // Sembunyikan kategori yang tidak ada itemnya
        const categories = document.querySelectorAll('.category-item');
        categories.forEach(category => {
            const visibleItems = category.querySelectorAll('.service-item[style="display: flex;"]');
            if (visibleItems.length === 0) {
                category.style.display = 'none';
            } else {
                category.style.display = 'block';
            }
        });
    });
}

function updateSelectedProducts() {
    const selectedItems = [];
    const checkboxes = document.querySelectorAll('.service-item input[type="checkbox"]:checked');
    
    checkboxes.forEach(checkbox => {
        const serviceItem = checkbox.closest('.service-item');
        selectedItems.push({
            id: checkbox.value,
            name: serviceItem.dataset.name,
            price: serviceItem.dataset.price
        });
    });
    
    // Update hidden input
    document.getElementById('selectedProductsInput').value = JSON.stringify(selectedItems);
    
    // Update badge
    updateSelectedBadges(selectedItems);
    
    // Update total count
    document.getElementById('totalCount').innerHTML = selectedItems.length;
    document.getElementById('totalInfo').style.display = selectedItems.length > 0 ? 'block' : 'none';
    
    // Enable/disable buttons
    document.getElementById('btnTambahPeserta').disabled = selectedItems.length === 0;
    document.getElementById('btnSelesai').disabled = selectedItems.length === 0;
}

function updateSelectedBadges(selectedItems) {
    const badgesContainer = document.getElementById('selectedBadges');
    if (!badgesContainer) return;
    
    if (selectedItems.length === 0) {
        badgesContainer.style.display = 'none';
        badgesContainer.innerHTML = '';
        return;
    }
    
    let html = '';
    selectedItems.forEach((item, index) => {
        html += `
            <span class="badge badge-selected">
                ${item.name}
                <i class="fas fa-times" onclick="removeSelectedItem(${index})"></i>
            </span>
        `;
    });
    
    badgesContainer.innerHTML = html;
    badgesContainer.style.display = 'flex';
}

function removeSelectedItem(index) {
    const selectedItems = JSON.parse(document.getElementById('selectedProductsInput').value || '[]');
    selectedItems.splice(index, 1);
    
    // Uncheck checkbox
    if (selectedItems[index]) {
        const checkbox = document.querySelector(`.service-item input[value="${selectedItems[index].id}"]`);
        if (checkbox) checkbox.checked = false;
    }
    
    document.getElementById('selectedProductsInput').value = JSON.stringify(selectedItems);
    updateSelectedBadges(selectedItems);
    document.getElementById('totalCount').innerHTML = selectedItems.length;
    
    if (selectedItems.length === 0) {
        document.getElementById('totalInfo').style.display = 'none';
        document.getElementById('btnTambahPeserta').disabled = true;
        document.getElementById('btnSelesai').disabled = true;
    }
}

function loadSelectedBadges() {
    // Load dari session jika ada
    const savedProducts = document.getElementById('selectedProductsInput').value;
    if (savedProducts && savedProducts !== '[]') {
        const selectedItems = JSON.parse(savedProducts);
        updateSelectedBadges(selectedItems);
        document.getElementById('totalCount').innerHTML = selectedItems.length;
        document.getElementById('totalInfo').style.display = 'block';
        document.getElementById('btnTambahPeserta').disabled = false;
        document.getElementById('btnSelesai').disabled = false;
        
        // Check checkbox yang sesuai
        selectedItems.forEach(item => {
            const checkbox = document.querySelector(`.service-item input[value="${item.id}"]`);
            if (checkbox) checkbox.checked = true;
        });
    }
}