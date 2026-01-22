/* ===================================================
   RESCHEDULE MODAL - MENGGUNAKAN SISTEM ORDER.PHP
=================================================== */

let currentDateReschedule = new Date();
let selectedDateReschedule = null;
let selectedTimeReschedule = null;

const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

// ========== BUKA MODAL ==========
function openRescheduleModal() {
    document.getElementById('rescheduleModal').style.display = 'flex';
    currentDateReschedule = new Date();
    renderCalendarReschedule();
}

// ========== TUTUP MODAL ==========
function closeRescheduleModal() {
    document.getElementById('rescheduleModal').style.display = 'none';
    selectedDateReschedule = null;
    selectedTimeReschedule = null;
    document.getElementById('selectedNewDate').value = '';
    document.getElementById('selectedNewTime').value = '';
    document.getElementById('dateDisplayReschedule').style.display = 'none';
    document.getElementById('timeSlotsSection').style.display = 'none';
    document.getElementById('btnSubmitReschedule').disabled = true;
}

// ========== GANTI BULAN ==========
function changeMonthReschedule(direction) {
    currentDateReschedule.setMonth(currentDateReschedule.getMonth() + direction);
    renderCalendarReschedule();
}

// ========== RENDER KALENDER ==========
async function renderCalendarReschedule() {
    const year = currentDateReschedule.getFullYear();
    const month = currentDateReschedule.getMonth();
    
    // Update header
    document.getElementById('currentMonthYear').textContent = `${monthNames[month]} ${year}`;
    
    // Hitung hari pertama dan jumlah hari
    const firstDay = new Date(year, month, 1).getDay();
    const adjustedFirstDay = firstDay === 0 ? 6 : firstDay - 1; // Senin = 0
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    const container = document.getElementById('calendarDaysReschedule');
    
    // Hapus semua hari kecuali header
    const headers = container.querySelectorAll('.day-header');
    container.innerHTML = '';
    headers.forEach(h => container.appendChild(h));
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Empty days
    for (let i = 0; i < adjustedFirstDay; i++) {
        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'day empty';
        container.appendChild(emptyDiv);
    }
    
    // Render tanggal
    for (let day = 1; day <= daysInMonth; day++) {
        const dateObj = new Date(year, month, day);
        const dateStr = formatDateStr(dateObj);
        const isToday = dateObj.getTime() === today.getTime();
        
        const dayDiv = document.createElement('div');
        dayDiv.className = 'day';
        dayDiv.textContent = day;
        
        if (isToday) {
            dayDiv.classList.add('today');
        }
        
        // Disable tanggal yang sudah lewat
        if (dateObj < today) {
            dayDiv.classList.add('disabled', 'past-date');
            dayDiv.title = 'Tanggal sudah lewat';
        } else {
            // Cek status tanggal dari server
            await checkDateStatusReschedule(dateStr, dayDiv, dateObj);
        }
        
        container.appendChild(dayDiv);
    }
}

// ========== CEK STATUS TANGGAL ==========
async function checkDateStatusReschedule(dateStr, dayDiv, dateObj) {
    try {
        const response = await fetch(`../admin/check_date_status.php?tanggal=${dateStr}`);
        const data = await response.json();
        
        if (!data.success) {
            return;
        }
        
        if (data.is_holiday) {
            dayDiv.classList.add('holiday');
            dayDiv.title = 'Libur: ' + data.holiday_name;
        } else if (data.is_closed) {
            dayDiv.classList.add('closed');
            dayDiv.title = 'Klinik tutup';
        } else if (data.is_full) {
            dayDiv.classList.add('full');
            dayDiv.title = 'Jadwal penuh';
        } else {
            // Tanggal bisa diklik
            dayDiv.style.cursor = 'pointer';
            dayDiv.title = 'Klik untuk pilih jadwal';
            dayDiv.addEventListener('click', function() {
                selectDateReschedule(dateObj, dayDiv);
            });
        }
    } catch (error) {
        console.error('Error checking date:', error);
    }
}

