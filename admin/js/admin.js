// ========== ADMIN DASHBOARD JAVASCRIPT ==========

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Dashboard loaded');
    initializeFilters();
});

// Initialize filter functionality
function initializeFilters() {
    const filterSelect = document.querySelector('.filter-select');
    
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            filterBookingList(this.value);
        });
    }
}

// Filter booking list
function filterBookingList(filter) {
    console.log('Filter by:', filter);
    // TODO: Implement AJAX filter or page reload with filter param
}

// Show booking detail (will be implemented in next step)
function showBookingDetail(bookings) {
    console.log('Show detail for bookings:', bookings);
    
    // Preview in console for now
    if (Array.isArray(bookings) && bookings.length > 0) {
        console.table(bookings);
        
        // Next step: Show modal/detail panel
        alert(`${bookings.length} booking(s) found in this slot.\nNext step: Will show detailed modal.`);
    }
}

// Change month handler (called from inline script in dashboard.php)
function changeMonth() {
    const month = document.getElementById('monthSelect').value;
    const year = new Date().getFullYear();
    window.location.href = `dashboard.php?month=${month}&year=${year}&week=1`;
}

// Change week handler
function changeWeek() {
    const month = document.getElementById('monthSelect').value;
    const week = document.getElementById('weekSelect').value;
    const year = new Date().getFullYear();
    window.location.href = `dashboard.php?month=${month}&year=${year}&week=${week}`;
}

// Highlight current time slot (if viewing today)
function highlightCurrentTime() {
    const now = new Date();

    const hour = now.getHours();
    const minute = now.getMinutes();

    // Bulatkan ke slot 15 menit terdekat (KE BAWAH)
    const roundedMinute = Math.floor(minute / 15) * 15;

    const timeSlot = `${hour.toString().padStart(2, '0')}:${roundedMinute
        .toString()
        .padStart(2, '0')}`;

    // JS day: 0=Sunday, 1=Monday
    const jsDay = now.getDay();
    if (jsDay === 0) return; // skip Minggu

    // PHP kamu: Senin = 1
    const day = jsDay; 

    // Hapus highlight lama
    document.querySelectorAll('.booking-cell.current-slot').forEach(cell => {
        cell.classList.remove('current-slot');
    });

    // Cari slot yg cocok
    const currentCell = document.querySelector(
        `.booking-cell[data-time="${timeSlot}"][data-day="${day}"]`
    );

    if (currentCell) {
        currentCell.classList.add('current-slot');
    }
}

// Call highlight function
setTimeout(highlightCurrentTime, 500);

// Real-time clock (optional)
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('id-ID');
    
    // Update if clock element exists
    const clockEl = document.getElementById('live-clock');
    if (clockEl) {
        clockEl.textContent = timeString;
    }
}

// Update clock every second (optional)
// setInterval(updateClock, 1000);

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K: Focus search (if implemented)
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        // TODO: Focus search input
    }
    
    // ESC: Close modal (when implemented)
    if (e.key === 'Escape') {
        // TODO: Close any open modal
    }
});

// Export data functionality (for future use)
function exportBookings(format = 'csv') {
    console.log('Export bookings as:', format);
    alert('Export feature coming soon!');
}

// Print booking list
function printBookingList() {
    window.print();
}

// Utility: Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
}

// Utility: Format time
function formatTime(timeString) {
    return timeString.substring(0, 5) + ' WIB';
}

// Add smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});