// ===================== VARIABLES GLOBAL =====================
    let paymentMethods = [];
    let totalTagihan = window.paymentData.totalTagihan;
    let sudahDibayar = window.paymentData.sudahDibayar;
    let sisaTagihan = window.paymentData.sisaTagihan;
    let subtotal = window.paymentData.subtotal;
    let totalDiskonItem = window.paymentData.totalDiskonItem;

    let diskonTotalType = 'persen';
    let diskonTotal = 0;
    let currentItemIndex = null;
    let currentItemHarga = 0;
    let currentDiskonType = 'persen';

    // Inisialisasi diskonItems dari data PHP
    let diskonItems = {};

    window.paymentData.diskonItems.forEach((srv, index) => {
        diskonItems[index] = {
            harga: srv.harga,
            diskon: srv.diskon || 0,
            tipe: srv.diskon_tipe || ''
        };
    });

    // ===================== FUNGSI HELPER =====================
    function formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function formatRupiah(number) {
        return `Rp ${formatNumber(number)}`;
    }

    // ===================== DISKON TOTAL =====================
    function hitungDiskonTotal(tipe) {

        let persen = parseFloat(document.getElementById('diskonTotalPersen')?.value) || 0;
        let nominal = parseFloat(document.getElementById('diskonTotalNominal')?.value) || 0;

        if (tipe === 'persen') {

            if (persen > 100) persen = 100;
            if (persen < 0) persen = 0;

            nominal = Math.round(subtotal * persen / 100);

            document.getElementById('diskonTotalNominal').value = nominal || '';

        } else if (tipe === 'nominal') {

            if (nominal > subtotal) nominal = subtotal;
            if (nominal < 0) nominal = 0;

            persen = subtotal > 0
                ? Math.round((nominal / subtotal) * 100)
                : 0;

            document.getElementById('diskonTotalPersen').value = persen || '';
        }

        updatePreviewDiskonTotal();
    }

    function updatePreviewDiskonTotal() {

        const persen = parseFloat(document.getElementById('diskonTotalPersen').value) || 0;
        const nominal = parseFloat(document.getElementById('diskonTotalNominal').value) || 0;

        let diskon = 0;

        if (diskonTotalType === 'persen') {
            diskon = Math.round(subtotal * persen / 100);
        } 
        else {
            diskon = nominal;
        }

        const infoDiv = document.getElementById('diskonTotalInfo');

        if (diskon > 0) {
            infoDiv.innerHTML = `
                <span style="color:#10b981;">
                    <i class="fas fa-check-circle"></i>
                    Preview: Diskon Rp ${formatNumber(diskon)}
                </span>
            `;
        } else {
            infoDiv.innerHTML = `
                <i class="fas fa-info-circle"></i>
                Diskon total akan mengurangi dari jumlah tagihan akhir
            `;
        }
    }

    function applyDiskonTotal() {

        const persen = parseFloat(document.getElementById('diskonTotalPersen').value) || 0;
        const nominal = parseFloat(document.getElementById('diskonTotalNominal').value) || 0;

        if (diskonTotalType === 'persen') {
            diskonTotal = Math.round(subtotal * persen / 100);
        } 
        else {
            diskonTotal = nominal;
        }

        document.getElementById("diskonTotalInput").value = diskonTotal;

        updateSummary();

        showToast('Diskon total berhasil diterapkan!', 'success');

        document.getElementById("btnEditDiskonTotal").style.display = "inline-block";
        document.getElementById("btnRemoveDiskonTotal").style.display = "inline-block";

    }

    // ===================== DISKON PER ITEM =====================
    function openDiskonItem(index, harga, tipe, diskon, itemName) {
        currentItemIndex = index;
        currentItemHarga = harga;
        currentDiskonType = tipe || 'persen';
        
        // Set nilai modal
        document.getElementById('currentItemIndex').value = index;
        document.getElementById('currentItemHarga').value = harga;
        document.getElementById('itemName').textContent = itemName || 'Layanan';
        document.getElementById('itemHarga').textContent = formatRupiah(harga);
        document.getElementById('originalPrice').textContent = formatRupiah(harga);
        
        // Set tipe diskon yang aktif
        selectDiskonType(currentDiskonType);
        
        // Set nilai input berdasarkan tipe
        if (currentDiskonType === 'persen' && diskon > 0) {
            const persen = Math.round((diskon / harga) * 100);
            document.getElementById('inputDiskonPersen').value = persen;
            hitungDiskonFromPersen();
        } else if (currentDiskonType === 'nilai' && diskon > 0) {
            document.getElementById('inputDiskonNilai').value = diskon;
            hitungPersenFromDiskon();
        } else {
            resetDiskonInputs();
        }
        
        document.getElementById('popupDiskonItem').style.display = 'flex';
    }

    function closeDiskonItemPopup() {
        document.getElementById('popupDiskonItem').style.display = 'none';
        resetDiskonInputs();
    }

    function selectDiskonType(type) {
        currentDiskonType = type;
        
        // Update UI
        document.querySelectorAll('.type-option').forEach(option => {
            option.classList.remove('active');
            if (option.dataset.type === type) {
                option.classList.add('active');
            }
        });
        
        // Show/hide input sections
        document.querySelectorAll('.diskon-input-section').forEach(section => {
            section.classList.remove('active');
        });
        
        if (type === 'persen') {
            document.getElementById('diskonPersenSection').classList.add('active');
        } else {
            document.getElementById('diskonNilaiSection').classList.add('active');
        }
        
        // Reset inputs
        document.getElementById('inputDiskonPersen').value = '';
        document.getElementById('inputDiskonNilai').value = '';
        hitungDiskonFromPersen();
    }

    function hitungDiskonFromPersen() {
        const persenInput = document.getElementById('inputDiskonPersen');
        let persen = parseFloat(persenInput.value) || 0;
        
        // Validasi
        if (persen > 100) {
            persen = 100;
            persenInput.value = 100;
        }
        if (persen < 0) {
            persen = 0;
            persenInput.value = 0;
        }
        
        const diskon = Math.round(currentItemHarga * persen / 100);
        
        // Update display
        document.getElementById('nilaiDiskonPersen').textContent = formatRupiah(diskon);
        document.getElementById('appliedDiskon').textContent = `- ${formatRupiah(diskon)}`;
        document.getElementById('finalPrice').textContent = formatRupiah(currentItemHarga - diskon);
        
        // Update input nilai
        document.getElementById('inputDiskonNilai').value = diskon;
        document.getElementById('persenDiskonNilai').textContent = `${persen}%`;
    }

    function hitungPersenFromDiskon() {
        const nilaiInput = document.getElementById('inputDiskonNilai');
        let diskon = parseFloat(nilaiInput.value) || 0;
        
        // Validasi
        if (diskon > currentItemHarga) {
            diskon = currentItemHarga;
            nilaiInput.value = currentItemHarga;
        }
        if (diskon < 0) {
            diskon = 0;
            nilaiInput.value = 0;
        }
        
        const persen = diskon > 0 ? Math.round((diskon / currentItemHarga) * 100) : 0;
        
        // Update display
        document.getElementById('persenDiskonNilai').textContent = `${persen}%`;
        document.getElementById('appliedDiskon').textContent = `- ${formatRupiah(diskon)}`;
        document.getElementById('finalPrice').textContent = formatRupiah(currentItemHarga - diskon);
        
        // Update input persen
        document.getElementById('inputDiskonPersen').value = persen;
        document.getElementById('nilaiDiskonPersen').textContent = formatRupiah(diskon);
    }

    function resetDiskonInputs() {
        document.getElementById('inputDiskonPersen').value = '';
        document.getElementById('inputDiskonNilai').value = '';
        document.getElementById('nilaiDiskonPersen').textContent = 'Rp 0';
        document.getElementById('persenDiskonNilai').textContent = '0%';
        document.getElementById('appliedDiskon').textContent = '- Rp 0';
        document.getElementById('finalPrice').textContent = formatRupiah(currentItemHarga);
    }

    function applyDiskonItem() {
        let diskon = 0;
        let tipe = currentDiskonType;
        
        if (tipe === 'persen') {
            const persen = parseFloat(document.getElementById('inputDiskonPersen').value) || 0;
            diskon = Math.round(currentItemHarga * persen / 100);
        } else {
            diskon = parseFloat(document.getElementById('inputDiskonNilai').value) || 0;
        }
        
        // Validasi maksimal diskon
        if (diskon > currentItemHarga) {
            alert('Diskon tidak boleh lebih besar dari harga item');
            return;
        }
        
        // Simpan ke diskonItems
        diskonItems[currentItemIndex] = {
            harga: currentItemHarga,
            diskon: diskon,
            tipe: tipe
        };
        
        // Update UI di tabel
        updateDiskonItemUI(currentItemIndex, diskon, tipe);
        
        // Update total
        updateTotalSummary();
        
        // Close modal
        closeDiskonItemPopup();
        
        // Show success message
        showToast('Diskon berhasil diterapkan!', 'success');
    }

    function removeDiskonItem() {
        // Hapus diskon dari item
        diskonItems[currentItemIndex] = {
            harga: currentItemHarga,
            diskon: 0,
            tipe: ''
        };
        
        // Update UI di tabel
        updateDiskonItemUI(currentItemIndex, 0, '');
        
        // Update total
        updateTotalSummary();
        
        // Close modal
        closeDiskonItemPopup();
        
        // Show message
        showToast('Diskon berhasil dihapus!', 'info');
    }

    function updateDiskonItemUI(index, diskon, tipe) {
        const diskonCell = document.getElementById(`diskon-cell-${index}`);
        const totalCell = document.getElementById(`total-item-${index}`);
        
        // Update hidden inputs
        document.getElementById(`service_diskon_${index}`).value = diskon;
        document.getElementById(`service_diskon_tipe_${index}`).value = tipe;
        
        if (diskon > 0) {
            const persen = Math.round((diskon / currentItemHarga) * 100);
            
            let diskonHTML = `
                <div class="diskon-applied">
                    <div style="display: flex; align-items: center; gap: 8px;">
            `;
            
            if (tipe === 'persen') {
                diskonHTML += `
                    <span class="diskon-badge persen">${persen}%</span>
                    <span style="font-size: 12px; color: #64748b;">
                        (Rp ${formatNumber(diskon)})
                    </span>
                `;
            } else {
                diskonHTML += `
                    <span class="diskon-badge nilai">- Rp ${formatNumber(diskon)}</span>
                `;
            }
            
            diskonHTML += `
                    </div>
                </div>
            `;
            
            diskonCell.innerHTML = `
                ${diskonHTML}

                <button type="button"
                    class="btn-edit-diskon"
                    onclick="openDiskonItem(${index}, ${currentItemHarga}, '${tipe}', ${diskon})"
                    title="Edit Diskon">
                    <i class="fas fa-edit"></i>
                </button>
            `;
        } else {
            diskonCell.innerHTML = `
                <span class="no-diskon">-</span>

                <button type="button"
                    class="btn-edit-diskon"
                    onclick="openDiskonItem(${index}, ${currentItemHarga}, '', 0)"
                    title="Tambah Diskon">
                    <i class="fas fa-edit"></i>
                </button>
            `;
        }
        
        // Update total per item
        const totalPerItem = currentItemHarga - diskon;
        totalCell.textContent = `Rp ${formatNumber(totalPerItem)}`;
    }

    function updateTotalSummary() {
        // Hitung ulang subtotal dan diskon item
        subtotal = 0;
        totalDiskonItem = 0;
        totalTagihan = 0;
        
        Object.values(diskonItems).forEach(item => {
            subtotal += item.harga;
            totalDiskonItem += item.diskon;
        });
        
        totalTagihan = subtotal - totalDiskonItem - diskonTotal;
        if (totalTagihan < 0) totalTagihan = 0;
        
        sisaTagihan = totalTagihan - sudahDibayar;
        if (sisaTagihan < 0) sisaTagihan = 0;
        
        // Update semua UI
        updateAllUI();
    }

    function updateAllUI() {
        // Update tabel
        const elements = {
            subtotalDisplay: document.getElementById('subtotalDisplay'),
            diskonItemDisplay: document.getElementById('diskonItemDisplay'),
            diskonTotalDisplay: document.getElementById('diskonTotalDisplay'),
            totalTagihan: document.getElementById('totalTagihan'),
            sisaTagihan: document.getElementById('sisaTagihan'),
            totalDiskonItems: document.getElementById('total-diskon-items'),
            totalSemuaItems: document.getElementById('total-semua-items'),
            totalTagihanSummary: document.getElementById('totalTagihanSummary'),
            sisaTagihanSummary: document.getElementById('sisaTagihanSummary'),
            sisaTagihanModal: document.getElementById('sisaTagihanModal'),
            jumlahBayar: document.getElementById('jumlahBayar'),
            totalTagihanInput: document.getElementById('totalTagihanInput'),
            sisaTagihanInput: document.getElementById('sisaTagihanInput'),
            diskonTotalInput: document.getElementById('diskonTotalInput')
        };
        
        // Check if elements exist before updating
        if (elements.subtotalDisplay) {
            elements.subtotalDisplay.textContent = formatRupiah(subtotal);
        }
        if (elements.diskonItemDisplay) {
            elements.diskonItemDisplay.textContent = `- ${formatRupiah(totalDiskonItem)}`;
        }
        if (elements.diskonTotalDisplay) {
            elements.diskonTotalDisplay.textContent = `- ${formatRupiah(diskonTotal)}`;
        }
        if (elements.totalTagihan) {
            elements.totalTagihan.textContent = formatRupiah(totalTagihan);
        }
        if (elements.sisaTagihan) {
            elements.sisaTagihan.textContent = formatRupiah(sisaTagihan);
        }
        
        // Update tabel footer
        if (elements.totalDiskonItems) {
            elements.totalDiskonItems.textContent = `- ${formatRupiah(totalDiskonItem)}`;
        }
        if (elements.totalSemuaItems) {
            elements.totalSemuaItems.textContent = formatRupiah(totalTagihan);
        }
        
        // Update summary grid
        if (elements.totalTagihanSummary) {
            elements.totalTagihanSummary.textContent = formatRupiah(totalTagihan);
        }
        if (elements.sisaTagihanSummary) {
            elements.sisaTagihanSummary.textContent = formatRupiah(sisaTagihan);
        }
        
        // Update modal multiple payment
        if (elements.sisaTagihanModal) {
            elements.sisaTagihanModal.textContent = formatRupiah(sisaTagihan);
        }
        if (elements.jumlahBayar) {
            elements.jumlahBayar.value = sisaTagihan;
            elements.jumlahBayar.max = sisaTagihan;
        }
        if (elements.totalTagihanInput) {
            elements.totalTagihanInput.value = totalTagihan;
        }
        if (elements.sisaTagihanInput) {
            elements.sisaTagihanInput.value = sisaTagihan;
        }
        if (elements.diskonTotalInput) {
            elements.diskonTotalInput.value = diskonTotal;
        }
    }

    // ===================== UPDATE SUMMARY =====================
    function updateSummary() {
        // Hitung total setelah diskon total
        totalTagihan = subtotal - totalDiskonItem - diskonTotal;
        if (totalTagihan < 0) totalTagihan = 0;
        
        sisaTagihan = totalTagihan - sudahDibayar;
        if (sisaTagihan < 0) sisaTagihan = 0;
        
        // Update UI
        updateAllUI();
    }

    // ===================== MULTIPLE PAYMENT =====================
    function openMultiplePayment() {
        // Reset payment methods
        paymentMethods = [];
        
        // Clear container
        const container = document.getElementById('methodsContainer');
        if (!container) {
            console.error('methodsContainer not found');
            return;
        }
        container.innerHTML = '';
        
        // Set jumlah bayar default
        const jumlahBayarInput = document.getElementById('jumlahBayar');
        if (jumlahBayarInput) {
            jumlahBayarInput.value = sisaTagihan;
            jumlahBayarInput.max = sisaTagihan;
        }
        
        // Update sisa tagihan di modal
        const sisaTagihanModalEl = document.getElementById('sisaTagihanModal');
        if (sisaTagihanModalEl) {
            sisaTagihanModalEl.textContent = formatRupiah(sisaTagihan);
        }
        
        // Tambah method pertama
        addPaymentMethod();
        
        // Show modal
        const modal = document.getElementById('popupMultiplePayment');
        if (modal) {
            modal.style.display = 'flex';
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }
        
        updatePaymentMethods();
    }

    function closeMultiplePayment() {
        const modal = document.getElementById('popupMultiplePayment');
        if (modal) {
            modal.style.display = 'none';
            // Restore body scroll
            document.body.style.overflow = '';
        }
        
        // Reset form
        resetPaymentForm();
    }

    // Fungsi untuk reset form payment
    function resetPaymentForm() {
        paymentMethods = [];
        
        const container = document.getElementById('methodsContainer');
        if (container) {
            container.innerHTML = '';
        }
        
        const totalMethodsEl = document.getElementById('totalMethods');
        const jumlahMethodsEl = document.getElementById('jumlahMethods');
        
        if (totalMethodsEl) totalMethodsEl.textContent = 'Rp 0';
        if (jumlahMethodsEl) jumlahMethodsEl.textContent = '0 metode';
        
        // Reset checkboxes
        const checkboxes = document.querySelectorAll('#popupMultiplePayment input[type="checkbox"]');
        checkboxes.forEach(cb => {
            cb.checked = false;
            toggleCheckbox(cb, '');
        });
    }

   function addPaymentMethod() {

        const container = document.getElementById('methodsContainer');
        if (!container) return;

        const methods = ['tunai','transfer','qris','debit','kredit'];
        const methodId = Date.now() + Math.random();

        const row = document.createElement('div');
        row.className = 'method-row';
        row.id = `method-${methodId}`;

        row.innerHTML = `
            <div class="method-card">

                <select class="method-select">
                    <option value="">Pilih Metode</option>
                    ${methods.map(m => `<option value="${m}">${m.toUpperCase()}</option>`).join('')}
                </select>

                <input type="number"
                    class="method-amount"
                    placeholder="Jumlah Bayar"
                    min="1"
                    max="${sisaTagihan}">

                <button type="button"
                    class="btn-remove-method"
                    onclick="removePaymentMethod('${methodId}')">
                    <i class="fas fa-times"></i>
                </button>

            </div>

            <div class="cash-extra" style="display:none;">
                <input type="number" class="cash-paid" placeholder="Uang diterima">
                <div class="cash-change">Kembalian: Rp 0</div>
            </div>
            `;

        container.appendChild(row);

        /* ================= QUERY ELEMENT ================= */

        const select = row.querySelector('.method-select');
        const amountInput = row.querySelector('.method-amount');
        const referenceInput = row.querySelector('.method-reference');
        const cashExtra = row.querySelector('.cash-extra');
        const cashPaid = row.querySelector('.cash-paid');
        const changeText = row.querySelector('.cash-change');

        /* ================= TOGGLE TUNAI ================= */

        select.addEventListener('change', () => {

            if(select.value === 'tunai'){

                cashExtra.style.display = 'flex';
                referenceInput.style.display = 'none';

            } else {

                cashExtra.style.display = 'none';
                referenceInput.style.display = 'block';

            }

            updatePaymentMethods();
        });

        /* ================= HITUNG KEMBALIAN ================= */

        function hitungKembalian(){

            const bayar = parseFloat(amountInput.value) || 0;
            const uang = parseFloat(cashPaid.value) || 0;

            const kembali = uang - bayar;

            if(select.value !== 'tunai') return;

            if(uang === 0){
                changeText.textContent = "Kembalian: Rp 0";
                changeText.style.color = "#64748b";
                return;
            }

            if(kembali >= 0){

                changeText.textContent = `Kembalian: ${formatRupiah(kembali)}`;
                changeText.style.color = "#10b981";

            } else {

                changeText.textContent = `Kurang: ${formatRupiah(Math.abs(kembali))}`;
                changeText.style.color = "#ef4444";

            }
        }

        cashPaid.addEventListener('input', hitungKembalian);
        amountInput.addEventListener('input', hitungKembalian);

        /* ================= REALTIME UPDATE ================= */

        row.querySelectorAll('select, input').forEach(el => {
            el.addEventListener('input', updatePaymentMethods);
            el.addEventListener('change', updatePaymentMethods);
        });

        /* ================= AUTO FOCUS ================= */

        setTimeout(() => select.focus(), 100);

        updatePaymentMethods();
    }

    // Fungsi untuk hapus metode pembayaran
    function removePaymentMethod(id) {
        const row = document.getElementById(`method-${id}`);
        if (!row) return;
        
        // Animation before remove
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            row.remove();
            updatePaymentMethods();
            
            // Jika tidak ada method tersisa, tambah satu
            const container = document.getElementById('methodsContainer');
            if (container && container.children.length === 0) {
                addPaymentMethod();
            }
        }, 200);
    }

    // Fungsi untuk update payment methods
    function updatePaymentMethods() {
        const rows = document.querySelectorAll('.method-row');
        const jumlahBayar = parseFloat(document.getElementById('jumlahBayar')?.value) || 0;
        
        paymentMethods = [];
        let totalMethods = 0;
        let hasEmptyMethod = false;
        let hasDuplicateMethod = false;
        const usedMethods = new Set();
        
        rows.forEach(row => {
            const metode = row.querySelector('.method-select')?.value || '';
            const amount = parseFloat(row.querySelector('.method-amount')?.value) || 0;
            const reference = '';
            
            if (metode && amount > 0) {
                // Check duplicate
                if (usedMethods.has(metode)) {
                    hasDuplicateMethod = true;
                }
                usedMethods.add(metode);
                
                paymentMethods.push({
                    metode,
                    amount,
                    reference
                });
                
                totalMethods += amount;
            } else if (metode || amount > 0) {
                // Ada yang terisi tapi tidak lengkap
                hasEmptyMethod = true;
            }
        });
        
        // Update summary
        const totalMethodsEl = document.getElementById('totalMethods');
        const jumlahMethodsEl = document.getElementById('jumlahMethods');
        const btnConfirm = document.getElementById('btnConfirmPayment');
        
        if (totalMethodsEl) {
            totalMethodsEl.textContent = formatRupiah(totalMethods);
            
            // Color coding - SUPPORT PARTIAL PAYMENT
            if (totalMethods > jumlahBayar) {
                totalMethodsEl.style.color = '#ef4444'; // Red - melebihi
            } else if (totalMethods === jumlahBayar && totalMethods > 0) {
                totalMethodsEl.style.color = '#10b981'; // Green - lunas
            } else if (totalMethods > 0 && totalMethods < jumlahBayar) {
                totalMethodsEl.style.color = '#f59e0b'; // Orange - partial (ngutang)
            } else {
                totalMethodsEl.style.color = '#3b82f6'; // Blue - default
            }
        }
        
        if (jumlahMethodsEl) {
            jumlahMethodsEl.textContent = `${paymentMethods.length} metode`;
        }
        
        // ==================== VALIDASI - SUPPORT PARTIAL & FULL PAYMENT ====================
        let canSubmit = false;
        let errorMessage = '';
        let statusMessage = '';
        
        if (paymentMethods.length === 0) {
            errorMessage = 'Tambahkan minimal 1 metode pembayaran';
        } else if (hasEmptyMethod) {
            errorMessage = 'Lengkapi semua field metode pembayaran';
        } else if (hasDuplicateMethod) {
            errorMessage = 'Tidak boleh menggunakan metode yang sama';
        } else if (totalMethods > jumlahBayar) {
            errorMessage = `Total metode melebihi ${formatRupiah(totalMethods - jumlahBayar)}`;
        } else if (totalMethods > 0 && totalMethods <= jumlahBayar) {
            // ✅ VALID - Bisa partial atau lunas
            canSubmit = true;
            
            if (totalMethods === jumlahBayar) {
                statusMessage = '✓ Pembayaran Lunas';
            } else {
                const sisaHutang = jumlahBayar - totalMethods;
                statusMessage = `✓ Bayar ${formatRupiah(totalMethods)} (Sisa hutang: ${formatRupiah(sisaHutang)})`;
            }
        }
        
        if (btnConfirm) {
            btnConfirm.disabled = !canSubmit;
            btnConfirm.style.opacity = canSubmit ? '1' : '0.5';
            btnConfirm.title = canSubmit ? statusMessage : errorMessage;
            
            // Update button text untuk partial payment
            if (canSubmit) {
                if (totalMethods === jumlahBayar) {
                    btnConfirm.innerHTML = '<i class="fas fa-check"></i> Konfirmasi Pembayaran (LUNAS)';
                } else {
                    btnConfirm.innerHTML = '<i class="fas fa-check"></i> Konfirmasi Pembayaran (PARTIAL)';
                }
            } else {
                btnConfirm.innerHTML = '<i class="fas fa-check"></i> Konfirmasi Pembayaran';
            }
        }
        
        // Show status/error message
        showPaymentError(errorMessage || statusMessage, canSubmit);
    }

    // Fungsi untuk show error message di modal
    function showPaymentError(message, isValid) {
        let errorDiv = document.getElementById('payment-error-message');
        
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = 'payment-error-message';
            errorDiv.style.cssText = `
                padding: 12px 16px;
                border-radius: 8px;
                font-size: 14px;
                margin: 10px 0;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: all 0.3s ease;
                font-weight: 500;
            `;
            
            const summaryContainer = document.querySelector('.summary-container');
            if (summaryContainer) {
                summaryContainer.parentNode.insertBefore(errorDiv, summaryContainer.nextSibling);
            }
        }
        
        if (message) {
            if (isValid) {
                // Success/Info message (hijau atau orange)
                if (message.includes('Lunas')) {
                    errorDiv.innerHTML = `<i class="fas fa-check-circle"></i><span>${message}</span>`;
                    errorDiv.style.background = '#f0fdf4';
                    errorDiv.style.border = '2px solid #bbf7d0';
                    errorDiv.style.color = '#166534';
                } else {
                    // Partial payment (orange)
                    errorDiv.innerHTML = `<i class="fas fa-info-circle"></i><span>${message}</span>`;
                    errorDiv.style.background = '#fffbeb';
                    errorDiv.style.border = '2px solid #fde68a';
                    errorDiv.style.color = '#92400e';
                }
            } else {
                // Error message (merah)
                errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i><span>${message}</span>`;
                errorDiv.style.background = '#fef2f2';
                errorDiv.style.border = '2px solid #fecaca';
                errorDiv.style.color = '#991b1b';
            }
            errorDiv.style.display = 'flex';
        } else {
            errorDiv.style.display = 'none';
        }
    }

    // ===================== TOAST MESSAGE =====================
    function showToast(message, type = 'info') {
        // Buat toast element
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        // Add inline styles for toast
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : '#3b82f6'};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 10000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        // Animasi masuk
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        }, 10);
        
        // Hapus setelah 3 detik
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    // ===================== MISSING FUNCTIONS =====================
    
    // Fungsi untuk toggle checkbox dengan animasi
    function toggleCheckbox(checkbox, type) {
        const label = checkbox.parentElement;
        const box = label.querySelector('div');
        const icon = box ? box.querySelector('.fa-check') : null;
        
        if (!box) return;
        
        if (checkbox.checked) {
            box.style.background = '#10b981';
            box.style.borderColor = '#10b981';
            if (icon) icon.style.display = 'block';
        } else {
            box.style.background = 'white';
            box.style.borderColor = '#d1d5db';
            if (icon) icon.style.display = 'none';
        }
    }

    // Fungsi untuk membuka modal bayar lagi (partial payment)
    function openBayarLagi() {
        openMultiplePayment();
    }

// ===================== FUNGSI CETAK PEMBAYARAN =====================

function cetakPembayaran() {
    // Ambil booking ID dari URL saat ini
    const urlParams = new URLSearchParams(window.location.search);
    const bookingId = urlParams.get('id');
    
    if (!bookingId) {
        alert('Booking ID tidak ditemukan!');
        return;
    }
    
    // Buka cetak_faktur.php di tab baru
    const url = `cetak_faktur.php?id=${bookingId}`;
    window.open(url, '_blank');
}

// ===================== FUNGSI KIRIM INVOICE =====================

function kirimInvoice() {
    const urlParams = new URLSearchParams(window.location.search);
    const bookingId = urlParams.get('id');
    
    if (!bookingId) {
        alert('Booking ID tidak ditemukan!');
        return;
    }
    
    // Konfirmasi dulu
    const konfirmasi = confirm(
        'Kirim invoice ke pasien?\n\n' +
        'Invoice akan dikirim melalui:\n' +
        '- Email (jika ada)\n' +
        '- WhatsApp (jika ada)\n\n' +
        'Lanjutkan?'
    );
    
    if (!konfirmasi) {
        return;
    }
    
    // Show loading
    showToast('Mengirim invoice...', 'info');
    
    // Kirim request ke server
    fetch('kirim_invoice.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `booking_id=${bookingId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Invoice berhasil dikirim!', 'success');
        } else {
            alert('Gagal mengirim invoice: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengirim invoice');
    });
}

    // Fungsi untuk select tipe diskon total
    function selectDiskonTotalType(type) {

        diskonTotalType = type;

        // ===== Toggle active UI =====
        document.querySelectorAll('.type-option').forEach(option => {
            option.classList.remove('active');

            if (option.dataset.type === type) {
                option.classList.add('active');
            }
        });

        // ===== Toggle input =====
        const persenContainer = document.getElementById('diskonPersenContainer');
        const nominalContainer = document.getElementById('diskonNominalContainer');
        
        if (persenContainer) {
            persenContainer.style.display = type === 'persen' ? 'block' : 'none';
        }
        
        if (nominalContainer) {
            nominalContainer.style.display = type === 'nominal' ? 'block' : 'none';
        }

        // Reset value lawan tipe
        if(type === 'persen'){
            const nominalInput = document.getElementById('diskonTotalNominal');
            if (nominalInput) nominalInput.value = '';
        } else {
            const persenInput = document.getElementById('diskonTotalPersen');
            if (persenInput) persenInput.value = '';
        }

        updatePreviewDiskonTotal();
    }

    // ===================== INISIALISASI =====================
    document.addEventListener('DOMContentLoaded', function() {
        const formMultiple = document.getElementById('formMultiplePayment');
        
        if (formMultiple) {
            formMultiple.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validasi final
                if (paymentMethods.length === 0) {
                    alert('Tambahkan minimal satu metode pembayaran');
                    return false;
                }
                
                const jumlahBayar = parseFloat(document.getElementById('jumlahBayar').value) || 0;
                const totalMethods = paymentMethods.reduce((sum, m) => sum + m.amount, 0);
                
                // ✅ ALLOW PARTIAL PAYMENT
                if (totalMethods > jumlahBayar) {
                    alert(`Total metode pembayaran (${formatRupiah(totalMethods)}) melebihi jumlah yang akan dibayar (${formatRupiah(jumlahBayar)})`);
                    return false;
                }
                
                if (totalMethods === 0) {
                    alert('Total pembayaran tidak boleh Rp 0');
                    return false;
                }
                
                // Konfirmasi dengan info partial/lunas
                let konfirmasiText = `Konfirmasi Pembayaran\n\n`;
                konfirmasiText += `Jumlah: ${formatRupiah(totalMethods)}\n`;
                konfirmasiText += `Metode: ${paymentMethods.map(m => m.metode.toUpperCase()).join(', ')}\n\n`;
                
                if (totalMethods === jumlahBayar) {
                    konfirmasiText += `Status: LUNAS ✓\n\n`;
                } else {
                    const sisaHutang = jumlahBayar - totalMethods;
                    konfirmasiText += `Status: PARTIAL PAYMENT\n`;
                    konfirmasiText += `Sisa Hutang: ${formatRupiah(sisaHutang)}\n\n`;
                }
                
                konfirmasiText += `Lanjutkan?`;
                
                const konfirmasi = confirm(konfirmasiText);
                
                if (!konfirmasi) {
                    return false;
                }
                
                // Add payment methods data
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'payment_methods';
                input.value = JSON.stringify(paymentMethods);
                this.appendChild(input);
                
                // Add payment status (lunas atau partial)
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'payment_status';
                statusInput.value = totalMethods === jumlahBayar ? 'paid' : 'partial';
                this.appendChild(statusInput);
                
                // Add actual amount paid
                const amountInput = document.createElement('input');
                amountInput.type = 'hidden';
                amountInput.name = 'amount_paid';
                amountInput.value = totalMethods;
                this.appendChild(amountInput);
                
                // Show loading
                const btnConfirm = document.getElementById('btnConfirmPayment');
                if (btnConfirm) {
                    btnConfirm.disabled = true;
                    btnConfirm.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                }
                
                // Submit form
                this.submit();
            });
        }
    });

function editDiskonTotal() {

    document.getElementById("diskonTotalPersen").disabled = false;
    document.getElementById("diskonTotalNominal").disabled = false;

    document.getElementById("diskonTotalPersen").focus();

}

function removeDiskonTotal() {

    document.getElementById("diskonTotalPersen").value = "";
    document.getElementById("diskonTotalNominal").value = "";

    document.getElementById("diskonTotalDisplay").innerText = "- Rp 0";
    document.getElementById("diskonTotalInput").value = 0;

    document.getElementById("btnEditDiskonTotal").style.display = "none";
    document.getElementById("btnRemoveDiskonTotal").style.display = "none";

    updateSummary();
}

window.onload = function() {

    let diskon = parseFloat(document.getElementById("diskonTotalInput").value);

    if (diskon > 0) {
        document.getElementById("btnEditDiskonTotal").style.display = "inline-block";
        document.getElementById("btnRemoveDiskonTotal").style.display = "inline-block";
    }

};