// ========== PILIH TANGGAL ==========
async function selectDateReschedule(dateObj, dayDiv) {
    selectedDateReschedule = dateObj;
    selectedTimeReschedule = null;
    
    // Update UI
    document.querySelectorAll('#calendarDaysReschedule .day').forEach(d => {
        d.classList.remove('selected');
    });
    dayDiv.classList.add('selected');
    
    // Update hidden input
    const dateStr = formatDateStr(dateObj);
    document.getElementById('selectedNewDate').value = dateStr;
    document.getElementById('selectedNewTime').value = '';
    
    // Tampilkan tanggal yang dipilih
    const dateText = `${dateObj.getDate()} ${monthNames[dateObj.getMonth()]} ${dateObj.getFullYear()}`;
    document.getElementById('dateTextReschedule').textContent = dateText;
    document.getElementById('dateDisplayReschedule').style.display = 'block';
    
    // Load time slots
    await loadTimeSlotsReschedule(dateStr);
    
    // Disable submit button
    document.getElementById('btnSubmitReschedule').disabled = true;
}

// ========== LOAD TIME SLOTS ==========
async function loadTimeSlotsReschedule(dateStr) {
    const container = document.getElementById('timeSlots');
    const section = document.getElementById('timeSlotsSection');
    
    container.innerHTML = '<p style="text-align:center; color:#6b7280;">Loading...</p>';
    section.style.display = 'block';
    
    try {
        const response = await fetch(`../check_slots.php?tanggal=${dateStr}`);
        const data = await response.json();
        
        if (!data.success) {
            container.innerHTML = '<p style="text-align:center; color:#dc2626;">Gagal memuat jam</p>';
            return;
        }
        
        if (data.is_holiday) {
            container.innerHTML = `<p style="text-align:center; color:#92400e;">Libur: ${data.holiday_name}</p>`;
            return;
        }
        
        if (data.is_closed) {
            container.innerHTML = '<p style="text-align:center; color:#991b1b;">Klinik tutup di hari ini</p>';
            return;
        }
        
        // Render time slots
        container.innerHTML = '';
        
        if (!data.all_slots || data.all_slots.length === 0) {
            container.innerHTML = '<p style="text-align:center; color:#6b7280;">Tidak ada jam tersedia</p>';
            return;
        }
        
        data.all_slots.forEach(time => {
            const isBooked = data.booked.includes(time);
            
            const slotDiv = document.createElement('div');
            slotDiv.className = 'time-slot-reschedule';
            slotDiv.textContent = time;
            
            if (isBooked) {
                slotDiv.classList.add('full');
                slotDiv.title = 'Slot sudah terisi';
            } else {
                slotDiv.addEventListener('click', function() {
                    selectTimeReschedule(time, slotDiv);
                });
            }
            
            container.appendChild(slotDiv);
        });
        
    } catch (error) {
        console.error('Error loading slots:', error);
        container.innerHTML = '<p style="text-align:center; color:#dc2626;">Terjadi kesalahan</p>';
    }
}

// ========== PILIH WAKTU ==========
function selectTimeReschedule(time, slotDiv) {
    selectedTimeReschedule = time;
    
    // Update UI
    document.querySelectorAll('.time-slot-reschedule').forEach(s => {
        s.classList.remove('selected');
    });
    slotDiv.classList.add('selected');
    
    // Update hidden input
    document.getElementById('selectedNewTime').value = time;
    
    // Enable submit button
    document.getElementById('btnSubmitReschedule').disabled = false;
}

// ========== FORMAT TANGGAL ==========
function formatDateStr(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// ========== SUBMIT RESCHEDULE ==========
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('rescheduleForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const bookingId = document.querySelector('input[name="booking_id"]').value;
            const newDate = document.getElementById('selectedNewDate').value;
            const newTime = document.getElementById('selectedNewTime').value;
            
            if (!newDate || !newTime) {
                alert('Pilih tanggal dan waktu terlebih dahulu!');
                return;
            }
            
            const dateObj = new Date(newDate);
            const dateText = `${dateObj.getDate()} ${monthNames[dateObj.getMonth()]} ${dateObj.getFullYear()}`;
            const confirmMsg = `Yakin ingin ubah jadwal ke ${dateText} pukul ${newTime} WIB?`;
            
            if (!confirm(confirmMsg)) return;
            
            const btnSubmit = document.getElementById('btnSubmitReschedule');
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Memproses...';
            
            try {
                const formData = new FormData();
                formData.append('booking_id', bookingId);
                formData.append('new_date', newDate);
                formData.append('new_time', newTime);
                
                const response = await fetch('reschedule_booking.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('✅ Jadwal berhasil diubah!');
                    closeRescheduleModal();
                    location.reload();
                } else {
                    alert('❌ Gagal: ' + result.message);
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Jadwalkan Ulang';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ Terjadi kesalahan!');
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Jadwalkan Ulang';
            }
        });
    }
});

// Alias untuk backward compatibility
function rescheduleBooking() {
    openRescheduleModal();
}