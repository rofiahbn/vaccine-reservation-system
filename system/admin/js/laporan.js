// ===============================================
// LAPORAN - JavaScript
// ===============================================

// Fungsi untuk render chart pendapatan
function renderRevenueChart(trendData) {
    const ctx = document.getElementById('revenueChart');
    
    if (!ctx) return;
    
    const bulanIndo = [
        'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    
    const labels = trendData.map(d => {
        const date = new Date(d.tanggal);
        return date.getDate() + ' ' + bulanIndo[date.getMonth()];
    });
    
    const data = trendData.map(d => parseFloat(d.total));
    
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan',
                data: data,
                backgroundColor: '#fed7aa',
                borderColor: '#f59e0b',
                borderWidth: 0,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: false // <-- HILANGKAN GRID HORIZONTAL
                    },
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) return 'Rp ' + (value/1000000) + 'jt';
                            if (value >= 1000) return 'Rp ' + (value/1000) + 'rb';
                            return 'Rp ' + value;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false // <-- HILANGKAN GRID VERTIKAL
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        autoSkip: false
                    }
                }
            }
        }
    });
}

// Fungsi untuk filter dengan enter key
function handleSearchKeypress(event, tab) {
    if (event.key === 'Enter') {
        const searchValue = event.target.value;
        window.location.href = '?tab=' + tab + '&search=' + encodeURIComponent(searchValue);
    }
}

// Fungsi untuk export data
function exportToExcel(tab) {
    // Implementasi export ke Excel
    alert('Export to Excel: ' + tab);
}

function exportToPDF(tab) {
    // Implementasi export ke PDF
    alert('Export to PDF: ' + tab);
}

// Fungsi untuk print invoice
function printInvoice(bookingId) {
    window.open('print_invoice.php?id=' + bookingId, '_blank');
}

// Fungsi untuk send invoice
function sendInvoice(bookingId) {
    if (confirm('Kirim invoice ke email pasien?')) {
        fetch('send_invoice.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ booking_id: bookingId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Invoice berhasil dikirim!');
            } else {
                alert('Gagal mengirim invoice: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    }
}

// Initialize when document ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus search input
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const tab = new URLSearchParams(window.location.search).get('tab') || 'invoice';
                handleSearchKeypress(e, tab);
            }
        });
    }
});

function applyFilter() {
    const bulan = document.getElementById('bulan').value;
    const tahun = document.getElementById('tahun').value;
    const tab = new URLSearchParams(window.location.search).get('tab') || 'pendapatan';
    
    // Debug: lihat nilai yang akan dikirim
    console.log('Bulan:', bulan);
    console.log('Tahun:', tahun);
    console.log('Tab:', tab);
    
    // Cek apakah elemen ada
    console.log('Element bulan:', document.getElementById('bulan'));
    console.log('Element tahun:', document.getElementById('tahun'));
    
    window.location.href = `?tab=${tab}&bulan=${bulan}&tahun=${tahun}`;
}