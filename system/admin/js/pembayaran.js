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
let currentItemID = null;

// Inisialisasi diskonItems dari data PHP
let diskonItems = {};

window.paymentData.diskonItems.forEach((srv, index) => {
    diskonItems[index] = {
        id: srv.id,
        harga: srv.harga,
        diskon: srv.diskon || 0,
        tipe: srv.diskon_tipe || ''
    };
});

// ===================== FUNGSI HELPER =====================
function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

function formatRupiah(angka) {
    if (angka === undefined || angka === null) return 'Rp 0';
    return 'Rp ' + formatNumber(angka);
}

function formatInputRupiah(input) {
    if (!input) return;
    let value = input.value.replace(/[^0-9]/g, '');
    input.value = value ? parseInt(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") : '';
}

function unformatRupiah(value) {
    if (!value) return 0;
    return parseInt(value.toString().replace(/\./g, '')) || 0;
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
        persen = subtotal > 0 ? Math.round((nominal / subtotal) * 100) : 0;
        document.getElementById('diskonTotalPersen').value = persen || '';
    }
    updatePreviewDiskonTotal();
}

function updatePreviewDiskonTotal() {
    const persen = parseFloat(document.getElementById('diskonTotalPersen').value) || 0;
    const nominal = parseFloat(document.getElementById('diskonTotalNominal').value) || 0;
    let diskon = diskonTotalType === 'persen' ? Math.round(subtotal * persen / 100) : nominal;
    
    const infoDiv = document.getElementById('diskonTotalInfo');
    if (diskon > 0) {
        infoDiv.innerHTML = `<span style="color:#10b981;"><i class="fas fa-check-circle"></i> Preview: Diskon Rp ${formatNumber(diskon)}</span>`;
    } else {
        infoDiv.innerHTML = `<i class="fas fa-info-circle"></i> Diskon total akan mengurangi dari jumlah tagihan akhir`;
    }
}

function applyDiskonTotal() {
    const persen = parseFloat(document.getElementById('diskonTotalPersen').value) || 0;
    const nominal = parseFloat(document.getElementById('diskonTotalNominal').value) || 0;
    
    diskonTotal = diskonTotalType === 'persen' ? Math.round(subtotal * persen / 100) : nominal;
    
    document.getElementById("diskonTotalInput").value = diskonTotal;
    document.getElementById("diskonTotalDisplay").textContent = `- Rp ${formatNumber(diskonTotal)}`;
    document.getElementById("btnEditDiskonTotal").style.display = "inline-block";
    document.getElementById("btnRemoveDiskonTotal").style.display = "inline-block";
    
    updateSummary();
    showToast('Diskon total berhasil diterapkan!', 'success');
}

function editDiskonTotal() {
    document.getElementById("diskonTotalPersen").disabled = false;
    document.getElementById("diskonTotalNominal").disabled = false;
    document.getElementById("diskonTotalPersen").focus();
}

function removeDiskonTotal() {
    document.getElementById("diskonTotalPersen").value = "";
    document.getElementById("diskonTotalNominal").value = "";
    document.getElementById("diskonTotalDisplay").textContent = "- Rp 0";
    document.getElementById("diskonTotalInput").value = 0;
    document.getElementById("btnEditDiskonTotal").style.display = "none";
    document.getElementById("btnRemoveDiskonTotal").style.display = "none";
    
    diskonTotal = 0;
    updateSummary();
}

// ===================== DISKON PER ITEM =====================
function openDiskonItem(id, index, harga, tipe, diskon, itemName) {
    currentItemID = id;
    currentItemIndex = index;
    currentItemHarga = harga;
    currentDiskonType = tipe || 'persen';
    
    document.getElementById('currentItemID').value = id;
    document.getElementById('currentItemIndex').value = index;
    document.getElementById('currentItemHarga').value = harga;
    document.getElementById('itemName').textContent = itemName || 'Layanan';
    document.getElementById('itemHarga').textContent = formatRupiah(harga);
    document.getElementById('originalPrice').textContent = formatRupiah(harga);
    
    selectDiskonType(currentDiskonType);
    
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
    
    document.querySelectorAll('.type-option').forEach(option => {
        option.classList.remove('active');
        if (option.dataset.type === type) option.classList.add('active');
    });
    
    document.querySelectorAll('.diskon-input-section').forEach(section => {
        section.classList.remove('active');
    });
    
    if (type === 'persen') {
        document.getElementById('diskonPersenSection').classList.add('active');
    } else {
        document.getElementById('diskonNilaiSection').classList.add('active');
    }
    
    resetDiskonInputs();
}

