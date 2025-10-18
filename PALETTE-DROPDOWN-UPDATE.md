# Palette Dropdown Update

## Overview
Converted the palette icon and color swatches into a dropdown selector with full internationalization support for better UX and cleaner interface.

## Changes Made

### 1. HTML Structure (Updated in 3 files)
- `app/views/layouts/main.php`
- `app/views/dashboard.php`
- `app/views/empleados.php`

**Old Structure:**
```html
<button id="btnPaletteHint" title="Palette">
    <i class="bi bi-palette"></i>
</button>
<div class="palette-swatch" data-palette="blue"></div>
<div class="palette-swatch" data-palette="teal"></div>
<div class="palette-swatch" data-palette="violet"></div>
```

**New Structure:**
```html
<div class="dropdown me-2">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center" 
            type="button" id="paletteDropdown" data-bs-toggle="dropdown">
        <i class="bi bi-palette me-1"></i>
        <span id="currentPaletteName" data-i18n="theme_blue">Blue</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item palette-option" href="#" data-palette="blue">
                <span class="palette-color-dot" style="background: var(--swatch-blue);"></span>
                <span data-i18n="theme_blue">Blue</span>
            </a>
        </li>
        <li>
            <a class="dropdown-item palette-option" href="#" data-palette="teal">
                <span class="palette-color-dot" style="background: var(--swatch-teal);"></span>
                <span data-i18n="theme_teal">Teal</span>
            </a>
        </li>
        <li>
            <a class="dropdown-item palette-option" href="#" data-palette="violet">
                <span class="palette-color-dot" style="background: var(--swatch-violet);"></span>
                <span data-i18n="theme_violet">Violet</span>
            </a>
        </li>
    </ul>
</div>
```

### 2. Internationalization (i18n)

**Translation Files Updated:**
- `public/assets/i18n/es.json` - Spanish translations
- `public/assets/i18n/en.json` - English translations

**New Translation Keys:**
```json
{
  "theme_palette": "Tema" / "Theme",
  "theme_blue": "Azul" / "Blue",
  "theme_teal": "Verde Azulado" / "Teal",
  "theme_violet": "Violeta" / "Violet"
}
```

**Spanish (es.json):**
- `theme_blue`: "Azul"
- `theme_teal`: "Verde Azulado"
- `theme_violet`: "Violeta"

**English (en.json):**
- `theme_blue`: "Blue"
- `theme_teal`: "Teal"
- `theme_violet`: "Violet"

### 2. JavaScript (`public/assets/js/theme.js`)

**Updated Functions:**
- `initializeTheme()` - Now targets `.palette-option` instead of `.palette-swatch`
- `applyPalette()` - Calls `updatePaletteDropdown()` instead of `updateActiveSwatchIndicator()`
- **NEW:** `updatePaletteDropdown()` - Updates dropdown button text using i18n and marks active option

**Key Changes:**
```javascript
// Setup palette dropdown click handlers
document.querySelectorAll('.palette-option').forEach(option => {
    option.addEventListener('click', function(e) {
        e.preventDefault();
        const palette = this.dataset.palette;
        setPalette(palette);
    });
});

// Update dropdown display with i18n support
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
    
    // Mark active option
    document.querySelectorAll('.palette-option').forEach(option => {
        if (option.dataset.palette === currentPalette) {
            option.classList.add('active');
        } else {
            option.classList.remove('active');
        }
    });
}
```

### 3. CSS Styling (`public/assets/css/style.css`)

**New Styles Added:**
```css
/* Palette Dropdown Styling */
#paletteDropdown {
    min-width: 120px;
}

.palette-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0.5rem 1rem;
}

.palette-option.active {
    background-color: var(--primary-600);
    color: white;
}

.palette-color-dot {
    display: inline-block;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 2px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
}
```

**Legacy Code:**
- Old `.palette-swatch` styles kept for backward compatibility

## Features

### User Experience
✅ Click the palette button to open dropdown
✅ Current theme name displayed on button (translated)
✅ Color dots next to each theme name
✅ Active theme highlighted in dropdown
✅ Smooth transitions on theme change
✅ Selection persists in localStorage
✅ **NEW:** Full internationalization support
✅ **NEW:** Theme names change with language selection

### Visual Design
- Dropdown menu aligned to the right
- Color preview dots with theme colors
- Active selection highlighted with theme color
- Hover effects on dropdown items
- Clean, modern Bootstrap styling

### Internationalization
- **Spanish (es)**: "Azul", "Verde Azulado", "Violeta"
- **English (en)**: "Blue", "Teal", "Violet"
- Theme names update automatically when language changes
- Uses existing I18nModule for consistency

## Testing Checklist

- [ ] Open any page with the palette dropdown
- [ ] Verify dropdown shows current theme name (default: "Blue" in English or "Azul" in Spanish)
- [ ] Click dropdown to see all three theme options with translated names
- [ ] Select Teal - verify button updates to "Teal" (English) or "Verde Azulado" (Spanish)
- [ ] Verify page colors change to teal theme
- [ ] Reload page - verify Teal theme persists with correct translation
- [ ] Change language from English to Spanish - verify theme name translates
- [ ] Change language from Spanish to English - verify theme name translates
- [ ] Test all three themes in both languages
- [ ] Test on dashboard.php, empleados.php, and any layout-based pages
- [ ] Verify dropdown menu doesn't overflow on mobile
- [ ] Test theme switching during operations (shouldn't break functionality)

## Browser Compatibility
- Requires Bootstrap 5 JavaScript for dropdown functionality
- Uses CSS variables (supported in all modern browsers)
- localStorage for persistence (IE11+)

## Migration Notes
- Old `.palette-swatch` elements completely removed from HTML
- JavaScript still compatible with old code if swatches exist elsewhere
- CSS classes kept for backward compatibility
- No database changes required
- No API changes required

## Future Enhancements
- Add more theme colors (e.g., green, orange, red)
- Add keyboard navigation (arrow keys)
- Add theme preview on hover
- Consider adding a "System Default" option
