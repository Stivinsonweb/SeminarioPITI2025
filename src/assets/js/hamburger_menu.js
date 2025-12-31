(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const menuItems = document.querySelectorAll('.sidebar .menu-item, .btn-logout-sidebar');
        
        if (!hamburgerBtn || !sidebar || !sidebarOverlay) {
            console.error('Error: Elementos del menú hamburguesa no encontrados');
            console.log('hamburgerBtn:', hamburgerBtn);
            console.log('sidebar:', sidebar);
            console.log('sidebarOverlay:', sidebarOverlay);
            return;
        }
        
        function toggleSidebar() {
            const isActive = sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            
            const icon = hamburgerBtn.querySelector('i');
            if (isActive) {
                icon.classList.remove('bi-list');
                icon.classList.add('bi-x-lg');
                hamburgerBtn.setAttribute('aria-label', 'Cerrar menú');
                document.body.style.overflow = 'hidden';
            } else {
                icon.classList.remove('bi-x-lg');
                icon.classList.add('bi-list');
                hamburgerBtn.setAttribute('aria-label', 'Abrir menú');
                document.body.style.overflow = '';
            }
        }
        
        function closeSidebar() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            const icon = hamburgerBtn.querySelector('i');
            icon.classList.remove('bi-x-lg');
            icon.classList.add('bi-list');
            hamburgerBtn.setAttribute('aria-label', 'Abrir menú');
            document.body.style.overflow = '';
        }
        
        hamburgerBtn.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', closeSidebar);
        
        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });
        
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (window.innerWidth > 768 && sidebar.classList.contains('active')) {
                    closeSidebar();
                }
            }, 250);
        });
        
        console.log('✅ Menú hamburguesa inicializado correctamente');
    });
})();