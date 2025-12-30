let participantCount = 1;

function addParticipant() {
    const template = document.querySelector('.participant'); // ← Ini harus ada di HTML
    const container = document.getElementById('additionalParticipants');

    if (!template || !container) {
        alert('Template peserta tidak ditemukan');
        return;
    }

    participantCount++;

    const clone = template.cloneNode(true);

    // Update judul
    const title = clone.querySelector('.participant-title');
    if (title) title.innerText = 'Data Peserta ' + participantCount;

    // Reset semua input
    clone.querySelectorAll('input, textarea, select').forEach(el => {
        if (el.type === 'radio' || el.type === 'checkbox') {
            el.checked = false;
        } else {
            el.value = '';
        }
        
        // ⚠️ PENTING: Update name attribute pakai array
        if (el.name && el.name.includes('[')) {
            // Ganti participants[0] jadi participants[1], dst
            el.name = el.name.replace(/\[0\]/, '[' + participantCount + ']');
        } else if (el.name) {
            // Untuk name biasa, tambahin suffix
            el.name = el.name.replace(/\[\]$/, '') + '_' + participantCount + '[]';
        }
    });

    // Reset radio button groups
    clone.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.name = radio.name.replace(/participants\[\d+\]/, 'participants[' + participantCount + ']');
    });

    // Reset info usia
    const usiaInfo = clone.querySelector('.info-box');
    if (usiaInfo) {
        usiaInfo.style.display = 'none';
        usiaInfo.id = 'usiaInfo-' + participantCount;
    }

    // Tombol hapus
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.innerText = '🗑 Hapus Peserta';
    removeBtn.className = 'btn btn-remove-participant';
    removeBtn.style.marginTop = '16px';
    removeBtn.onclick = function () {
        if (confirm('Yakin hapus peserta ini?')) {
            clone.remove();
        }
    };

    clone.appendChild(removeBtn);
    container.appendChild(clone);

    // Scroll ke peserta baru
    clone.scrollIntoView({ behavior: 'smooth', block: 'start' });
}