function hitungDiskonFromPersen() {
    const persenInput = document.getElementById('inputDiskonPersen');
    let persen = parseFloat(persenInput.value) || 0;
    
    if (persen > 100) { persen = 100; persenInput.value = 100; }
    if (persen < 0) { persen = 0; persenInput.value = 0; }
    
    const diskon = Math.round(currentItemHarga * persen / 100);
    
    document.getElementById('nilaiDiskonPersen').textContent = formatRupiah(diskon);
    document.getElementById('appliedDiskon').textContent = `- ${formatRupiah(diskon)}`;
    document.getElementById('finalPrice').textContent = formatRupiah(currentItemHarga - diskon);
    document.getElementById('inputDiskonNilai').value = diskon;
    document.getElementById('persenDiskonNilai').textContent = `${persen}%`;
}

function hitungPersenFromDiskon() {
    const nilaiInput = document.getElementById('inputDiskonNilai');
    let diskon = parseFloat(nilaiInput.value) || 0;
    
    if (diskon > currentItemHarga) { diskon = currentItemHarga; nilaiInput.value = currentItemHarga; }
    if (diskon < 0) { diskon = 0; nilaiInput.value = 0; }
    
    const persen = diskon > 0 ? Math.round((diskon / currentItemHarga) * 100) : 0;
    
    document.getElementById('persenDiskonNilai').textContent = `${persen}%`;
    document.getElementById('appliedDiskon').textContent = `- ${formatRupiah(diskon)}`;
    document.getElementById('finalPrice').textContent = formatRupiah(currentItemHarga - diskon);
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
    let tipe = currentDiskonType;
    let diskon = 0;
    let id = currentItemID;
    
    if (tipe === 'persen') {
        const persen = parseFloat(document.getElementById('inputDiskonPersen').value) || 0;
        diskon = Math.round(currentItemHarga * persen / 100);
    } else {
        diskon = parseFloat(document.getElementById('inputDiskonNilai').value) || 0;
    }
    
    if (diskon > currentItemHarga) {
        alert('Diskon tidak boleh lebih besar dari harga item');
        return;
    }
    
    diskonItems[currentItemIndex] = {
        id: currentItemID,
        harga: currentItemHarga,
        diskon: diskon,
        tipe: tipe
    };
    
    updateDiskonItemUI(id, currentItemIndex, diskon, tipe);
    updateDiskonToDatabase(id, diskon, tipe);
    updateSummary();
    
    closeDiskonItemPopup();
    showToast('Diskon berhasil diterapkan dan tersimpan!', 'success');
}

function updateDiskonToDatabase(id, diskon, tipe) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('diskon', diskon);
    formData.append('tipe_diskon', tipe);
    formData.append('action', 'update_diskon');

    fetch('update_diskon.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            showToast('Gagal menyimpan diskon ke database: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Terjadi kesalahan koneksi: ' + error.message, 'error');
    });
}

function removeDiskonItem() {
    diskonItems[currentItemIndex] = {
        id: currentItemID,
        harga: currentItemHarga,
        diskon: 0,
        tipe: ''
    };
    
    updateDiskonItemUI(currentItemID, currentItemIndex, 0, '');
    updateDiskonToDatabase(currentItemID, 0, '');
    updateSummary();
    
    closeDiskonItemPopup();
    showToast('Diskon berhasil dihapus!', 'info');
}

