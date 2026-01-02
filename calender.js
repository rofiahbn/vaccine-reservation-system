let selectedDateValue = null;

function selectDate(day) {
    // Remove previous selection
    document.querySelectorAll('.day').forEach(d => {
        d.classList.remove('selected');
    });

    // Add selection to clicked day
    event.target.classList.add('selected');

    // Format tanggal
    const bulan = bulanNow;
    const tahun = tahunNow;
    const tanggal = `${tahun}-${String(bulan).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    selectedDateValue = tanggal;
    document.getElementById('selectedDate').value = tanggal;

    // Format display
    const namaBulan = namaBulanNow;
    document.getElementById('dateText').textContent = `${day} ${namaBulan} ${tahun}`;
    document.getElementById('dateDisplay').style.display = 'block';

    // Enable submit button
    document.getElementById('btnSubmit').disabled = false;
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