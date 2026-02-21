function updateStatus(bookingId, newStatus) {
            fetch('update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `booking_id=${bookingId}&status=${newStatus}`
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    alert('Status updated!');
                    location.reload(); // Refresh page biar status terbaru terlihat
                } else {
                    alert('Update failed');
                }
            })
            .catch(err => console.error(err));
        }

        function assignDoctors() {
            const selects = document.querySelectorAll('.doctorSelect');
            let doctorIds = [];

            selects.forEach(sel => {
                if (sel.value) doctorIds.push(sel.value);
            });

            if (doctorIds.length === 0) {
                alert('Pilih minimal 1 dokter');
                return;
            }

            // Tampilkan loading
            const selesaiBtn = document.querySelector('#addDoctorPopup .popup-content button[onclick="assignDoctors()"]');
            const originalText = selesaiBtn.innerHTML;
            selesaiBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            selesaiBtn.disabled = true;

            fetch('assign_doctor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    booking_id: bookingId,
                    doctor_ids: doctorIds,
                    mode: 'add'  // <-- PENTING: mode add
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeAddDoctorPopup();
                    location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                    selesaiBtn.innerHTML = originalText;
                    selesaiBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Terjadi kesalahan koneksi');
                selesaiBtn.innerHTML = originalText;
                selesaiBtn.disabled = false;
            });
        }

        function openAddDoctorPopup() {
            document.getElementById('addDoctorPopup').style.display = 'flex';
        }

        function closeAddDoctorPopup() {
            document.getElementById('addDoctorPopup').style.display = 'none';
        }

        function addDoctorDropdown() {
            const container = document.getElementById('doctorContainer');
            const firstDropdown = container.querySelector('select');
            const newDropdown = firstDropdown.cloneNode(true);
            newDropdown.value = "";
            container.appendChild(newDropdown);
        }

        function removeStaff(bookingId, staffId) {
            if(!confirm('Apakah kamu yakin ingin menghapus staff ini?')) return;

            fetch('remove_staff.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `booking_id=${bookingId}&staff_id=${staffId}`
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Staff berhasil dihapus!');
                    document.getElementById(`staff-${staffId}`).remove();
                } else {
                    alert('Gagal menghapus staff!');
                }
            })
            .catch(err => console.error(err));
        }

function cancelBooking(button, bookingId) {
    if (!confirm('Apakah kamu yakin ingin membatalkan booking ini?')) return;

    // Disable tombol langsung
    button.disabled = true;
    button.style.opacity = 0.5;
    button.style.cursor = 'not-allowed';

    // Kirim request ke server
    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `booking_id=${bookingId}&status=cancelled`
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            alert('Booking dibatalkan!');
            // opsional: update status badge di halaman
            const badge = document.querySelector('.status-badge-large');
            if(badge) {
                badge.textContent = 'Pesanan Dibatalkan';
                badge.className = 'status-badge-large cancelled';
            }
        } else {
            alert('Gagal membatalkan booking!');
            // re-enable tombol kalau gagal
            button.disabled = false;
            button.style.opacity = 1;
            button.style.cursor = 'pointer';
        }
    })
    .catch(err => {
        console.error(err);
        // re-enable tombol kalau error
        button.disabled = false;
        button.style.opacity = 1;
        button.style.cursor = 'pointer';
    });
}

let activeParticipantIndex = 0;

function showParticipant(index) {
    activeParticipantIndex = index;

    document.querySelectorAll('.participant-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.participant-tab').forEach(t => t.classList.remove('active'));

    document.getElementById('participant-' + index).classList.add('active');
    document.querySelectorAll('.participant-tab')[index].classList.add('active');
}
