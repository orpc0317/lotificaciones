// Layout JavaScript
(function() {
    'use strict';

    // Menu structure
    const menuStructure = [
        {
            id: 'catalog',
            icon: 'bi-folder-fill',
            label: { es: 'Catálogo', en: 'Catalog' },
            submenu: [
                {
                    id: 'employees',
                    icon: 'bi-people-fill',
                    label: { es: 'Empleados', en: 'Employees' },
                    url: 'empleados'
                }
            ]
        }
    ];

    let currentLang = 'es';
    let translations = {};

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        initializeLayout();
        loadLanguagePreference();
        renderMenu();
        setupEventListeners();
        highlightActiveMenuItem();
    });

    function initializeLayout() {
        // Check if sidebar was collapsed
        const wasCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
        if (wasCollapsed) {
            document.getElementById('sidebar').classList.add('collapsed');
        }
    }

    function loadLanguagePreference() {
        // Get language from themeManager if available
        if (window.themeManager && typeof window.themeManager.getLanguage === 'function') {
            currentLang = window.themeManager.getLanguage();
        } else {
            currentLang = localStorage.getItem('lotificaciones_lang') || 'es';
        }
    }

    function renderMenu() {
        const menuContainer = document.getElementById('mainMenu');
        if (!menuContainer) return;

        menuContainer.innerHTML = '';

        menuStructure.forEach(item => {
            const li = document.createElement('li');
            li.className = 'menu-item';
            li.dataset.menuId = item.id;

            if (item.submenu && item.submenu.length > 0) {
                // Menu with submenu
                li.innerHTML = `
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#submenu-${item.id}">
                        <i class="bi ${item.icon}"></i>
                        <span class="menu-item-text">${item.label[currentLang]}</span>
                        <i class="bi bi-chevron-right expand-icon"></i>
                    </a>
                    <ul class="submenu collapse" id="submenu-${item.id}">
                        ${item.submenu.map(sub => `
                            <li>
                                <a href="${sub.url}" class="nav-link" data-page="${sub.id}">
                                    <i class="bi ${sub.icon} me-2" style="font-size: 0.9rem;"></i>
                                    <span>${sub.label[currentLang]}</span>
                                </a>
                            </li>
                        `).join('')}
                    </ul>
                `;

                // Add event listener for expand/collapse
                const link = li.querySelector('a[data-bs-toggle="collapse"]');
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    li.classList.toggle('expanded');
                });
            } else {
                // Simple menu item
                li.innerHTML = `
                    <a href="${item.url}" class="nav-link" data-page="${item.id}">
                        <i class="bi ${item.icon}"></i>
                        <span class="menu-item-text">${item.label[currentLang]}</span>
                    </a>
                `;
            }

            menuContainer.appendChild(li);
        });
    }

    function setupEventListeners() {
        // Toggle sidebar
        const toggleBtn = document.getElementById('toggleSidebar');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
            });
        }

        // Mobile toggle
        const toggleMobileBtn = document.getElementById('toggleSidebarMobile');
        if (toggleMobileBtn) {
            toggleMobileBtn.addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('show');
                
                // Add overlay
                let overlay = document.querySelector('.sidebar-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'sidebar-overlay';
                    document.body.appendChild(overlay);
                    overlay.addEventListener('click', function() {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                    });
                }
                overlay.classList.toggle('show');
            });
        }

        // Listen for language changes from theme.js
        window.addEventListener('languageChanged', function(e) {
            currentLang = e.detail.lang;
            renderMenu();
            highlightActiveMenuItem();
        });
    }

    function highlightActiveMenuItem() {
        // Get current page from URL
        const path = window.location.pathname;
        const page = path.split('/').pop() || 'index';

        // Remove all active classes
        document.querySelectorAll('.menu-item a').forEach(link => {
            link.classList.remove('active');
        });

        // Add active class to current page
        const activeLink = document.querySelector(`a[data-page="${page}"]`) || 
                          document.querySelector(`a[href="${page}"]`);
        
        if (activeLink) {
            activeLink.classList.add('active');
            
            // Expand parent menu if in submenu
            const submenu = activeLink.closest('.submenu');
            if (submenu) {
                submenu.classList.add('show');
                const parentMenuItem = submenu.closest('.menu-item');
                if (parentMenuItem) {
                    parentMenuItem.classList.add('expanded');
                }
            }
        }
    }

    // Export functions for external use
    window.layoutManager = {
        renderMenu: renderMenu,
        highlightActiveMenuItem: highlightActiveMenuItem,
        getCurrentLang: function() { return currentLang; }
    };
})();
