// ========== PRODUCT SELECTION ==========

// Data produk per kategori
const productData = {
    "Vaksinasi": [
        { id: 1, name: "Vaksin Flu" },
        { id: 2, name: "Vaksin Hepatitis" },
        { id: 3, name: "Vaksin COVID-19" },
        { id: 4, name: "Vaksin Meningitis" },
        { id: 5, name: "Vaksin Typhoid" }
    ],
    "Paket Kesehatan": [
        { id: 6, name: "Infus Vitamin C Booster" },
        { id: 7, name: "Infus Immunity" },
        { id: 8, name: "Paket Premium Imunisasi" },
        { id: 9, name: "Medical Check Up" }
    ],
    "Vitamin": [
        { id: 10, name: "Vitamin B Complex" },
        { id: 11, name: "Vitamin D3" },
        { id: 12, name: "Suntik Vitamin C" }
    ],
    "Obat": [
        { id: 13, name: "Obat Anti Mabuk" },
        { id: 14, name: "Obat Anti Malaria" },
        { id: 15, name: "Obat Diare" }
    ],
    "Swab": [
        { id: 16, name: "Swab Antigen COVID-19" },
        { id: 17, name: "Swab PCR COVID-19" }
    ],
    "Lab": [
        { id: 18, name: "Cek Darah Lengkap" },
        { id: 19, name: "Cek Kolesterol" },
        { id: 20, name: "Cek Gula Darah" }
    ],
    "Servis": [
        { id: 21, name: "Konsultasi Dokter" },
        { id: 22, name: "Pemasangan Infus" }
    ]
};

let selectedProducts = []; // Array untuk nyimpan produk yang dipilih

// Render kategori accordion
function renderCategories() {
    const accordion = document.getElementById('categoryAccordion');
    let html = '';
    
    for (let category in productData) {
        html += `
            <div class="category-item">
                <div class="category-header" onclick="toggleCategory('${category}')">
                    <span>${category}</span>
                    <i class="fas fa-chevron-down" id="icon-${category}"></i>
                </div>
                <div class="category-content" id="content-${category}" style="display:none;">
        `;
        
        // Loop produk dalam kategori
        productData[category].forEach(product => {
            html += `
                <label class="product-checkbox">
                    <input type="checkbox" value="${product.id}" onchange="toggleProduct(${product.id}, '${product.name}')">
                    <span>${product.name}</span>
                </label>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    }
    
    accordion.innerHTML = html;
}

// Toggle kategori (expand/collapse)
function toggleCategory(category) {
    const content = document.getElementById('content-' + category);
    const icon = document.getElementById('icon-' + category);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

// Toggle product selection
function toggleProduct(id, name) {
    const index = selectedProducts.findIndex(p => p.id === id);
    
    if (index > -1) {
        // Hapus dari array
        selectedProducts.splice(index, 1);
    } else {
        // Tambah ke array
        selectedProducts.push({ id: id, name: name });
    }
    
    updateBadges();
    updateHiddenInput();
}

// Update badges
function updateBadges() {
    const badgesContainer = document.getElementById('selectedBadges');
    const totalInfo = document.getElementById('totalInfo');
    const totalCount = document.getElementById('totalCount');
    
    if (selectedProducts.length === 0) {
        badgesContainer.style.display = 'none';
        totalInfo.style.display = 'none';
    } else {
        badgesContainer.style.display = 'flex';
        totalInfo.style.display = 'block';
        totalCount.textContent = selectedProducts.length;
        
        let badgesHTML = '';
        selectedProducts.forEach(product => {
            badgesHTML += `
                <span class="product-badge">
                    ${product.name}
                    <button type="button" onclick="removeProduct(${product.id})">×</button>
                </span>
            `;
        });
        
        badgesContainer.innerHTML = badgesHTML;
    }
}

// Remove product
function removeProduct(id) {
    // Uncheck checkbox
    const checkbox = document.querySelector(`input[value="${id}"]`);
    if (checkbox) checkbox.checked = false;
    
    // Remove from array
    selectedProducts = selectedProducts.filter(p => p.id !== id);
    
    updateBadges();
    updateHiddenInput();
}

// Update hidden input
function updateHiddenInput() {
    const input = document.getElementById('selectedProductsInput');
    input.value = JSON.stringify(selectedProducts);
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    // Load categories
    renderCategories();
    
    // Search dengan filter kategori
    const searchInput = document.getElementById('searchLayanan');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            // Loop semua kategori
            document.querySelectorAll('.category-item').forEach(categoryItem => {
                let hasMatch = false;
                const content = categoryItem.querySelector('.category-content');
                const header = categoryItem.querySelector('.category-header');
                const icon = categoryItem.querySelector('.category-header i');
                
                // Loop produk dalam kategori
                categoryItem.querySelectorAll('.product-checkbox').forEach(label => {
                    const text = label.textContent.toLowerCase();
                    if (query === '' || text.includes(query)) {
                        label.style.display = 'flex';
                        if (query !== '') hasMatch = true;
                    } else {
                        label.style.display = 'none';
                    }
                });
                
                // Show/hide kategori berdasarkan hasil search
                if (query === '') {
                    categoryItem.style.display = 'block';
                } else if (hasMatch) {
                    categoryItem.style.display = 'block';
                    // Auto expand kategori kalau ada match
                    content.style.display = 'block';
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    categoryItem.style.display = 'none';
                }
            });
        });
    }
});