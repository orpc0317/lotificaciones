# Multi-Tab Record-Level Navigation System

## Overview
This document describes the implementation of a multi-tab, record-level navigation system for the Lotificaciones application, inspired by desktop ERP systems like Microsoft Dynamics.

## System Architecture

### URL Structure
The system uses clean, RESTful URLs with record-specific routes:

```
/empleados                      → Employee list view
/empleados/view/{id}           → Read-only employee record view
/empleados/edit/{id}           → Edit mode for employee record
```

### Key Features
1. **Multi-Tab Support**: Users can open multiple employees in separate browser tabs
2. **Dynamic Tab Titles**: Each tab shows "Employee #ID - Name" for easy identification
3. **Edit Conflict Detection**: Warns when the same employee is being edited in multiple tabs
4. **Cross-Tab Synchronization**: Updates are broadcasted to all open tabs
5. **Read-Only View First**: View button opens read-only, with explicit "Edit" action required

## Implementation Details

### 1. Routes (routes/web.php)
Added dynamic route handling with regex pattern matching:

```php
// Match /empleados/view/{id}
if (preg_match('#^/empleados/view/(\d+)$#', $uri, $matches)) {
    $id = $matches[1];
    (new EmpleadoController())->view($id);
    break;
}

// Match /empleados/edit/{id}
if (preg_match('#^/empleados/edit/(\d+)$#', $uri, $matches)) {
    $id = $matches[1];
    (new EmpleadoController())->edit($id);
    break;
}
```

### 2. Controller Methods (app/Controllers/EmpleadoController.php)
Added two new methods:

**view($id)**: Loads employee data and renders read-only view
- Fetches employee record
- Loads departments and positions for display
- Renders `empleado_view.php`

**edit($id)**: Loads employee data and renders edit form
- Fetches employee record
- Loads departments and positions for dropdowns
- Renders `empleado_edit.php`

### 3. View Pages

#### empleado_view.php (Read-Only)
- Displays all employee information in read-only format
- 6-tab interface (General, Contact, Employment, Emergency, Medical, Other)
- Action buttons: Close, Back to List, Edit
- Dynamic page title: "Employee #ID - Name | Lotificaciones"
- i18n support for all labels

#### empleado_edit.php (Edit Mode)
- Full edit form with all employee fields
- Same 6-tab interface as view mode
- Photo upload with preview
- Form change tracking (warns before leaving with unsaved changes)
- Edit conflict detection with visual warning
- Action buttons: Save, Cancel, View Mode, Back to List
- Dynamic page title: "Editing Employee #ID - Name | Lotificaciones"

### 4. DataTable Integration (public/assets/js/empleados.js)

Modified row action handlers to open new tabs:

```javascript
// View button - opens read-only view in new tab
$(document).on('click', '.ver-ficha', function(e){ 
    e.preventDefault();
    var id = $(this).data('id'); 
    window.open(api('empleados/view/' + id), '_blank');
});

// Edit button - opens edit mode in new tab
$(document).on('click', '.editar', function(e){ 
    e.preventDefault();
    var id = $(this).data('id'); 
    window.open(api('empleados/edit/' + id), '_blank');
});
```

### 5. Cross-Tab Communication

Uses the **Broadcast Channel API** for real-time synchronization across tabs.

#### Edit Page (empleado_edit.php)
```javascript
const editChannel = new BroadcastChannel('lotificaciones-employee-edit');

// Announce editing started
editChannel.postMessage({ 
    action: 'edit-started', 
    empleadoId: empleadoId 
});

// Listen for conflicts
editChannel.onmessage = (event) => {
    if (event.data.action === 'edit-started' && 
        event.data.empleadoId === empleadoId) {
        showEditLockWarning(); // Show warning banner
    }
};

// Notify on save
editChannel.postMessage({ 
    action: 'data-updated', 
    empleadoId: empleadoId 
});
```

#### List Page (empleados.js)
```javascript
const employeeChannel = new BroadcastChannel('lotificaciones-employee-edit');

// Listen for updates
employeeChannel.onmessage = (event) => {
    if (event.data.action === 'data-updated') {
        showUpdateNotification();
        tabla.reloadData(); // Refresh DataTable
    }
};
```

### 6. Form Change Tracking

Edit page tracks form modifications to prevent accidental data loss:

```javascript
let formModified = false;

// Track changes
form.querySelectorAll('input, select, textarea').forEach(input => {
    input.addEventListener('change', () => {
        formModified = true;
    });
});

// Warn before leaving
window.addEventListener('beforeunload', (e) => {
    if (formModified) {
        e.preventDefault();
        e.returnValue = ''; // Shows browser warning
    }
});
```

## User Workflows

### Viewing an Employee
1. User clicks "View" (eye icon) on any employee row
2. New tab opens with `/empleados/view/{id}`
3. Tab title shows "Employee #1005 - Juan Pérez | Lotificaciones"
4. User can:
   - Browse all employee information
   - Click "Edit" to switch to edit mode
   - Close tab or return to list

### Editing an Employee
1. User clicks "Edit" (pencil icon) on any employee row
2. New tab opens with `/empleados/edit/{id}`
3. Tab title shows "Editing Employee #1005 - Juan Pérez | Lotificaciones"
4. If another tab is already editing this employee:
   - Warning banner appears: "This employee is being edited in another tab"
