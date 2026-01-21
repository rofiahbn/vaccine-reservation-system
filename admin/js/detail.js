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

        function rescheduleBooking(bookingId) {
            alert('Fitur reschedule akan segera hadir!');
            // TODO: Implement reschedule modal
        }

        function editBooking(bookingId) {
            // Redirect ke halaman edit
            window.location.href = `edit_booking.php?id=${bookingId}`;
        }

        function assignDoctors() {
            const selects = document.querySelectorAll('.doctorSelect');
            const doctorIds = [];

            selects.forEach(sel => {
                if(sel.value) doctorIds.push(sel.value);
            });

            if(doctorIds.length === 0) {
                alert('Silakan pilih minimal satu dokter!');
                return;
            }

            fetch('assign_doctor.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `booking_id=${bookingId}&doctor_ids=${doctorIds.join(',')}`
            })
            .then(res => res.text())
            .then(data => {
                alert('Dokter berhasil ditambahkan!');
                closeAddDoctorPopup();
                location.reload();
            })
            .catch(err => console.error(err));
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
            const newDropdown = firstDropdown.cloneNode(true); // clone dropdown pertama
            newDropdown.value = ""; // reset value
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