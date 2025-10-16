# Error Handling Standardization

## Overview
Centralized error handling system for consistent error logging, reporting, and user notifications across all AJAX operations and JavaScript interactions.

## Implementation Date
October 15, 2025

## ErrorHandler Utility

### Core Object
```javascript
var ErrorHandler = {
    log: function(context, error, metadata) { },
    showToast: function(title, detail) { },
    handleAjaxError: function(context, error, showToast) { },
    handleValidationError: function(context, validationErrors) { },
    handleServerError: function(context, response) { }
}
```

### Methods

#### 1. `log(context, error, metadata)`
Centralized error logging with context and metadata.

**Parameters:**
- `context` (string): Where the error occurred (e.g., "CreateEmployee", "UpdateEmployee")
- `error` (Error|string): The error object or message
- `metadata` (Object): Additional context (optional)

**Features:**
- Extracts error message and stack trace
- Adds ISO timestamp
- Stores last error in `window.__lastError` for debugging
- Structured console output with context

**Example:**
```javascript
ErrorHandler.log('CreateEmployee.Submit', err, { formId: 'formNuevoEmpleado' });
```

**Console Output:**
```javascript
[CreateEmployee.Submit] {
    context: "CreateEmployee.Submit",
    message: "Network request failed",
    timestamp: "2025-10-15T10:30:45.123Z",
    stack: "Error: Network request failed\n  at ...",
    metadata: { formId: "formNuevoEmpleado" }
}
```

#### 2. `showToast(title, detail)`
Display user-friendly error toast notification.

**Parameters:**
- `title` (string): Error title (default: "Ha ocurrido un error")
- `detail` (string): Optional error detail

**Features:**
- Uses SweetAlert2 toast with error icon
- Position and duration from constants
- Fallback to console if SweetAlert not available
- Error-safe implementation

**Example:**
```javascript
ErrorHandler.showToast('Error de conexión', 'No se pudo conectar al servidor');
```

#### 3. `handleAjaxError(context, error, showToast)`
Handle AJAX/fetch network errors.

**Parameters:**
- `context` (string): Operation context
- `error` (Error): The error object
- `showToast` (boolean): Show user notification (default: true)

**Features:**
- Logs error with AJAX type metadata
- Shows "Error de conexión" toast
- Extracts error message from error object
- Can suppress toast for silent errors

**Example:**
```javascript
fetch(api('empleados/create'), options)
    .catch(function(err) {
        ErrorHandler.handleAjaxError('CreateEmployee', err);
    });
```

#### 4. `handleValidationError(context, validationErrors)`
Handle form validation errors.

**Parameters:**
- `context` (string): Operation context
- `validationErrors` (Object): Validation error object from server

**Features:**
- Logs validation errors with metadata
- Extracts first error message for toast
- Shows "Error de validación" with specific message
- Handles array and object error formats

**Example:**
```javascript
if (response.validation_errors) {
    ErrorHandler.handleValidationError('CreateEmployee', response.validation_errors);
}
```

#### 5. `handleServerError(context, response)`
Handle server-side errors from API responses.

**Parameters:**
- `context` (string): Operation context
- `response` (Object): Server response object

**Features:**
- Extracts error from `response.error` or `response.message`
- Logs full response for debugging
- Shows specific error message to user
- Fallback to generic "Error en el servidor"

**Example:**
```javascript
.then(function(resp) {
    if (!resp.success) {
        ErrorHandler.handleServerError('CreateEmployee', resp);
    }
})
```

## Implementation Locations

### 1. Cross-Tab Sync
**Location:** `initCrossTabSync()`
```javascript
catch (e) {
    ErrorHandler.log('CrossTabSync', e, { feature: 'BroadcastChannel' });
}
```

### 2. Update Notifications
**Location:** `showUpdateNotification()`
```javascript
catch (e) {
    ErrorHandler.log('showUpdateNotification', e);
}
```

### 3. Button Loading States
**Location:** `setButtonLoading()`
```javascript
catch(e) {
    ErrorHandler.log('setButtonLoading', e, { isLoading: isLoading });
}
```

### 4. Create Employee Form
**Location:** `#formNuevoEmpleado submit handler`

**Server Error:**
```javascript
else {
    ErrorHandler.handleServerError('CreateEmployee', resp);
}
```

**Response Parsing Error:**
```javascript
catch(e) {
    ErrorHandler.log('CreateEmployee.ResponseParsing', e);
}
```

**Network Error:**
```javascript
.catch(function(err) {
    ErrorHandler.handleAjaxError('CreateEmployee', err);
})
```

**Submit Handler Error:**
```javascript
catch(e) {
    ErrorHandler.log('CreateEmployeeForm.Submit', e);
}
```

### 5. Update Employee Form
**Location:** `#formEditar submit handler`

**Server Error:**
```javascript
else {
    ErrorHandler.handleServerError('UpdateEmployee', resp);
}
```

**Response Parsing Error:**
```javascript
catch(e) {
    ErrorHandler.log('UpdateEmployee.ResponseParsing', e);
}
```

**Network Error:**
```javascript
.catch(function(err) {
    ErrorHandler.handleAjaxError('UpdateEmployee', err);
})
```

**Submit Handler Error:**
```javascript
catch(e) {
    ErrorHandler.log('UpdateEmployeeForm.Submit', e);
}
```