5. User makes changes
6. If they try to leave without saving:
   - Browser shows "You have unsaved changes" warning
7. On save:
   - Form submits to `/empleados/update`
   - All other tabs are notified
   - List views automatically refresh
   - User redirected to view mode

### Working with Multiple Employees
1. User can have 10+ employee tabs open simultaneously
2. Each tab has unique URL and title
3. Browser manages tab switching (Alt+Tab, Ctrl+Tab)
4. Users can:
   - Compare employees side-by-side
   - Copy data between records
   - Work on multiple tasks in parallel

## Broadcast Channel Events

### Event Types

| Event | Payload | Purpose |
|-------|---------|---------|
| `edit-started` | `{ action, empleadoId }` | Notify other tabs editing has begun |
| `edit-closed` | `{ action, empleadoId }` | Notify other tabs editing has ended |
| `data-updated` | `{ action, empleadoId }` | Notify data was saved |

### Channel Name
`lotificaciones-employee-edit`

## Browser Compatibility

### Broadcast Channel API Support
- ✅ Chrome 54+
- ✅ Firefox 38+
- ✅ Edge 79+
- ✅ Safari 15.4+
- ❌ Internet Explorer (not supported)

**Fallback**: System gracefully degrades - multi-tab navigation still works, but without conflict warnings.

## i18n Support

### New Translation Keys Added

Spanish (es.json):
```json
{
  "employee": "Empleado",
  "editing": "Editando",
  "employee_information": "Información del Empleado",
  "back_to_list": "Volver a la lista",
  "view_mode": "Ver sin editar",
  "edit_conflict_warning": "Este empleado está siendo editado en otra pestaña.",
  "warning": "¡Advertencia!"
}
```

English (en.json):
```json
{
  "employee": "Employee",
  "editing": "Editing",
  "employee_information": "Employee Information",
  "back_to_list": "Back to list",
  "view_mode": "View without editing",
  "edit_conflict_warning": "This employee is being edited in another tab.",
  "warning": "Warning!"
}
```

## Testing Checklist

### Functional Tests
- [ ] Click "View" button opens read-only page in new tab
- [ ] Click "Edit" button opens edit page in new tab
- [ ] Tab titles show correct employee ID and name
- [ ] "Edit" button in view page switches to edit mode
- [ ] "Save" button in edit page updates employee
- [ ] "Cancel" button returns to view mode
- [ ] Form change tracking warns on navigation
- [ ] Photo upload and preview work

### Multi-Tab Tests
- [ ] Open same employee in 2 tabs (view mode) - no warning
- [ ] Open same employee in 2 tabs (edit mode) - warning appears
- [ ] Save in Tab 1, verify Tab 2 shows update notification
- [ ] Save in Tab 1, verify list view auto-refreshes
- [ ] Open 5+ different employees in separate tabs
- [ ] Verify tab titles are all unique and correct

### Cross-Browser Tests
- [ ] Test in Chrome
- [ ] Test in Firefox
- [ ] Test in Edge
- [ ] Test in Safari

## Future Enhancements

### Possible Improvements
1. **Real-time collaborative editing**: Use WebSockets for live updates
2. **Tab persistence**: Remember open tabs on browser restart
3. **Recently viewed**: Track and quick-access recently opened employees
4. **Keyboard shortcuts**: Ctrl+S to save, Ctrl+W to close
5. **Split-screen mode**: View two employees side-by-side in same window
6. **Activity log**: Show who else is viewing/editing
7. **Auto-save drafts**: Save form state to localStorage every 30 seconds

### Scalability Considerations
- For 1000+ concurrent users, consider server-side session locking
- Implement optimistic locking with version numbers
- Add conflict resolution UI when saves collide

## Troubleshooting

### Issue: New tabs not opening
**Cause**: Popup blocker  
**Solution**: Whitelist the application domain

### Issue: Edit warning not showing
**Cause**: Broadcast Channel API not supported  
**Solution**: Use modern browser (Chrome 54+, Firefox 38+)

### Issue: Tab titles not updating
**Cause**: JavaScript error in page  
**Solution**: Check browser console for errors

### Issue: Form not submitting
**Cause**: Network error or validation failure  
**Solution**: Check network tab and console for errors

## Code Files Modified

### New Files
- `app/views/empleado_view.php` - Read-only employee view page
- `app/views/empleado_edit.php` - Edit mode employee page
- `README-multi-tab-navigation.md` - This documentation

### Modified Files
- `routes/web.php` - Added dynamic route handlers
- `app/Controllers/EmpleadoController.php` - Added view() and edit() methods
- `public/assets/js/empleados.js` - Modified button handlers, added cross-tab sync
- `public/assets/i18n/es.json` - Added new translation keys
- `public/assets/i18n/en.json` - Added new translation keys

## Related Documentation
- [README-layout-implementation.md](./README-layout-implementation.md) - Sidebar and layout system
- [README-theme-centralization.md](./README-theme-centralization.md) - Theme management
- [README-server-side-processing.md](./README-server-side-processing.md) - DataTables server-side mode

---

**Last Updated**: October 15, 2025  
**Version**: 1.0.0  
**Author**: Development Team
