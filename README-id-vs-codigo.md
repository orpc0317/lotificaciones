# ID vs Codigo Strategy

## Overview
The application uses a dual-identifier system for employee records to balance technical requirements with user experience.

## Implementation Date
October 16, 2025

## The Two Identifiers

### 1. Numeric ID (`id`)
- **Type:** Integer (auto-increment)
- **Purpose:** Database primary key and internal routing
- **Visibility:** Hidden from users
- **Usage:**
  - Database relationships and foreign keys
  - URL routing (`/empleados/view/123`, `/empleados/edit/123`)
  - DataTable row identification
  - Backend operations
  - Cross-tab communication (when numeric ID is needed)

### 2. Employee Code (`codigo`)
- **Type:** String (e.g., "EMP67149d9f3e2d4")
- **Purpose:** User-friendly identifier
- **Visibility:** Displayed to users
- **Usage:**
  - Display in tables and forms
  - User communication
  - Reports and exports
  - BroadcastChannel cross-tab synchronization
  - Page titles and breadcrumbs

## Why Both?

### Numeric ID Benefits
✅ **Database Performance:** Integer joins are faster than string joins  
✅ **Guaranteed Uniqueness:** Auto-increment ensures no collisions  
✅ **Smaller Index Size:** Integers take less storage than strings  
✅ **URL Routing:** Clean, predictable URLs  
✅ **Stable Reference:** Never changes, even if codigo generation changes

### Codigo Benefits
✅ **User-Friendly:** Easier to communicate ("EMP123" vs "14789")  
✅ **Meaningful:** Can include prefixes/patterns  
✅ **Portable:** Can be used across systems  
✅ **Business Logic:** Can encode information (department, year, etc.)

## Current Implementation

### DataTable Configuration
```php
// In EmpleadoController::ajaxList()
$columns = [
    ['data' => null, 'title' => '', 'className' => 'no-export dt-no-colvis'], // Actions column
    ['data' => 'codigo', 'title' => 'Código'], // ✅ Visible to users
    ['data' => 'thumbnail', 'title' => 'Foto'],
    ['data' => 'id', 'title' => 'ID', 'visible' => false], // ✅ Hidden but available
    // ... other columns
];
```

### DataTable Rendering
```javascript
// In dataTable.js
obj.render = function(data, type, row) {
    // Uses row.id (numeric) for data-id attribute
    return '<a href="#" class="ver-ficha action-icon fs-5" data-id="' + row.id + '" title="Ver detalles"><i class="bi bi-eye-fill"></i></a>';
};
```

### URL Generation
```javascript
// In formHandlers.js
function handleView() {
    var id = $(this).data('id'); // Gets numeric ID from data attribute
    window.open(DataTableModule.api('empleados/view/' + encodeURIComponent(id)), '_blank');
}
```

### View Page Variables
```javascript
// In empleado_view.php
const empleadoId = 123; // ✅ Numeric ID for routing
const empleadoCodigo = "EMP67149d9f3e2d4"; // ✅ Code for display/BroadcastChannel
```

### Edit Page Variables
```javascript
// In empleado_edit.php
const empleadoNumericId = 123; // ✅ Numeric ID for routing
const empleadoId = "EMP67149d9f3e2d4"; // ✅ Code for BroadcastChannel (backward compat)
```

## Routes

### Route Patterns
```php
// In routes/web.php
// Match /empleados/view/{id} - Expects numeric ID
preg_match('#^/empleados/view/(\d+)$#', $uri, $matches)

// Match /empleados/edit/{id} - Expects numeric ID
preg_match('#^/empleados/edit/(\d+)$#', $uri, $matches)
```

The `(\d+)` pattern ensures only numeric IDs are accepted.

## Data Flow Examples

### Viewing an Employee
1. User clicks View button in table
2. Button has `data-id="123"` (numeric ID from hidden column)
3. JavaScript opens: `/empleados/view/123`
4. Route matches and calls `EmpleadoController::view(123)`
5. Controller queries: `SELECT * FROM empleados WHERE id = 123`
6. View displays codigo "EMP67149d9f3e2d4" to user

### Editing an Employee
1. User clicks Edit button on view page
2. JavaScript uses `empleadoId = 123` (numeric)
3. Navigates to: `/empleados/edit/123`
4. Route matches and calls `EmpleadoController::edit(123)`
5. Edit page loads with both:
   - `empleadoNumericId = 123` (for save/cancel redirects)
   - `empleadoId = "EMP67149d9f3e2d4"` (for BroadcastChannel)

### Cross-Tab Communication
Uses `codigo` (not numeric ID) because:
- BroadcastChannel messages should be stable
- Codigo is unique and meaningful
- Multiple tabs might refer to same employee by codigo

```javascript
// BroadcastChannel uses codigo
editChannel.postMessage({ 
    action: 'lock-check', 
    empleadoId: empleadoCodigo // "EMP67149d9f3e2d4"
});
```

## Benefits of This Architecture

### 1. Performance
- Database queries use indexed integer ID
- Fast lookups and joins
- Minimal storage overhead

### 2. User Experience
- Users see meaningful codigo: "EMP67149d9f3e2d4"
- ID is hidden complexity
- Clean, professional interface

### 3. Flexibility
- Can change codigo generation without breaking references
- Numeric ID remains stable
- Easy to add validation rules to codigo

### 4. Security
- Sequential IDs not exposed to users
- Harder to enumerate records
- Codigo can be randomized

### 5. Maintainability
- Clear separation of concerns
- Consistent pattern across all pages
- Easy to understand for developers

## Common Pitfalls (Avoided)

### ❌ Using Codigo for Routes
```javascript
// WRONG - Routes expect numeric ID
window.location.href = `empleados/edit/EMP67149d9f3e2d4`;
// Result: 404 - route pattern (\d+) doesn't match
```

### ❌ Using ID for Display
```php
<!-- WRONG - Users don't understand numeric IDs -->
<h1>Employee #123</h1>
<!-- CORRECT - Use codigo -->
<h1>Employee #EMP67149d9f3e2d4</h1>
```

### ❌ Hiding ID Column from DataTable
```php
// WRONG - ID not available in JavaScript
// Don't include 'id' column at all

// CORRECT - Include but hide
['data' => 'id', 'title' => 'ID', 'visible' => false]
```

## Testing Checklist

- [ ] View button uses numeric ID in URL
- [ ] Edit button uses numeric ID in URL
- [ ] Page titles display codigo, not numeric ID
- [ ] Database queries use numeric ID
- [ ] BroadcastChannel uses codigo
- [ ] ID column hidden in DataTable
- [ ] Codigo visible in DataTable
- [ ] Routes accept numeric IDs
- [ ] Routes reject non-numeric IDs

## Migration Notes

If you ever need to change the codigo format:
1. Numeric ID remains unchanged ✅
2. Update codigo generation logic
3. Update codigo in database
4. No URL changes needed
5. No route changes needed
6. Only display updates needed

## Related Documentation

- [Multi-Tab Navigation](README-multi-tab-navigation.md)
- [Server-Side Processing](README-server-side-processing.md)
- [Modular Architecture](README-modular-architecture.md)

## Conclusion

The dual-identifier system provides:
- **Technical excellence:** Fast, reliable database operations
- **User experience:** Meaningful, user-friendly identifiers
- **Maintainability:** Clear separation between internal and external IDs
- **Flexibility:** Easy to change codigo without breaking system

**Status:** ✅ Implemented and working correctly
