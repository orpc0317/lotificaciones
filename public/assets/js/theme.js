// Theme and Language Management
// Centralized functionality for theme palette and language switching
(function() {
    'use strict';

    let currentLang = 'es';
    let currentPalette = 'blue';

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeTheme();
        initializeLanguage();
    });

    // Initialize theme
    function initializeTheme() {
        // Load saved palette or default to blue
        currentPalette = localStorage.getItem('theme_palette') || 'blue';
        applyPalette(currentPalette);

        // Setup palette swatch click handlers
        document.querySelectorAll('.palette-swatch').forEach(swatch => {
            swatch.addEventListener('click', function() {
                const palette = this.dataset.palette;
                setPalette(palette);
            });
        });

        // Mark active swatch
        updateActiveSwatchIndicator();
    }

    // Apply palette to document
    function applyPalette(palette) {
        currentPalette = palette;
        document.documentElement.setAttribute('data-palette', palette);
        localStorage.setItem('theme_palette', palette);
        updateActiveSwatchIndicator();
        
        // Dispatch event for other components
        window.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { palette: palette } 
        }));
    }

    // Set and apply palette
    function setPalette(palette) {
        applyPalette(palette);
    }

    // Update active swatch visual indicator
    function updateActiveSwatchIndicator() {
        document.querySelectorAll('.palette-swatch').forEach(s => {
            s.classList.remove('active');
            if (s.dataset.palette === currentPalette) {
                s.classList.add('active');
            }
        });
    }

    // Initialize language
    function initializeLanguage() {
        // Load saved language or default to Spanish
        currentLang = localStorage.getItem('lotificaciones_lang') || 'es';
        
        // Set language selector value
        const langSelect = document.getElementById('langSelect');
        if (langSelect) {
            langSelect.value = currentLang;
            
            // Add change event listener
            langSelect.addEventListener('change', function() {
                setLanguage(this.value);
            });
        }
    }

    // Set language
    function setLanguage(lang) {
        currentLang = lang;
        localStorage.setItem('lotificaciones_lang', lang);
        
        // Dispatch event for other components
        window.dispatchEvent(new CustomEvent('languageChanged', { 
            detail: { lang: lang } 
        }));
        
        // If there's a page-specific reload function, call it
        if (typeof window.reloadOrBuild === 'function') {
            window.reloadOrBuild();
        }
        
        // If there's a layout menu to update, update it
        if (window.layoutManager && typeof window.layoutManager.renderMenu === 'function') {
            window.layoutManager.renderMenu();
        }
    }

    // Get current language
    function getCurrentLanguage() {
        return currentLang;
    }

    // Get current palette
    function getCurrentPalette() {
        return currentPalette;
    }

    // Export to window
    window.themeManager = {
        setPalette: setPalette,
        getPalette: getCurrentPalette,
        setLanguage: setLanguage,
        getLanguage: getCurrentLanguage,
        applyPalette: applyPalette
    };

})();
