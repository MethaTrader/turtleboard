import './bootstrap';
import Alpine from 'alpinejs';

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Initialize sidebar toggle and dropdown functionalities
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-64');
        });
    }

    // User profile dropdown
    const dropdownBtn = document.getElementById('profile-dropdown-btn');
    const dropdownMenu = document.getElementById('dropdown-menu');

    if (dropdownBtn && dropdownMenu) {
        dropdownBtn.addEventListener('click', function() {
            dropdownMenu.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('user-profile-dropdown');
            if (dropdown && !dropdown.contains(event.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    }
});