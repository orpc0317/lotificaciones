# Tab Validation Badges - Testing Checklist

**Date:** October 17, 2025  
**Feature:** Tab validation badges on Employee Edit page

---

## Quick Test Steps

### Test 1: Initial Load
1. Navigate to employee list
2. Click "View" on any employee
3. Click "Edit" button
4. **Expected:** General tab shows badge with "2" and red underline

### Test 2: Fill First Name
1. Type in "Nombres" field
2. **Expected:** Badge changes from "2" to "1" immediately
3. **Expected:** Red underline stays visible

### Test 3: Fill Last Name
1. Type in "Apellidos" field
2. **Expected:** Badge disappears smoothly
3. **Expected:** Red underline fades out
4. **Expected:** No validation indicators visible

### Test 4: Clear a Field
1. Delete content from "Nombres" field
2. Click outside field (blur event)
3. **Expected:** Badge reappears with "1"
4. **Expected:** Red underline animates back

### Test 5: Clear Both Fields
1. Delete content from both "Nombres" and "Apellidos"
2. **Expected:** Badge shows "2"
3. **Expected:** Red underline visible

### Test 6: Switch Tabs
1. Click "Personal" tab
2. **Expected:** No badge (no required fields)
3. Click back to "General" tab
4. **Expected:** Badge still shows "2" (state persisted)

---

## Visual Checklist

- [ ] Badge is circular (border-radius: 999px)
- [ ] Badge background is red (#dc3545)
- [ ] Badge text is white (#fff)
- [ ] Badge text size is readable (11px)
- [ ] Underline is 3px height
- [ ] Underline is red (#dc3545)
- [ ] Underline animates smoothly (260ms)
- [ ] Underline expands from left to right
- [ ] Badge updates instantly on input
- [ ] No lag or flickering

---

## Browser Testing

### Chrome/Edge
- [ ] Badge renders correctly
- [ ] Animation is smooth
- [ ] No console errors

### Firefox
- [ ] Badge renders correctly
- [ ] Animation is smooth
- [ ] No console errors

### Safari (if available)
- [ ] Badge renders correctly
- [ ] Animation is smooth
- [ ] No console errors

---

## Edge Cases

### Empty Spaces
1. Enter only spaces in "Nombres" field
2. **Expected:** Should still show as invalid (count: 1)

### Whitespace Trimming
1. Enter "  Juan  " (spaces before/after)
2. **Expected:** Should count as valid (`.trim()` removes spaces)

### Quick Typing
1. Type very fast in "Nombres" field
2. **Expected:** Badge updates smoothly, no lag

### Tab Switching While Typing
1. Start typing in "Nombres"
2. Quickly switch to "Personal" tab
3. Switch back to "General"
4. **Expected:** Badge count should be correct

---

## Console Check

Open browser console (F12) and verify:
- [ ] No JavaScript errors
- [ ] No "undefined" warnings
- [ ] Function `updateTabBadges` exists
- [ ] Function `attachValidationListeners` exists

---

## Performance Check

- [ ] Page loads quickly (no delay from validation JS)
- [ ] Badge updates are instant (< 50ms perceived)
- [ ] No memory leaks (check DevTools Memory tab)
- [ ] No excessive DOM reflows

---

## Mobile Testing (Optional)

### Mobile Chrome
- [ ] Badge visible and readable
- [ ] Touch events work (tap fields)
- [ ] Animation plays smoothly

### Mobile Safari
- [ ] Badge visible and readable
- [ ] Touch events work
- [ ] Animation plays smoothly

---

## Status After Testing

### All Tests Pass ✅
- Feature is ready for production
- No issues found
- Users will love it!

### Some Tests Fail ⚠️
- Document issues found
- Apply fixes
- Retest

---

## Quick Fix Guide

### Badge Not Showing
**Check:**
```javascript
// Verify badge HTML exists
document.querySelector('.badge-tab[data-tab="general"]')
// Should return: <span class="badge-tab" ...>
```

### Underline Not Animating
**Check:**
```javascript
// Verify tab-invalid class added
document.getElementById('general-tab').classList.contains('tab-invalid')
// Should return: true (when fields empty)
```

### Count Incorrect
**Check:**
```javascript
// Verify required fields detected
document.querySelectorAll('#general input[required]').length
// Should return: 2 (nombres, apellidos)
```

---

## Report Issues

If you find any issues, document:
1. **Browser:** Chrome/Firefox/Safari/Edge (with version)
2. **OS:** Windows/Mac/Linux
3. **Steps to reproduce:** Detailed steps
4. **Expected:** What should happen
5. **Actual:** What actually happens
6. **Screenshot:** If visual issue

---

**Happy Testing!** 🎉
