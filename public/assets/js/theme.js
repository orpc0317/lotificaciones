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
        initializeTranslations();
    });

    // Initialize translations
    function initializeTranslations() {
        // Load translations if I18nModule is available
        if (window.I18nModule && typeof window.I18nModule.loadTranslations === 'function') {
            const savedLang = localStorage.getItem('lotificaciones_lang') || 'es';
            window.I18nModule.loadTranslations(savedLang).then(function() {
                // Translations loaded successfully
                console.log('Translations loaded for:', savedLang);
                // Update palette dropdown after translations load
                updatePaletteDropdown();
            }).catch(function(err) {
                console.error('Error loading translations:', err);
            });
        }
        
        // Listen for language changes from other parts of the app
        window.addEventListener('languageChanged', function(e) {
            console.log('Language changed event received:', e.detail.lang);
            // Force palette dropdown update after language change
            setTimeout(function() {
                updatePaletteDropdown();
            }, 100);
        });
    }

    // Initialize theme
    function initializeTheme() {
        // Load saved palette or default to blue
        currentPalette = localStorage.getItem('theme_palette') || 'blue';
        applyPalette(currentPalette);

        // Setup palette dropdown click handlers
        document.querySelectorAll('.palette-option').forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                const palette = this.dataset.palette;
                setPalette(palette);
            });
        });

        // Update dropdown display
        updatePaletteDropdown();
    }

    // Apply palette to document
    function applyPalette(palette) {
        currentPalette = palette;
        document.documentElement.setAttribute('data-palette', palette);
        localStorage.setItem('theme_palette', palette);
        updatePaletteDropdown();
        
        // Dispatch event for other components
        window.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { palette: palette } 
        }));
    }

    // Set and apply palette
    function setPalette(palette) {
        applyPalette(palette);
    }

    // Update palette dropdown display
    function updatePaletteDropdown() {
        const currentPaletteNameEl = document.getElementById('currentPaletteName');
        if (currentPaletteNameEl) {
            // Update the data-i18n attribute to match the current palette
            const i18nKey = 'theme_' + currentPalette;
            currentPaletteNameEl.setAttribute('data-i18n', i18nKey);
            
            // Apply translations immediately using I18nModule
            if (window.I18nModule && typeof window.I18nModule.applyTranslations === 'function') {
                window.I18nModule.applyTranslations();
            }
        }

        // Mark active option in dropdown
        document.querySelectorAll('.palette-option').forEach(option => {
            const parent = option.parentElement;
            if (option.dataset.palette === currentPalette) {
                option.classList.add('active');
                if (parent) parent.classList.add('active');
            } else {
                option.classList.remove('active');
                if (parent) parent.classList.remove('active');
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
        
        // Load translations for new language
        if (window.I18nModule && typeof window.I18nModule.loadTranslations === 'function') {
            window.I18nModule.loadTranslations(lang).then(function() {
                console.log('Language changed to:', lang);
                // Update palette dropdown after translations load
                setTimeout(function() {
                    updatePaletteDropdown();
                }, 50);
            }).catch(function(err) {
                console.error('Error loading translations for language change:', err);
            });
        }
        
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
