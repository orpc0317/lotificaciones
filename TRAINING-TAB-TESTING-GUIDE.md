# Training Tab (Master-Detail) Testing Guide

## Overview
This guide will help you test the **Capacitación (Training/Courses)** tab functionality in the Employee Edit form.

## Current Status
✅ Database table created: `empleado_capacitacion`
✅ UI implemented: Form + Dynamic Table
✅ JavaScript CRUD functions ready
⚠️ **Backend integration pending** (data doesn't persist yet)

---

## Test Scenarios

### 1. **Add New Training Course**

#### Steps:
1. Navigate to employee edit page: `http://localhost:8080/lotificaciones/public/empleados/edit/1`
2. Click on the **"Capacitación"** tab (book icon - 7th tab)
3. Fill in the form:
   - **Curso/Capacitación**: "Seguridad Industrial"
   - **Fecha de Aprobación**: Select today's date
   - **Recursos Aprobados**: 1500.00
   - **Comentarios**: "Curso obligatorio completado con éxito"
4. Click **"Agregar Curso"** button

#### Expected Results:
✅ New row appears in the table below
✅ Form clears after adding
✅ Table shows: Course name, formatted date, formatted currency, comments
✅ Row has Edit (pencil) and Delete (trash) action buttons

#### Screenshot Points:
- Form filled with sample data
- Table with newly added row
- Formatted date and currency display

---

### 2. **Add Multiple Courses**

#### Steps:
1. Add these additional courses:
   - Course: "Primeros Auxilios", Date: 2024-03-15, Resources: 800.00, Comments: "Certificado vigente"
   - Course: "Manejo Defensivo", Date: 2024-06-20, Resources: 1200.00, Comments: ""
   - Course: "Excel Avanzado", Date: 2024-09-10, Resources: 600.00, Comments: "Online"

#### Expected Results:
✅ Table shows all 4 courses
✅ Courses listed in order of addition
✅ Empty comments show as "-" or blank
✅ Currency formatted with 2 decimals
✅ Dates formatted consistently

---

### 3. **Edit Existing Course**

#### Steps:
1. Click the **Edit (pencil icon)** button on "Primeros Auxilios" row
2. Observe the form is populated with existing data
3. Change:
   - Resources: 800.00 → 950.00
   - Comments: Add "Renovación pendiente 2025"
4. Click **"Actualizar Curso"** button

#### Expected Results:
✅ Form populates with selected row data
✅ Button text changes to "Actualizar Curso" (Update Course)
✅ After update, row reflects new values
✅ Form clears and button returns to "Agregar Curso"
✅ Row order remains the same (edit doesn't move row)

---

### 4. **Cancel Edit**

#### Steps:
1. Click Edit on any row
2. Form populates
3. Change some values BUT don't click Update
4. Click **"Cancelar"** button

#### Expected Results:
✅ Form clears
✅ Button returns to "Agregar Curso"
✅ Original row data unchanged
✅ No new row added

---

### 5. **Delete Course**

#### Steps:
1. Click the **Delete (trash icon)** button on "Excel Avanzado" row
2. Observe confirmation dialog

#### Expected Results:
✅ Browser confirmation appears: "¿Está seguro de eliminar este curso?"
✅ If confirmed: Row disappears from table
✅ If cancelled: Row remains in table
✅ Remaining rows re-number correctly

---

### 6. **Empty State**

#### Steps:
1. Delete all courses from the table one by one
2. Observe the table area

#### Expected Results:
✅ When no courses exist, shows message:
   > "No hay cursos registrados. Agregue un curso usando el formulario."
✅ Empty state has subtle styling (gray text, centered)
✅ Table headers disappear when empty

---

### 7. **Form Validation**

#### Steps:
1. Try to add a course with **empty Course Name**
2. Try to add with **empty Date**
3. Try to add with **negative Resources**
4. Try to add with **valid data**

#### Expected Results:
✅ Alert appears: "Por favor complete todos los campos obligatorios"
✅ Row is NOT added to table
✅ Form retains entered data (doesn't clear on validation error)
✅ Valid data adds successfully

---

### 8. **Data Persistence (CURRENT LIMITATION)**

#### Steps:
1. Add 2-3 training courses
2. Scroll down and click **"Guardar Cambios"** (Save Employee)
3. Navigate away or refresh the page
4. Return to the same employee's edit page
5. Click on Capacitación tab

#### Expected Results (CURRENT):
⚠️ **Training data will NOT load** (backend integration pending)
⚠️ Tab will show empty state
⚠️ Hidden input `training_data` is sent on form submit, but not processed by backend

#### Expected Results (AFTER BACKEND INTEGRATION):
✅ Training courses should reload from database
✅ All added courses appear in table
✅ Edit/Delete continue to work

---

### 9. **Hidden Input Verification**

#### Steps:
1. Add 2 courses to the table
2. Open browser DevTools (F12)
3. Go to Elements/Inspector tab
4. Search for: `<input type="hidden" id="training_data"`
5. Check the `value` attribute

#### Expected Results:
✅ Hidden input contains JSON array like:
```json
[
  {
    "nombre": "Seguridad Industrial",
    "fecha": "2025-10-17",
    "recursos": 1500,
    "comentarios": "Curso obligatorio"
  },
  {
    "nombre": "Primeros Auxilios",
    "fecha": "2024-03-15",
    "recursos": 800,
    "comentarios": "Certificado vigente"
  }
]
```
✅ JSON updates in real-time as you add/edit/delete

---

### 10. **UI/UX Quality Checks**

#### Checks:
- [ ] Tab icon displays correctly (book icon)
- [ ] Form layout is clean and aligned
- [ ] Date picker works correctly
- [ ] Number input accepts decimals
- [ ] Textarea resizes appropriately
- [ ] Table is responsive
- [ ] Action buttons have hover effects
- [ ] Icons are clear and recognizable
- [ ] Currency shows $ symbol
- [ ] Dates show in readable format (DD/MM/YYYY or MM/DD/YYYY based on locale)
- [ ] No console errors in browser DevTools

---

## Known Issues / Pending Work

### ⚠️ Backend Integration Required:

1. **Load Training Data on Edit**
   - Need to add: `EmpleadoModel::getTrainingByEmpleado($id)`
   - Load data in: `EmpleadoController::edit($id)`
   - Pass to view: `$training_data`

2. **Save Training Data on Form Submit**
   - Need to add: `EmpleadoModel::saveTraining($empleado_id, $data)`
   - Update: `EmpleadoController::update($id)` to process `training_data` input
   - Handle: INSERT new records, UPDATE existing, DELETE removed

3. **API Endpoints (Optional Enhancement)**
   ```
   GET    /api/empleados/{id}/training       - List courses
   POST   /api/empleados/{id}/training       - Add course
   PUT    /api/empleados/{id}/training/{tid} - Update course
   DELETE /api/empleados/{id}/training/{tid} - Delete course
   ```

---

## Testing Checklist

### Frontend Functionality (Ready to Test):
- [ ] Add new course
- [ ] Add multiple courses
- [ ] Edit existing course
- [ ] Cancel edit
- [ ] Delete course (with confirmation)
- [ ] Delete all courses (empty state)
- [ ] Form validation
- [ ] Hidden input updates
- [ ] UI is visually correct
- [ ] No JavaScript errors

### Backend Integration (Pending):
- [ ] Training data loads on edit
- [ ] Training data saves on form submit
- [ ] Training data persists after page refresh
- [ ] API endpoints created (optional)

---

## Quick Test Script

Run this in your browser console on the edit page:

```javascript
// Check if training functions exist
console.log('Functions available:', {
    addTrainingRow: typeof addTrainingRow,
    renderTrainingTable: typeof renderTrainingTable,
    editTrainingRow: typeof editTrainingRow,
    deleteTrainingRow: typeof deleteTrainingRow
});

// Check current training data
console.log('Current training data:', trainingData);

// Check hidden input value
console.log('Hidden input:', document.getElementById('training_data').value);
```

---

## Sample Test Data

Use these courses for testing:

| Course Name | Date | Resources | Comments |
|------------|------|-----------|----------|
| Seguridad Industrial | 2024-01-15 | 1500.00 | Curso obligatorio |
| Primeros Auxilios | 2024-03-20 | 800.00 | Certificado vigente hasta 2026 |
| Manejo Defensivo | 2024-06-10 | 1200.00 | Incluye práctica |
| Excel Avanzado | 2024-09-05 | 600.00 | Curso online |
| Liderazgo | 2024-11-12 | 2000.00 | Workshop presencial |

---

## Screenshots to Capture

1. **Empty state** - No courses message
2. **Form filled** - Before adding
3. **Single course** - One row in table
4. **Multiple courses** - Full table
5. **Edit mode** - Form populated, button says "Actualizar"
6. **Delete confirmation** - Browser dialog
7. **Hidden input** - DevTools showing JSON
8. **Tab navigation** - All 7 tabs visible

---

## Next Steps

After frontend testing is complete:

1. ✅ Verify all UI functionality works
2. ⚠️ Implement backend loading (read training from DB)
3. ⚠️ Implement backend saving (persist training to DB)
4. ⚠️ Add training display to View page (read-only)
5. ⚠️ Create API endpoints (optional)
6. ✅ Full integration testing

---

## Questions to Answer During Testing

1. Does the form validation prevent empty submissions?
2. Can you edit and update a course successfully?
3. Does the delete confirmation work properly?
4. Is the empty state message clear and helpful?
5. Are dates and currency formatted correctly for your locale?
6. Does the hidden input update in real-time?
7. Are there any console errors when adding/editing/deleting?
8. Is the UI intuitive for users?

---

**Ready to test! Start with scenario #1 and work your way through the list.**