function updateDiskonItemUI(id_item, index, diskon, tipe) {
    const diskonCell = document.getElementById(`diskon-cell-${index}`);
    const totalCell = document.getElementById(`total-item-${index}`);
    const harga = currentItemHarga;
    
    // Update hidden inputs
    const serviceDiskon = document.getElementById(`service_diskon_${index}`);
    const serviceTipe = document.getElementById(`service_diskon_tipe_${index}`);
    
    if (serviceDiskon) serviceDiskon.value = diskon;
    if (serviceTipe) serviceTipe.value = tipe;
    
    if (diskon > 0) {
        const persen = Math.round((diskon / harga) * 100);
        let diskonHTML = `<div class="diskon-applied"><div style="display: flex; align-items: center; gap: 8px;">`;
        
        if (tipe === 'persen') {
            diskonHTML += `<span class="diskon-badge persen">${persen}%</span>`;
            diskonHTML += `<span style="font-size: 12px; color: #64748b;">(Rp ${formatNumber(diskon)})</span>`;
        } else {
            diskonHTML += `<span class="diskon-badge nilai">- Rp ${formatNumber(diskon)}</span>`;
        }
        
        diskonHTML += `</div></div>`;
        
        diskonCell.innerHTML = diskonHTML + `
            <button type="button" class="btn-edit-diskon"
                onclick="openDiskonItem(${id_item}, ${index}, ${harga}, '${tipe}', ${diskon}, '')"
                title="Edit Diskon">
                <i class="fas fa-edit"></i>
            </button>
        `;
    } else {
        diskonCell.innerHTML = `
            <span class="no-diskon">-</span>
            <button type="button" class="btn-edit-diskon"
                onclick="openDiskonItem(${id_item}, ${index}, ${harga}, '', 0, '')"
                title="Tambah Diskon">
                <i class="fas fa-edit"></i>
            </button>
        `;
    }
    
    if (totalCell) {
        totalCell.textContent = `Rp ${formatNumber(harga - diskon)}`;
    }
}

// ===================== UPDATE SUMMARY =====================
function updateSummary() {
    // Hitung ulang subtotal dan diskon item
    subtotal = 0;
    totalDiskonItem = 0;
    
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
        jumlahBayar: document.getElementById('jumlahBayar'),
        totalTagihanInput: document.getElementById('totalTagihanInput'),
        sisaTagihanInput: document.getElementById('sisaTagihanInput'),
        diskonTotalInput: document.getElementById('diskonTotalInput')
    };
    
    if (elements.subtotalDisplay) elements.subtotalDisplay.textContent = formatRupiah(subtotal);
    if (elements.diskonItemDisplay) elements.diskonItemDisplay.textContent = `- ${formatRupiah(totalDiskonItem)}`;
    if (elements.diskonTotalDisplay) elements.diskonTotalDisplay.textContent = `- ${formatRupiah(diskonTotal)}`;
    if (elements.totalTagihan) elements.totalTagihan.textContent = formatRupiah(totalTagihan);
    if (elements.sisaTagihan) elements.sisaTagihan.textContent = formatRupiah(sisaTagihan);
    
    if (elements.totalDiskonItems) elements.totalDiskonItems.textContent = `- ${formatRupiah(totalDiskonItem)}`;
    if (elements.totalSemuaItems) elements.totalSemuaItems.textContent = formatRupiah(totalTagihan);
    
    if (elements.totalTagihanSummary) elements.totalTagihanSummary.textContent = formatRupiah(totalTagihan);
    if (elements.sisaTagihanSummary) elements.sisaTagihanSummary.textContent = formatRupiah(sisaTagihan);
    
    // Update modal
    if (elements.jumlahBayar) {
        elements.jumlahBayar.value = formatNumber(sisaTagihan);
        elements.jumlahBayar.setAttribute('data-original', sisaTagihan);
    }
    if (elements.totalTagihanInput) elements.totalTagihanInput.value = totalTagihan;
    if (elements.sisaTagihanInput) elements.sisaTagihanInput.value = sisaTagihan;
    if (elements.diskonTotalInput) elements.diskonTotalInput.value = diskonTotal;
}

// ===================== MULTIPLE PAYMENT =====================
function openMultiplePayment() {
    sisaTagihan = window.paymentData.sisaTagihan;
    
    paymentMethods = [];
    
    const container = document.getElementById('methodsContainer');
    if (container) container.innerHTML = '';
    
    const jumlahBayarInput = document.getElementById('jumlahBayar');
    if (jumlahBayarInput) {
        jumlahBayarInput.value = formatNumber(sisaTagihan);
        jumlahBayarInput.setAttribute('data-original', sisaTagihan);
    }
    
    addPaymentMethod();
    
    const modal = document.getElementById('popupMultiplePayment');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    updatePaymentMethods();
}

