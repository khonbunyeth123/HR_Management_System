document.addEventListener('DOMContentLoaded', () => {
    // --- Sidebar & Overlay Logic ---
    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggle = document.getElementById('sidebarToggle');

    if (sidebar && overlay && toggle) {
        const setSidebarOpen = (isOpen) => {
            sidebar.classList.toggle('-translate-x-full', !isOpen);
            overlay.classList.toggle('hidden', !isOpen);
            toggle.setAttribute('aria-expanded', String(isOpen));
            toggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
        };

        toggle.addEventListener('click', () => {
            setSidebarOpen(sidebar.classList.contains('-translate-x-full'));
        });

        overlay.addEventListener('click', () => setSidebarOpen(false));

        sidebar.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.matchMedia('(max-width: 767px)').matches) {
                    setSidebarOpen(false);
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setSidebarOpen(false);
            }
        });

        window.addEventListener('resize', () => {
            if (window.matchMedia('(min-width: 768px)').matches) {
                overlay.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // --- Toast Notification System ---
    window.Toast = {
        show: function(title, message, type = 'info', duration = 3000) {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icons = {
                success: 'mdi:check-circle',
                error: 'mdi:alert-circle',
                warning: 'mdi:alert',
                info: 'mdi:information'
            };

            toast.innerHTML = `
                <div class="toast-icon">
                    <span class="iconify w-full h-full" data-icon="${icons[type] || icons.info}"></span>
                </div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
            `;

            container.appendChild(toast);
            
            // Force reflow
            toast.offsetHeight;
            
            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        },
        success: function(title, message, duration) { this.show(title, message, 'success', duration); },
        error: function(title, message, duration) { this.show(title, message, 'error', duration); },
        warning: function(title, message, duration) { this.show(title, message, 'warning', duration); },
        info: function(title, message, duration) { this.show(title, message, 'info', duration); }
    };
});
