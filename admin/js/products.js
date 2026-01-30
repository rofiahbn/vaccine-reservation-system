// Product Form JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Image upload functionality
    const uploadBox = document.getElementById('uploadBox');
    const productImage = document.getElementById('productImage');
    const imagePreview = document.getElementById('imagePreview');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const imageData = document.getElementById('imageData');

    // Click on upload box to trigger file input
    if (uploadBox) {
        uploadBox.addEventListener('click', function() {
            productImage.click();
        });
    }

    // Handle image selection
    if (productImage) {
        productImage.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('File harus berupa gambar');
                    return;
                }

                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file maksimal 5MB');
                    return;
                }

                // Read and preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    if (uploadPlaceholder) {
                        uploadPlaceholder.style.display = 'none';
                    }
                    // Store base64 data
                    imageData.value = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Form submission
    const productForm = document.getElementById('productForm');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get keterangan/description
            const keterangan = document.getElementById('keterangan').value;
            
            // Create FormData
            const formData = new FormData(productForm);
            formData.append('deskripsi', keterangan);

            // Show loading state
            const submitBtn = productForm.querySelector('.btn-submit');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            submitBtn.textContent = 'Menyimpan...';

            // Submit form
            fetch('save_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showAlert('success', data.message);
                    
                    // Redirect after 1 second
                    setTimeout(() => {
                        window.location.href = 'products.php';
                    }, 1000);
                } else {
                    // Show error message
                    showAlert('error', data.message || 'Terjadi kesalahan');
                    
                    // Reset button
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    submitBtn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Terjadi kesalahan saat menyimpan data');
                
                // Reset button
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
                submitBtn.textContent = originalText;
            });
        });
    }

    // Number input validation (prevent negative numbers)
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
    });
});

// Show alert message
function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());

    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    
    const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
    alert.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
    `;

    // Insert at top of form container
    const formContainer = document.querySelector('.form-container');
    formContainer.insertBefore(alert, formContainer.firstChild);

    // Auto remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Format currency input (optional enhancement)
function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    if (value) {
        value = parseInt(value).toLocaleString('id-ID');
        input.value = value;
    }
}

// Auto-calculate discount (optional)
function calculateDiscount() {
    const hargaStandard = parseFloat(document.querySelector('input[name="harga"]').value) || 0;
    const diskon = parseFloat(document.querySelector('input[name="harga_diskon"]').value) || 0;
    
    if (hargaStandard > 0 && diskon > 0) {
        const persentase = ((diskon / hargaStandard) * 100).toFixed(2);
        console.log(`Diskon: ${persentase}%`);
    }
}