function closeMultiplePayment() {
    const modal = document.getElementById('popupMultiplePayment');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    resetPaymentForm();
}

function resetPaymentForm() {
    paymentMethods = [];
    
    const container = document.getElementById('methodsContainer');
    if (container) container.innerHTML = '';
    
    const totalMethodsEl = document.getElementById('totalMethods');
    const jumlahMethodsEl = document.getElementById('jumlahMethods');
    
    if (totalMethodsEl) totalMethodsEl.textContent = 'Rp 0';
    if (jumlahMethodsEl) jumlahMethodsEl.textContent = '0 metode';
    
    document.querySelectorAll('#popupMultiplePayment input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
        toggleCheckbox(cb, '');
    });
}

function openBayarLagi() {
    openMultiplePayment();
}

function addPaymentMethod() {
    const container = document.getElementById('methodsContainer');
    if (!container) return;

    const methods = ['tunai', 'transfer', 'qris', 'debit', 'kredit'];
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

            <div style="position: relative; flex: 1;">
                <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #64748b;">Rp</span>
                <input type="text" class="method-amount" placeholder="0"
                    style="width: 100%; padding: 12px 15px 12px 35px; border: 2px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box;"
                    oninput="formatInputRupiah(this); updatePaymentMethods()">
            </div>

            <button type="button" class="btn-remove-method" onclick="removePaymentMethod('${methodId}')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="cash-extra" style="display:none;">
            <div style="position: relative; flex: 1;">
                <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #64748b;">Rp</span>
                <input type="text" class="cash-paid" placeholder="0"
                    style="width: 100%; padding: 12px 15px 12px 35px; border: 2px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box;"
                    oninput="formatInputRupiah(this); hitungKembalian(this)">
            </div>
            <div class="cash-change">Kembalian: Rp 0</div>
        </div>
    `;

    container.appendChild(row);

    const select = row.querySelector('.method-select');
    const amountInput = row.querySelector('.method-amount');
    const cashExtra = row.querySelector('.cash-extra');
    const cashPaid = row.querySelector('.cash-paid');
    const changeText = row.querySelector('.cash-change');

    select.addEventListener('change', () => {
        cashExtra.style.display = select.value === 'tunai' ? 'flex' : 'none';
        updatePaymentMethods();
    });

    function hitungKembalian() {
        if (select.value !== 'tunai') return;
        
        const bayar = unformatRupiah(amountInput.value) || 0;
        const uang = unformatRupiah(cashPaid.value) || 0;
        const kembali = uang - bayar;

        if (uang === 0) {
            changeText.textContent = "Kembalian: Rp 0";
            changeText.style.color = "#64748b";
        } else if (kembali >= 0) {
            changeText.textContent = `Kembalian: ${formatRupiah(kembali)}`;
            changeText.style.color = "#10b981";
        } else {
            changeText.textContent = `Kurang: ${formatRupiah(Math.abs(kembali))}`;
            changeText.style.color = "#ef4444";
        }
    }

    cashPaid.addEventListener('input', function() {
        formatInputRupiah(this);
        hitungKembalian();
        updatePaymentMethods();
    });
    
    amountInput.addEventListener('input', hitungKembalian);

    row.querySelectorAll('select, input').forEach(el => {
        el.addEventListener('input', updatePaymentMethods);
        el.addEventListener('change', updatePaymentMethods);
    });

    setTimeout(() => select.focus(), 100);
    updatePaymentMethods();
}

function removePaymentMethod(id) {
    const row = document.getElementById(`method-${id}`);
    if (!row) return;
    
    row.style.opacity = '0';
    row.style.transform = 'translateX(-20px)';
    
    setTimeout(() => {
        row.remove();
        updatePaymentMethods();
        
        const container = document.getElementById('methodsContainer');
        if (container && container.children.length === 0) {
            addPaymentMethod();
        }
    }, 200);
}

function updatePaymentMethods() {
    const rows = document.querySelectorAll('.method-row');
    const jumlahBayarInput = document.getElementById('jumlahBayar');
    const jumlahBayar = parseFloat(jumlahBayarInput?.getAttribute('data-original')) || 0;
    
    paymentMethods = [];
    let totalMethods = 0;
    let hasEmptyMethod = false;
    let hasDuplicateMethod = false;
    const usedMethods = new Set();
    
    rows.forEach(row => {
        const metode = row.querySelector('.method-select')?.value || '';
        const amountInput = row.querySelector('.method-amount');
        const amount = unformatRupiah(amountInput?.value) || 0;
        
        if (metode && amount > 0) {
            if (usedMethods.has(metode)) hasDuplicateMethod = true;
            usedMethods.add(metode);
            
            paymentMethods.push({ metode, amount, reference: '' });
            totalMethods += amount;
        } else if (metode || amount > 0) {
            hasEmptyMethod = true;
        }
    });
    
    const totalMethodsEl = document.getElementById('totalMethods');
    const jumlahMethodsEl = document.getElementById('jumlahMethods');
    const btnConfirm = document.getElementById('btnConfirmPayment');
    
    if (totalMethodsEl) {
        totalMethodsEl.textContent = formatRupiah(totalMethods);
        
        if (totalMethods > jumlahBayar) {
            totalMethodsEl.style.color = '#ef4444';
        } else if (totalMethods === jumlahBayar && totalMethods > 0) {
            totalMethodsEl.style.color = '#10b981';
        } else if (totalMethods > 0 && totalMethods < jumlahBayar) {
            totalMethodsEl.style.color = '#f59e0b';
        } else {
            totalMethodsEl.style.color = '#3b82f6';
        }
    }
    
    if (jumlahMethodsEl) {
        jumlahMethodsEl.textContent = `${paymentMethods.length} metode`;
    }
    
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
        canSubmit = true;
        statusMessage = totalMethods === jumlahBayar ? 
            '✓ Pembayaran Lunas' : 
            `✓ Bayar ${formatRupiah(totalMethods)} (Sisa: ${formatRupiah(jumlahBayar - totalMethods)})`;
    }
    
    if (btnConfirm) {
        btnConfirm.disabled = !canSubmit;
        btnConfirm.style.opacity = canSubmit ? '1' : '0.5';
        btnConfirm.title = canSubmit ? statusMessage : errorMessage;
        btnConfirm.innerHTML = canSubmit ? 
            (totalMethods === jumlahBayar ? 
                '<i class="fas fa-check"></i> Konfirmasi (LUNAS)' : 
                '<i class="fas fa-check"></i> Konfirmasi (PARTIAL)') : 
            '<i class="fas fa-check"></i> Konfirmasi Pembayaran';
    }
    
    showPaymentError(errorMessage || statusMessage, canSubmit);
}

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
            if (message.includes('Lunas')) {
                errorDiv.innerHTML = `<i class="fas fa-check-circle"></i><span>${message}</span>`;
                errorDiv.style.background = '#f0fdf4';
                errorDiv.style.border = '2px solid #bbf7d0';
                errorDiv.style.color = '#166534';
            } else {
                errorDiv.innerHTML = `<i class="fas fa-info-circle"></i><span>${message}</span>`;
                errorDiv.style.background = '#fffbeb';
                errorDiv.style.border = '2px solid #fde68a';
                errorDiv.style.color = '#92400e';
            }
        } else {
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
    const toast = document.createElement('div');
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i><span>${message}</span>`;
    
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
    
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    }, 10);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ===================== FUNGSI LAINNYA =====================
function toggleCheckbox(checkbox, type) {
    const label = checkbox.parentElement;
    const box = label.querySelector('div');
    const icon = box?.querySelector('.fa-check');
    
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

function cetakPembayaran() {
    const urlParams = new URLSearchParams(window.location.search);
    const bookingId = urlParams.get('id');
    
    if (bookingId) {
        window.open(`cetak_faktur.php?id=${bookingId}`, '_blank');
    } else {
        alert('Booking ID tidak ditemukan!');
    }
}

function kirimInvoice() {
    const urlParams = new URLSearchParams(window.location.search);
    const bookingId = urlParams.get('id');
    
    if (!bookingId) {
        alert('Booking ID tidak ditemukan!');
        return;
    }
    
    if (!confirm('Kirim invoice ke pasien?\n\nInvoice akan dikirim melalui:\n- Email (jika ada)\n- WhatsApp (jika ada)\n\nLanjutkan?')) {
        return;
    }
    
    showToast('Mengirim invoice...', 'info');
    
    fetch('kirim_invoice.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
        alert('Terjadi kesalahan saat mengirim invoice');
    });
}

