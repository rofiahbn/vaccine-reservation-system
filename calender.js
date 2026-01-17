// calender.js - WITH BETTER DEBUG
console.log('📅 calender.js loaded');

// ================= DEBUG =================
console.log('🔍 Initial state:');
console.log('- bulanNow from PHP:', bulanNow);
console.log('- tahunNow from PHP:', tahunNow);
console.log('- namaBulanNow from PHP:', namaBulanNow);

// Check form and inputs
const monthForm = document.getElementById('monthForm');
const bulanInput = document.getElementById('inputBulan');
const tahunInput = document.getElementById('inputTahun');

console.log('🔍 Form check:');
console.log('- monthForm found:', !!monthForm);
console.log('- bulanInput found:', !!bulanInput, 'value:', bulanInput?.value);
console.log('- tahunInput found:', !!tahunInput, 'value:', tahunInput?.value);

// Check if there are duplicate forms
const allForms = document.querySelectorAll('form#monthForm');
console.log('🔍 Duplicate check:');
console.log('- Total forms with id="monthForm":', allForms.length);
if (allForms.length > 1) {
    console.error('❌ ERROR: Multiple forms with id="monthForm"!');
    allForms.forEach((form, index) => {
        console.log(`  Form ${index + 1}:`, form);
    });
}

// ================= NAVIGASI BULAN =================
function prevMonth() {
    console.log('⬅️ prevMonth() called');
    
    if (!bulanInput || !tahunInput) {
        console.error('❌ Input elements not found!');
        alert('Error: Form elements not found');
        return;
    }
    
    let bulan = parseInt(bulanInput.value);
    let tahun = parseInt(tahunInput.value);
    
    console.log('Current:', bulan, tahun);
    
    bulan--;
    if (bulan < 1) {
        bulan = 12;
        tahun--;
    }
    
    console.log('New:', bulan, tahun);
    
    bulanInput.value = bulan;
    tahunInput.value = tahun;
    
    if (monthForm) {
        console.log('Submitting form...');
        monthForm.submit();
    } else {
        console.error('❌ monthForm not found for submit!');
        // Fallback
        window.location.href = `order.php?bulan=${bulan}&tahun=${tahun}`;
    }
}

function nextMonth() {
    console.log('➡️ nextMonth() called');
    
    if (!bulanInput || !tahunInput) {
        console.error('❌ Input elements not found!');
        alert('Error: Form elements not found');
        return;
    }
    
    let bulan = parseInt(bulanInput.value);
    let tahun = parseInt(tahunInput.value);
    
    console.log('Current:', bulan, tahun);
    
    bulan++;
    if (bulan > 12) {
        bulan = 1;
        tahun++;
    }
    
    console.log('New:', bulan, tahun);
    
    bulanInput.value = bulan;
    tahunInput.value = tahun;
    
    if (monthForm) {
        console.log('Submitting form...');
        monthForm.submit();
    } else {
        console.error('❌ monthForm not found for submit!');
        // Fallback
        window.location.href = `order.php?bulan=${bulan}&tahun=${tahun}`;
    }
}

// ================= FUNGSI LAIN (sama) =================
let selectedDate = null;
let selectedTime = null;

function selectDate(element, tanggal) {
    console.log('📅 selectDate():', tanggal);
    
    // Reset previous
    document.querySelectorAll('.day.selected').forEach(day => {
        day.classList.remove('selected');
    });
    
    // Select new
    element.classList.add('selected');
    selectedDate = tanggal;
    
    // Get current bulan/tahun
    const bulan = parseInt(bulanInput.value);
    const tahun = parseInt(tahunInput.value);
    
    // Format date
    const formattedDate = `${tahun}-${bulan.toString().padStart(2, '0')}-${tanggal.toString().padStart(2, '0')}`;
    console.log('Formatted date:', formattedDate);
    
    document.getElementById('selectedDate').value = formattedDate;
    
    // Show selected
    const namaBulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    const bulanNama = namaBulan[bulan - 1] || 'Unknown';
    document.getElementById('dateText').textContent = `${tanggal} ${bulanNama} ${tahun}`;
    document.getElementById('dateDisplay').style.display = 'block';
    
    console.log('Selected:', `${tanggal} ${bulanNama} ${tahun}`);
    
    // Generate time slots
    generateTimeSlots();
}

function generateTimeSlots() {
    const container = document.getElementById('slotsContainer');
    const section = document.getElementById('timeSlots');
    
    if (!container || !section) return;
    
    container.innerHTML = '';
    
    // Generate slots
    for (let hour = 8; hour <= 16; hour++) {
        if (hour === 12) continue;
        
        ['00', '15', '30', '45'].forEach(minute => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'time-slot';
            button.textContent = `${hour.toString().padStart(2, '0')}:${minute}`;
            
            const available = Math.random() > 0.3;
            
            if (available) {
                button.onclick = function() {
                    selectTimeSlot(this, button.textContent);
                };
            } else {
                button.classList.add('disabled');
                button.disabled = true;
            }
            
            container.appendChild(button);
        });
    }
    
    section.style.display = 'block';
    document.getElementById('selectedTime').value = '';
    document.getElementById('btnSelesai').disabled = true;
}

function selectTimeSlot(element, waktu) {
    document.querySelectorAll('.time-slot.selected').forEach(slot => {
        slot.classList.remove('selected');
    });
    
    element.classList.add('selected');
    selectedTime = waktu;
    document.getElementById('selectedTime').value = waktu + ':00';
    document.getElementById('btnSelesai').disabled = false;
}

async function updateDayColorsFromDB() {
    const bulan = document.getElementById('inputBulan').value;
    const tahun = document.getElementById('inputTahun').value;
    
    const response = await fetch(`get_schedule.php?bulan=${bulan}&tahun=${tahun}`);
    const data = await response.json();
    
    // Update warna hari berdasarkan data database
    data.hari.forEach((day, index) => {
        const dayElement = document.getElementById(`day-${index+1}`);
        if (dayElement) {
            if (day.status === 'libur') {
                dayElement.classList.add('libur');
            }
        }
    });
}

function applyDatabaseColors(data) {
    console.log('🖌️ Applying database colors...');
    
    // Loop semua hari dalam response
    for (let tgl = 1; tgl <= data.jumlah_hari; tgl++) {
        const dayElement = document.getElementById(`day-${tgl}`);
        if (!dayElement) continue;
        
        const dayData = data.hari[tgl];
        if (!dayData) continue;
        
        console.log(`Day ${tgl}: ${dayData.status} - ${dayData.keterangan}`);
        
        // Hapus class lama (kecuali 'today')
        const isToday = dayElement.classList.contains('today');
        
        // Reset ke default
        dayElement.className = 'day';
        if (isToday) dayElement.classList.add('today');
        
        // Terapkan status dari database
        if (dayData.status === 'libur') {
            dayElement.classList.add('libur');
            dayElement.title = dayData.keterangan || 'Libur';
            dayElement.style.cursor = 'not-allowed';
            dayElement.onclick = null; // Nonaktifkan klik
        } 
        else if (dayData.status === 'tersedia') {
            // Aktifkan klik
            dayElement.onclick = function() {
                selectDate(this, tgl);
            };
            dayElement.style.cursor = 'pointer';
            
            // Weekend styling
            if (dayData.weekend) {
                dayElement.classList.add('weekend');
            }
        }
    }
    
    console.log('✅ Calendar colors updated from database');
}


// ================= INITIALIZE =================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM Ready');
    console.log('✅ Calendar functions ready');
});

// Expose
window.prevMonth = prevMonth;
window.nextMonth = nextMonth;
window.selectDate = selectDate;