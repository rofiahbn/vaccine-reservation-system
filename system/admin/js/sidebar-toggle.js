/**
 * Sidebar Toggle Script
 * Toggle sidebar antara expanded dan collapsed state
 */

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const logo = document.querySelector('.sidebar .logo');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    
    // Load saved state from localStorage
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true') {
        sidebar.classList.add('collapsed');
    }
    
    // Toggle function
    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        
        // Save state to localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
        
        // Optional: Trigger resize event untuk chart/graph yang mungkin perlu resize
        window.dispatchEvent(new Event('resize'));
    }
    
    // Click logo to toggle
    if (logo) {
        logo.addEventListener('click', toggleSidebar);
    }
    
    // Click toggle button (jika ada)
    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleSidebar);
    }
    
    // Add tooltips to nav items for collapsed state
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        const text = item.querySelector('span');
        if (text) {
            item.setAttribute('data-tooltip', text.textContent.trim());
        }
    });
});

function toggleSubmenu(el) {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar.classList.contains('collapsed')) return;

    const submenu = el.nextElementSibling;

    el.classList.toggle('open');
    submenu.classList.toggle('open');
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('collapsed');

    // kalau sidebar collapse → tutup semua submenu
    if (sidebar.classList.contains('collapsed')) {
        document.querySelectorAll('.submenu').forEach(menu => {
            menu.classList.remove('open');
        });

        document.querySelectorAll('.nav-item.has-submenu').forEach(item => {
            item.classList.remove('open');
        });
    }
}

// Optional: Auto-collapse on small screens
function checkScreenSize() {
    const sidebar = document.querySelector('.sidebar');
    if (window.innerWidth < 768) {
        sidebar.classList.add('collapsed');
    }
}

// Run on load
checkScreenSize();

// Run on resize
window.addEventListener('resize', function() {
    // Debounce resize event
    clearTimeout(window.resizeTimer);
    window.resizeTimer = setTimeout(checkScreenSize, 250);
});