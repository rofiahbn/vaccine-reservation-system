let participantCount = 1;

function addParticipant() {
    const template = document.querySelector('.participant');
    const container = document.getElementById('additionalParticipants');

    if (!template || !container) {
        alert('Template peserta tidak ditemukan');
        return;
    }

    participantCount++;

    const clone = template.cloneNode(true);

    // Judul
    const title = clone.querySelector('.participant-title');
    if (title) title.innerText = 'Data Peserta ' + participantCount;

    // Reset input
    clone.querySelectorAll('input, textarea').forEach(el => {
        if (el.type === 'radio' || el.type === 'checkbox') {
            el.checked = false;
        } else {
            el.value = '';
        }
    });

    // Supaya radio tidak bentrok
    clone.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.name = radio.name + '_' + participantCount;
    });

    // Reset info usia
    const usiaInfo = clone.querySelector('.usiaInfo');
    if (usiaInfo) usiaInfo.style.display = 'none';

    /* ===== TOMBOL HAPUS PESERTA ===== */
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.innerText = '🗑 Hapus Peserta';
    removeBtn.className = 'btn btn-remove-participant';
    removeBtn.style.marginTop = '16px';

    removeBtn.onclick = function () {
        clone.remove();
    };

    clone.appendChild(removeBtn);

    container.appendChild(clone);
}