### 6. View Employee Click Handler
**Location:** `.ver-ficha click handler`
```javascript
catch(err) {
    ErrorHandler.log('ViewEmployee.ClickHandler', err, { id: id });
}
```

### 7. Edit Employee Click Handler
**Location:** `.editar click handler`
```javascript
catch(err) {
    ErrorHandler.log('EditEmployee.ClickHandler', err, { id: id });
}
```

### 8. Delete Employee
**Location:** `.eliminar click handler`

**Request Error:**
```javascript
.catch(function(err) {
    ErrorHandler.log('DeleteEmployee.Request', err, { id: id });
    Swal.showValidationMessage('Error: ' + err.message);
});
```

**Click Handler Error:**
```javascript
catch(e) {
    ErrorHandler.log('DeleteEmployee.ClickHandler', e, { id: id });
}
```

## Error Categories

### 1. Network Errors
- **Type:** Connection failures, timeouts, CORS issues
- **Handler:** `handleAjaxError()`
- **User Message:** "Error de conexión"
- **Logged:** Yes, with request context

### 2. Server Errors
- **Type:** 4xx/5xx responses, business logic failures
- **Handler:** `handleServerError()`
- **User Message:** Specific error from server
- **Logged:** Yes, with full response

### 3. Validation Errors
- **Type:** Input validation failures
- **Handler:** `handleValidationError()`
- **User Message:** First validation error
- **Logged:** Yes, with all validation errors

### 4. Client Errors
- **Type:** JavaScript exceptions, DOM errors
- **Handler:** `log()`
- **User Message:** None (silent)
- **Logged:** Yes, with stack trace

## Benefits

### 1. Consistency
- **Uniform Logging:** All errors logged with same structure
- **Standard Messaging:** Consistent user-facing error messages
- **Predictable Behavior:** Same error types handled the same way

### 2. Debugging
- **Context Awareness:** Know exactly where error occurred
- **Metadata Tracking:** Additional context for complex errors
- **Stack Traces:** Full stack traces for all JavaScript errors
- **Last Error:** `window.__lastError` for console debugging

### 3. User Experience
- **Friendly Messages:** Technical errors translated to user-friendly text
- **Visual Feedback:** Toast notifications with appropriate icons
- **No Silent Failures:** All errors reported to user appropriately
- **Recoverable:** Clear indication of what went wrong

### 4. Maintenance
- **Single Source:** Update error handling logic in one place
- **Easy Extensions:** Add new error types without modifying handlers
- **Testable:** Centralized handlers easier to unit test
- **Documentation:** Self-documenting with descriptive context strings

## Error Flow Examples

### Example 1: Network Failure on Create
```
User submits form
→ Network request fails
→ ErrorHandler.handleAjaxError('CreateEmployee', err)
→ Logs: [CreateEmployee] { context, message, timestamp, metadata: {type: 'AJAX'} }
→ Toast: "Error de conexión"
→ Button state restored
```

### Example 2: Validation Error on Update
```
User submits invalid data
→ Server returns validation errors
→ ErrorHandler.handleServerError('UpdateEmployee', resp)
→ Logs: [UpdateEmployee] { context, message, response }
→ Toast: "El email no es válido" (first validation error)
→ Button state restored
```

### Example 3: Client-Side Exception
```
JavaScript error in event handler
→ ErrorHandler.log('DeleteEmployee.ClickHandler', err, { id: 123 })
→ Logs: [DeleteEmployee.ClickHandler] { context, message, stack, metadata: {id: 123} }
→ window.__lastError updated
→ No toast (silent client error)
```

## Testing Checklist

- [x] Network errors show "Error de conexión" toast
- [x] Server errors show specific error message
- [x] Validation errors show first validation message
- [x] All errors logged to console
- [x] window.__lastError updated
- [x] Stack traces captured
- [x] Metadata included in logs
- [x] Context strings descriptive
- [x] Toast notifications work
- [x] Fallback to console if SweetAlert unavailable

## Files Modified

1. **public/assets/js/empleados.js**
   - Added ErrorHandler utility object (120 lines)
   - Replaced 20+ ad-hoc error handlers
   - Standardized error contexts
   - Added metadata to all error logs

## Constants Used

From `constants.js`:
- `TOAST_POSITION`: 'top-end'
- `TOAST_ERROR_DURATION`: 2500ms

## Browser Compatibility

- ✅ Chrome 90+ (Error constructor, stack traces)
- ✅ Firefox 88+ (Error constructor, stack traces)
- ✅ Safari 14+ (Error constructor, stack traces)
- ✅ Edge 90+ (Error constructor, stack traces)
- ✅ All modern browsers with console.error support

## Future Enhancements

1. **Error Reporting Service:** Send errors to backend logging service
2. **User Feedback:** Allow users to report errors with context
3. **Retry Logic:** Automatic retry for transient network errors
4. **Error Analytics:** Track error frequency and patterns
5. **Offline Queue:** Queue failed requests when offline

## Related Documentation

- [Loading States Implementation](README-loading-states.md)
- [Input Validation](VALIDATION-IMPLEMENTATION.md)
- [Security Implementation](SECURITY-IMPLEMENTATION.md)
- [QA Analysis Report](QA-ANALYSIS-REPORT.md)
