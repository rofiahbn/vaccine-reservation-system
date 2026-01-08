let selectedDateValue = null;

function selectDate(element, day) {
    document.querySelectorAll('.day').forEach(d => {
        d.classList.remove('selected');
    });

    element.classList.add('selected');

    const tanggal = `${tahunNow}-${String(bulanNow).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    selectedDateValue = tanggal;

    document.getElementById('selectedDate').value = tanggal;
    document.getElementById('dateText').textContent =
        `${day} ${namaBulanNow} ${tahunNow}`;
    document.getElementById('dateDisplay').style.display = 'block';

    // reset jam
    document.getElementById('selectedTime').value = '';
    document.getElementById('btnSubmit').disabled = true;

    // ⬇️ INI YANG SEKARANG AKAN JALAN
    generateTimeSlots();
}

function prevMonth() {
    let bulan = bulanNow;
    let tahun = tahunNow;

    bulan--;
    if (bulan < 1) {
        bulan = 12;
        tahun--;
    }

    document.getElementById('inputBulan').value = bulan;
    document.getElementById('inputTahun').value = tahun;
    document.getElementById('monthForm').submit();
}

function nextMonth() {
    let bulan = bulanNow;
    let tahun = tahunNow;

    bulan++;
    if (bulan > 12) {
        bulan = 1;
        tahun++;
    }

    document.getElementById('inputBulan').value = bulan;
    document.getElementById('inputTahun').value = tahun;
    document.getElementById('monthForm').submit();
}

function generateTimeSlots() {
    const container = document.getElementById('slotsContainer');
    container.innerHTML = '';

    // Jam operasional
    let startHour = 9;
    let endHour = 17;
    let interval = 15; // menit

    for (let hour = startHour; hour < endHour; hour++) {
        for (let minute = 0; minute < 60; minute += interval) {
            const time =
                String(hour).padStart(2, '0') + ':' +
                String(minute).padStart(2, '0');

            const slot = document.createElement('div');
            slot.classList.add('time-slot');
            slot.textContent = time;

            slot.onclick = () => selectTime(slot, time);

            container.appendChild(slot);
        }
    }

    document.getElementById('timeSlots').style.display = 'block';
}

function selectTime(element, time) {
    document.querySelectorAll('.time-slot').forEach(s => {
        s.classList.remove('selected');
    });

    element.classList.add('selected');
    document.getElementById('selectedTime').value = time;

    document.getElementById('btnSubmit').disabled = false;
}