function selectDiskonTotalType(type) {
    diskonTotalType = type;
    
    document.querySelectorAll('.type-option').forEach(option => {
        option.classList.remove('active');
        if (option.dataset.type === type) option.classList.add('active');
    });
    
    const persenContainer = document.getElementById('diskonPersenContainer');
    const nominalContainer = document.getElementById('diskonNominalContainer');
    
    if (persenContainer) persenContainer.style.display = type === 'persen' ? 'block' : 'none';
    if (nominalContainer) nominalContainer.style.display = type === 'nominal' ? 'block' : 'none';
    
    if (type === 'persen') {
        const nominalInput = document.getElementById('diskonTotalNominal');
        if (nominalInput) nominalInput.value = '';
    } else {
        const persenInput = document.getElementById('diskonTotalPersen');
        if (persenInput) persenInput.value = '';
    }
    
    updatePreviewDiskonTotal();
}

// ===================== FORM SUBMIT HANDLER =====================
document.addEventListener('DOMContentLoaded', function() {
    const formMultiple = document.getElementById('formMultiplePayment');
    
    if (formMultiple) {
        // Hapus event listener lama dengan clone
        const newForm = formMultiple.cloneNode(true);
        formMultiple.parentNode.replaceChild(newForm, formMultiple);
        
        newForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (paymentMethods.length === 0) {
                alert('Tambahkan minimal satu metode pembayaran');
                return false;
            }
            
            const jumlahBayarInput = document.getElementById('jumlahBayar');
            const jumlahBayar = parseFloat(jumlahBayarInput?.getAttribute('data-original')) || 0;
            const totalMethods = paymentMethods.reduce((sum, m) => sum + m.amount, 0);
            
            if (totalMethods > jumlahBayar) {
                alert(`Total metode pembayaran (${formatRupiah(totalMethods)}) melebihi jumlah yang akan dibayar (${formatRupiah(jumlahBayar)})`);
                return false;
            }
            
            if (totalMethods === 0) {
                alert('Total pembayaran tidak boleh Rp 0');
                return false;
            }
            
            let konfirmasiText = `Konfirmasi Pembayaran\n\n`;
            konfirmasiText += `Jumlah: ${formatRupiah(totalMethods)}\n`;
            konfirmasiText += `Metode: ${paymentMethods.map(m => m.metode.toUpperCase()).join(', ')}\n\n`;
            
            if (totalMethods === jumlahBayar) {
                konfirmasiText += `Status: LUNAS ✓\n\n`;
            } else {
                konfirmasiText += `Status: PARTIAL PAYMENT\n`;
                konfirmasiText += `Sisa: ${formatRupiah(jumlahBayar - totalMethods)}\n\n`;
            }
            
            konfirmasiText += `Lanjutkan?`;
            
            if (!confirm(konfirmasiText)) {
                return false;
            }
            
            // Add data to form
            const methodsInput = document.createElement('input');
            methodsInput.type = 'hidden';
            methodsInput.name = 'payment_methods';
            methodsInput.value = JSON.stringify(paymentMethods);
            this.appendChild(methodsInput);
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'payment_status';
            statusInput.value = totalMethods === jumlahBayar ? 'paid' : 'partial';
            this.appendChild(statusInput);
            
            const amountInput = document.createElement('input');
            amountInput.type = 'hidden';
            amountInput.name = 'amount_paid';
            amountInput.value = totalMethods;
            this.appendChild(amountInput);
            
            const btnConfirm = document.getElementById('btnConfirmPayment');
            if (btnConfirm) {
                btnConfirm.disabled = true;
                btnConfirm.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            }
            
            this.submit();
        });
    }
});

// ===================== WINDOW ONLOAD =====================
window.onload = function() {
    let diskon = parseFloat(document.getElementById("diskonTotalInput")?.value) || 0;
    if (diskon > 0) {
        document.getElementById("btnEditDiskonTotal").style.display = "inline-block";
        document.getElementById("btnRemoveDiskonTotal").style.display = "inline-block";
    }
    
    // Initial sync
    updateSummary();
};