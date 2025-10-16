# Theme and Language Management Centralization

## Summary of Changes

### Problem
- The sidebar menu was using theme colors that were hard to see
- Theme and language functionality was duplicated across multiple files
- No centralized management of theme palette and language settings

### Solution
Created a centralized theme management system that:
1. Handles theme palette switching (Blue/Teal/Violet)
2. Manages language switching (Spanish/English)
3. Synchronizes settings across all pages
4. Uses localStorage for persistence
5. Dispatches events for component communication

## Files Created

### 1. `public/assets/js/theme.js` (NEW)
**Purpose**: Centralized theme and language management

**Features**:
- `themeManager.setPalette(palette)` - Change theme color
- `themeManager.getPalette()` - Get current palette
- `themeManager.setLanguage(lang)` - Change language
- `themeManager.getLanguage()` - Get current language
- Automatic initialization on page load
- Event dispatching for theme/language changes
- LocalStorage persistence

**Events Dispatched**:
- `themeChanged` - When theme palette changes
- `languageChanged` - When language changes

## Files Modified

### 2. `public/assets/js/layout.js`
**Changes**:
- ✅ Removed duplicate palette switching code
- ✅ Removed duplicate language selector handling
- ✅ Added event listener for `languageChanged` from theme.js
- ✅ Simplified `loadLanguagePreference()` to use themeManager
- ✅ Menu re-renders when language changes via event

### 3. `public/assets/js/empleados.js`
**Changes**:
- ✅ Removed `applyPalette()` function (now in theme.js)
- ✅ Removed palette swatch click handlers (now in theme.js)
- ✅ Removed localStorage palette management (now in theme.js)
- ✅ Uses centralized theme manager

### 4. `app/views/dashboard.php`
**Changes**:
- ✅ Added `<script src="assets/js/theme.js"></script>` before layout.js
- ✅ Ensures theme.js loads first for proper initialization

### 5. `app/views/empleados.php`
**Changes**:
- ✅ Added `<script src="assets/js/theme.js"></script>` before layout.js
- ✅ Ensures theme.js loads first for proper initialization

## CSS (Already Working)

### `public/assets/css/layout.css`
- Sidebar already uses CSS custom properties: `var(--primary-700)`, `var(--primary-800)`
- No changes needed - automatically responds to theme changes

### `public/assets/css/theme.css`
- Defines color palettes using `data-palette` attribute
- Supports blue, teal, and violet themes
- Applied to `:root[data-palette="..."]`

## How It Works

### Theme Palette Flow:
1. User clicks a palette swatch (blue/teal/violet)
2. `theme.js` captures the click
3. Sets `data-palette` attribute on `<html>` element
4. Saves preference to localStorage
5. Dispatches `themeChanged` event
6. CSS variables update automatically via `:root[data-palette="..."]` rules
7. **Sidebar colors change immediately** because they use `var(--primary-700)` and `var(--primary-800)`

### Language Flow:
1. User changes language selector
2. `theme.js` captures the change
3. Saves language to localStorage
4. Dispatches `languageChanged` event
5. `layout.js` listens for event and re-renders menu
6. `empleados.js` listens for event and calls `reloadOrBuild()`
7. All UI elements update to new language

## Benefits

✅ **Single Source of Truth**: All theme/language logic in one place  
✅ **No Code Duplication**: Removed redundant palette code from empleados.js  
✅ **Automatic Sync**: All pages use same theme/language settings  
✅ **Event-Driven**: Components communicate via custom events  
✅ **Persistent**: Settings saved in localStorage  
✅ **Modular**: Easy to add new pages that automatically get theme/language support  
✅ **Sidebar Visibility**: Sidebar now properly uses theme colors and is always visible  

## Theme Color Variables

The sidebar uses these CSS variables (defined in theme.css):

```css
:root[data-palette="blue"] {
  --primary-600: #2563eb;
  --primary-700: #1d4ed8;
  --primary-800: #1e40af;
}

:root[data-palette="teal"] {
  --primary-600: #14b8a6;
  --primary-700: #0f766e;
  --primary-800: #115e59;
}

:root[data-palette="violet"] {
  --primary-600: #8b5cf6;
  --primary-700: #7c3aed;
  --primary-800: #6d28d9;
}
```

The sidebar gradient uses:
```css
background: linear-gradient(180deg, var(--primary-700) 0%, var(--primary-800) 100%);
```

## Testing

To verify everything works:

1. **Test Theme Switching**:
   - Click Blue/Teal/Violet palette swatches
   - Sidebar should change colors immediately
   - Refresh page - theme should persist

2. **Test Language Switching**:
   - Change language selector between ES/EN
   - Menu should update: "Catálogo/Empleados" ↔ "Catalog/Employees"
   - DataTable should reload with new language
   - Refresh page - language should persist

3. **Test Cross-Page Sync**:
   - Change theme on Dashboard
   - Navigate to Employees
   - Theme should be the same
   - Same for language

## Script Load Order (Important!)

```html
<!-- Must load in this order: -->
<script src="assets/js/theme.js"></script>      <!-- 1. Theme manager first -->
<script src="assets/js/layout.js"></script>     <!-- 2. Layout uses theme manager -->
<script src="assets/js/empleados.js"></script>  <!-- 3. Page scripts use theme manager -->
```

## Future Enhancements

- Add more theme palettes (red, green, orange, etc.)
- Add dark mode toggle
- Add font size preferences
- Add accessibility settings
- Add user profile integration
