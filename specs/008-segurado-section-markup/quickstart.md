# Quickstart Validation: Segurado Section Markup

**Date**: 2026-10-07
**Spec**: `specs/008-segurado-section-markup/spec.md`
**Branch**: `008-segurado-section-markup`

This guide documents how to validate the segurado section markup
feature end-to-end.

---

## Prerequisites

- WordPress 6.0+ with the Vue Blocks theme activated
- PHP 7.4+
- Modern browser (Chrome, Firefox, Safari, Edge)
- JavaScript enabled (for Vue features) and disabled (for fallback)

---

## Validation Scenarios

### Scenario 1: Display Segurado Information (P1)

**Steps**:
1. Create a new post in WordPress admin
2. Fill in the segurado meta fields (name, document, email, etc.)
3. Publish the post
4. View the post on the frontend

**Expected**:
- Segurado section displays with all populated fields
- Fields are semantically structured (`<section>`, `<dl>`, etc.)
- No empty labels for missing optional fields
- Page works with JavaScript disabled

**Verification**:
```bash
# Load page with JS disabled
# Check that all segurado fields are visible
# Verify semantic HTML structure
```

---

### Scenario 2: Edit Segurado Information (P2)

**Steps**:
1. Navigate to the segurado edit page (or click edit trigger)
2. Modify the name field
3. Submit the form

**Expected**:
- Form submits without page reload (JS) or with standard POST (no JS)
- Data is saved and displayed correctly on reload
- Validation errors are shown inline

**Verification**:
```bash
# Submit form with valid data
# Verify data persists on page reload
# Submit form with invalid email
# Verify error message is shown
```

---

### Scenario 3: Vue-Enhanced Interactions (P3)

**Steps**:
1. Enable JavaScript in browser
2. Navigate to a post with segurado data
3. Click an inline edit trigger on a field
4. Modify the value and blur the field

**Expected**:
- Field becomes editable without page reload
- Auto-save triggers on blur
- Success feedback is shown
- Error feedback is shown if validation fails

**Verification**:
```bash
# Click edit trigger
# Verify field becomes editable
# Modify value and blur
# Verify auto-save request is sent (check Network tab)
# Verify success feedback
```

---

### Scenario 4: REST API Integration

**Steps**:
1. Open browser DevTools → Network tab
2. Trigger an inline edit
3. Observe the REST API request

**Expected**:
- Request sent to `/wp-json/wp/v2/posts/{id}`
- `X-WP-Nonce` header is present
- Request body contains updated meta fields
- Response contains updated `vb_segurado` field

**Verification**:
```bash
# Check request URL
# Check X-WP-Nonce header
# Check request body
# Check response
```

---

### Scenario 5: Non-JS Fallback

**Steps**:
1. Disable JavaScript in browser
2. Navigate to a post with segurado data
3. Click edit trigger (or navigate to edit page)
4. Modify fields and submit

**Expected**:
- Edit form is fully functional
- Form submits via standard POST
- Data is saved and displayed correctly
- No JavaScript errors (since JS is disabled)

**Verification**:
```bash
# Disable JavaScript
# Navigate to edit page
# Submit form
# Verify data persists
```

---

### Scenario 6: Theme Check Compliance

**Steps**:
1. Run Theme Check plugin or `phpcs` with WordPress standards
2. Review any errors or warnings

**Expected**:
- No errors related to segurado template parts
- No errors related to segurado functions
- All output is properly escaped

**Verification**:
```bash
# Run Theme Check
# Review results
```

---

### Scenario 7: Zero-Build Compatibility

**Steps**:
1. Install theme on a plain WordPress install (no `node_modules/`)
2. Do not run any build steps
3. Verify the theme works correctly

**Expected**:
- Theme activates without errors
- Segurado section displays correctly
- Vue loads from CDN
- All functionality works

**Verification**:
```bash
# Install theme on clean WordPress
# Activate theme
# Verify segurado section works
```

---

## Success Criteria Mapping

| Criterion | Scenario |
|---|---|
| SC-001 | Scenario 1 |
| SC-002 | Scenario 2 |
| SC-003 | Scenario 3 |
| Scenario 4 | REST API integration |
| SC-005 | Scenario 1, 2, 3 |
| SC-006 | Scenario 6 |
| SC-007 | Scenario 7 |