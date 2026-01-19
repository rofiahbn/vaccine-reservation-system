// ========== PRODUCT SELECTION ==========

// Data produk per kategori
const productData = {
    "Vaksinasi": [
        { id: 1, name: "Adacel (Sanofi)" },
        { id: 2, name: "Arexvy (GSK)" },
        { id: 3, name: "Avaxim 160 (Sanofi)" },
        { id: 4, name: "Avaxim 80 (Sanofi)" },
        { id: 5, name: "BCG (Biofarma)" },
        { id: 6, name: "Boostrix (GSK)" },
        { id: 7, name: "bOPV Polio (Biofarma)" },
        { id: 8, name: "Campak (Biofarma)" },
        { id: 9, name: "Cervarix (GSK)" },
        { id: 10, name: "Engerix B 10mcg (GSK)" },
        { id: 11, name: "Engerix B 20mcg (GSK)" },
        { id: 12, name: "Euvax B Adult (Sanofi)" },
        { id: 13, name: "Euvax B Pediatric (Sanofi)" },
        { id: 14, name: "Fluarix Tetra (GSK)" },
        { id: 15, name: "Formening (Mersi)" },
        { id: 16, name: "Gardasil (MSD)" },
        { id: 17, name: "Gardasil 9 (MSD)" },
        { id: 18, name: "Havrix 1440 (GSK)" },
        { id: 19, name: "Havrix 720 (GSK)" },
        { id: 20, name: "Hepatitis B Dewasa (Biofarma)" },
        { id: 21, name: "Hexaxim (Sanofi)" },
        { id: 22, name: "Imojev (Sanofi)" },
        { id: 23, name: "Infanrix Hexa (GSK)" },
        { id: 24, name: "Influvac Tetra (Abbott)" },
        { id: 25, name: "Inlive (Sinovac)" },
        { id: 26, name: "IPV (Biofarma)" },
        { id: 27, name: "MMR II (MSD)" },
        { id: 28, name: "MR (Biofarma)" },
        { id: 29, name: "Menactra (Sanofi)" },
        { id: 30, name: "Menivax (Biofarma)" },
        { id: 31, name: "Menquadfi (Sanofi)" },
        { id: 32, name: "Pneumovax 23 (MSD)" },
        { id: 33, name: "Prevenar 13 (Pfizer)" },
        { id: 34, name: "Prevenar 20 (Pfizer)" },
        { id: 35, name: "Proquad (MSD)" },
        { id: 36, name: "Qdenga (Takeda)" },
        { id: 37, name: "Rotarix (GSK)" },
        { id: 38, name: "Rotateq (MSD)" },
        { id: 39, name: "Shingrix (GSK)" },
        { id: 40, name: "Stamaril (Sanofi)" },
        { id: 41, name: "Synflorix (GSK)" },
        { id: 42, name: "Tetraxim (Sanofi)" },
        { id: 43, name: "Twinrix (GSK)" },
        { id: 44, name: "Typhim Vi (Sanofi)" },
        { id: 45, name: "Varivax (MSD)" },
        { id: 46, name: "Vaxigrip Tetra (Sanofi)" },
        { id: 47, name: "Vaxneuvance (MSD)" },
        { id: 48, name: "Vecon Adult (Biofarma)" },
        { id: 49, name: "Verorab (Sanofi)" },
        { id: 50, name: "Vivaxim (Sanofi)" }
    ],

    "Paket Kesehatan": [
        { id: 51, name: "Home Care" },
        { id: 52, name: "Telekonsultasi" },
        { id: 53, name: "Pemeriksaan Dokter" },
        { id: 54, name: "Medical Check Up Lengkap" },
        { id: 55, name: "Medical Check Up Standard" },
        { id: 56, name: "Pemeriksaan Asam Urat" },
        { id: 57, name: "Pemeriksaan Gula Darah" },
        { id: 58, name: "Pemeriksaan Kolesterol" }
    ],

    "Vitamin": [
        { id: 59, name: "Vitamin B Complex" },
        { id: 60, name: "Vitamin D3" },
        { id: 61, name: "Suntik Vitamin C" },
        { id: 62, name: "Vitamin Badan Bugar" },
        { id: 63, name: "Vitamin Bugar Kinclong" },
        { id: 64, name: "Vitamin Jeruk Segar" },
        { id: 65, name: "Vitamin Remaja Abadi" },
        { id: 66, name: "Vitamin Segar Bugar" },
        { id: 67, name: "Vitamin Segar Kinclong" },
        { id: 68, name: "Vitamin Sultan" },
        { id: 69, name: "Vitamin Segar Bugar Ekstra" },
        { id: 70, name: "Vitamin Sultan +" },
        { id: 71, name: "Vitamin Badan Bugar Ekstra" },
        { id: 72, name: "Vitamin Jeruk Segar Ekstra" }
    ],

    "Obat": [
        { id: 73, name: "Pantoprazole 40 mg Vial" },
        { id: 74, name: "Paracetamol 1 g Fl" },
        { id: 75, name: "Tuberculin PPD RT 23 SSI" }
    ],
    "Swab": [
        { id: 76, name: "Swab Antigen COVID-19" },
        { id: 77, name: "Swab PCR COVID-19" }
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