# Feature Specification: Segurado Section Markup

**Feature Branch**: `008-segurado-section-markup`

**Created**: 2026-10-07

**Status**: Draft

**Input**: User description: "Create a structured markup section for displaying and editing segurado (insured/policyholder) information in the WordPress theme, following server-first rendering and progressive enhancement principles."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Display Segurado Information (Priority: P1)

A site visitor views a policy or claim page and sees the segurado's information clearly presented in a structured, accessible format.

**Why this priority**: Core requirement — users must be able to read segurado details without JavaScript, ensuring SEO crawlability, accessibility, and resilience per the theme's server-first rendering principle.

**Independent Test**: Can be fully tested by loading a policy page with segurado data in a browser with JavaScript disabled and verifying all segurado fields are visible and semantically structured.

**Acceptance Scenarios**:

1. **Given** a policy post with associated segurado data, **When** the page loads, **Then** the segurado section displays name, document ID, contact info, and address in semantic HTML
2. **Given** a segurado with missing optional fields, **When** the page loads, **Then** only populated fields are shown without empty labels or layout gaps

---

### User Story 2 - Edit Segurado Information (Priority: P2)

An authenticated user (policyholder or admin) edits the segurado's information through a form that works without JavaScript.

**Why this priority**: Editing is a core administrative function; must work via standard form submission per progressive enhancement principle.

**Independent Test**: Can be fully tested by submitting the edit form with JavaScript disabled and verifying data persists and displays correctly on reload.

**Acceptance Scenarios**:

1. **Given** an edit page for a segurado, **When** the user submits valid changes, **Then** the data is saved and the display page reflects updates
2. **Given** an edit form with validation errors, **When** the user submits, **Then** errors are displayed inline and submitted data is preserved

---

### User Story 3 - Vue-Enhanced Interactions (Priority: P3)

When JavaScript is available, the segurado section provides enhanced interactions (inline editing, auto-save, dynamic field validation) without breaking the base functionality.

**Why this priority**: Enhances UX for modern browsers while maintaining the non-JS baseline; follows the CDN-first Vue progressive enhancement pattern.

**Independent Test**: Can be tested by enabling JavaScript and verifying inline edit, auto-save, and validation work, then disabling JavaScript and confirming base edit/display still functions.

**Acceptance Scenarios**:

1. **Given** JavaScript is enabled, **When** the user clicks an inline edit trigger, **Then** the field becomes editable without page reload
2. **Given** auto-save is enabled, **When** the user modifies a field and blurs, **Then** changes are saved via REST API with nonce
3. **Given** a validation error occurs during inline edit, **Then** error is shown inline without losing focus

---

### Edge Cases

- What happens when segurado data is deleted but referenced by a policy? (Show placeholder with "Data unavailable" message)
- How does system handle very long names or addresses? (Text wraps, container scrolls, no layout break)
- What if REST API is unavailable during inline edit? (Fallback to full form submission, show user-friendly error)
- How are special characters in names/documents handled? (Properly escaped per WordPress escaping standards)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST render segurado display markup server-side via PHP template parts, producing semantic HTML (`<section>`, `<dl>`, `<dt>`, `<dd>` or equivalent) without requiring JavaScript
- **FR-002**: System MUST expose segurado data fields via WordPress REST API using `register_rest_field()` on the relevant post type (policy/claim), including: full name, document type/number, email, phone, address lines, city, state, postal code, country
- **FR-003**: System MUST provide a server-rendered edit form for segurado data that submits via standard POST to a WordPress admin-handled endpoint, functioning without JavaScript
- **FR-004**: System MUST validate segurado data on both server (PHP) and client (Vue) with consistent rules: required fields (name, document), email format, phone format, document format per type
- **FR-005**: System MUST include `wp_rest` nonce in all state-changing REST requests from Vue, sourced from `vbData.nonce`
- **FR-006**: System MUST mount a Vue app over the server-rendered segurado section to enable inline editing, auto-save, and dynamic validation when JavaScript is available
- **FR-007**: System MUST use CSS custom properties from `:root` in `style.css` for all visual tokens (colors, spacing, radii) in the segurado section — no hard-coded values
- **FR-008**: System MUST prefix all PHP functions with `vb_`, constants with `VB_`, CSS classes with `vb-`, and use `vue-blocks` text domain for i18n strings
- **FR-009**: System MUST escape all output at point of emission using `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` as appropriate; data from `vbData` consumed via `v-text`/`:attr` treated as pre-escaped
- **FR-010**: System MUST support RTL languages and WCAG 2.1 AA contrast/keyboard navigation in the segurado section markup

### Key Entities

- **Segurado**: Represents the insured/policyholder person. Key attributes: full_name (string, required), document_type (enum: CPF, CNPJ, Passport, Other), document_number (string, required), email (string, optional), phone (string, optional), address_line_1 (string, optional), address_line_2 (string, optional), city (string, optional), state (string, optional), postal_code (string, optional), country (string, default: BR)
- **Policy**: The insurance policy post type that references a Segurado. Relationship: one Policy has one Segurado (stored as post meta or separate CPT with post-to-post connection)

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Segurado display page loads and renders all data in under 1.5 seconds on 3G connection (server-rendered HTML, no JS required for content)
- **SC-002**: Edit form submission (non-JS) completes and redirects back to display in under 2 seconds
- **SC-003**: Inline edit auto-save (JS-enabled) persists changes via REST API in under 500ms median latency
- **SC-004**: Zero JavaScript errors in console when loading segurado section with Vue mounted
- **SC-005**: 100% of segurado fields keyboard-navigable and screen-reader accessible in both display and edit modes
- **SC-006**: Theme Check passes with no errors or warnings for template parts and functions added for this feature
- **SC-007**: Works on plain WordPress install with no `node_modules/`, no `dist/`, no build step (CDN-first principle)

## Assumptions

- Target users include Portuguese-speaking policyholders and administrators; i18n strings use `vue-blocks` text domain
- Existing WordPress REST API infrastructure (`vb_register_rest_fields()`, `vbData` localization) will be extended for segurado fields
- Segurado data is stored as post meta on a Policy CPT or as a separate Segurado CPT with post-to-post relationship (exact storage to be determined during implementation)
- The theme already enqueues Vue 3.4.x from CDN and mounts apps via `assets/js/app.js` — this feature adds a new mount point
- Mobile responsiveness follows existing theme breakpoints and CSS token system
- Data retention follows standard insurance industry practices (not specified in feature scope)