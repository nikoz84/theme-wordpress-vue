# Tasks: Segurado Section Markup

**Input**: Design documents from `specs/008-segurado-section-markup/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/segurado-rest-api.contract.md

**Tests**: Not explicitly requested — test tasks omitted.

**Organization**: Tasks grouped by user story for independent implementation.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: User story label (US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Theme root**: `theme/`
- **Template parts**: `theme/template-parts/`
- **PHP helpers**: `theme/inc/`
- **Vue app**: `theme/assets/js/`
- **Styles**: `theme/style.css`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Register segurado meta fields and REST API infrastructure

- [x] T001 Register segurado meta fields with `register_meta()` in `theme/functions.php` — set `show_in_rest => true`, `single => true`, and `sanitize_callback` for each `vb_segurado_*` key
- [x] T002 [P] Create `theme/inc/segurado.php` with helper functions: `vb_sanitize_segurado_field()`, `vb_validate_segurado_data()`, `vb_get_segurado()`, `vb_update_segurado()`
- [x] T003 [P] Add segurado CSS custom properties to `:root` in `theme/style.css` — `--vb-segurado-bg`, `--vb-segurado-border`, `--vb-segurado-radius`, `--vb-segurado-spacing`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T004 Extend `vb_register_rest_fields()` in `theme/functions.php` — register `vb_segurado` field on `post` post type with `get_callback` returning structured segurado object
- [x] T005 Implement server-side validation in `theme/inc/segurado.php` — `vb_validate_segurado_data()` checks required fields, email format, document format per type, max lengths; returns `WP_Error` on failure
- [x] T006 Implement server-side sanitization in `theme/inc/segurado.php` — `vb_sanitize_segurado_field()` applies `sanitize_text_field()`, `sanitize_email()`, custom phone sanitizer, and enum validation for document type
- [x] T007 Add `vb_segurado` i18n strings to `vbData.i18n` in `theme/functions.php` `wp_localize_script()` — labels for all fields, validation error messages, success/error feedback strings

**Checkpoint**: Foundation ready — user story implementation can now begin

---

## Phase 3: User Story 1 - Display Segurado Information (Priority: P1) 🎯 MVP

**Goal**: Server-rendered semantic HTML display of segurado data, works without JavaScript

**Independent Test**: Load a post with segurado data in a browser with JavaScript disabled — all fields visible, semantic structure, no empty labels for missing optional fields

### Implementation for User Story 1

- [x] T008 [US1] Create `theme/template-parts/segurado/segurado-display.php` — semantic `<section>` with `<dl>`/`<dt>`/`<dd>` structure, escapes all output with `esc_html()`/`esc_attr()`, skips empty optional fields
- [x] T009 [US1] Add display styles to `theme/style.css` — `.vb-segurado-section`, `.vb-segurado-list`, `.vb-segurado-term`, `.vb-segurado-definition` using CSS custom properties from T003
- [x] T010 [US1] Integrate display template into `theme/single.php` — call `get_template_part('template-parts/segurado/segurado-display')` when post has segurado meta

**Checkpoint**: User Story 1 fully functional — segurado data displays without JavaScript

---

## Phase 4: User Story 2 - Edit Segurado Information (Priority: P2)

**Goal**: Server-rendered edit form with validation, works without JavaScript

**Independent Test**: Submit the edit form with JavaScript disabled — valid data saves and displays, invalid data shows errors inline with submitted data preserved

### Implementation for User Story 2

- [x] T011 [US2] Create `theme/template-parts/segurado/segurado-edit.php` — standard HTML form with `action="admin-post.php"`, `name="vb_update_segurado"`, nonce field, all segurado fields as inputs with `value` attributes escaped with `esc_attr()`
- [x] T012 [US2] Add form validation display in `theme/template-parts/segurado/segurado-edit.php` — show inline error messages per field using `vb_validate_segurado_data()` errors, preserve submitted values on validation failure
- [x] T013 [US2] Implement form handler in `theme/inc/segurado.php` — `vb_handle_segurado_form_submission()` hooked to `admin_post_vb_update_segurado` and `admin_post_nopriv_vb_update_segurado`, verifies nonce, validates, saves via `vb_update_segurado()`, redirects with success/error query arg
- [x] T014 [US2] Add edit form styles to `theme/style.css` — `.vb-segurado-form`, `.vb-segurado-field`, `.vb-segurado-error`, `.vb-segurado-success` using CSS custom properties
- [x] T015 [US2] Integrate edit form into `theme/single.php` — show edit form when user has `edit_post` capability, show display template otherwise

**Checkpoint**: User Stories 1 AND 2 both work independently — display and edit both functional without JavaScript

---

## Phase 5: User Story 3 - Vue-Enhanced Interactions (Priority: P3)

**Goal**: Inline editing, auto-save, and dynamic validation via Vue.js progressive enhancement

**Independent Test**: Enable JavaScript — inline edit triggers work, auto-save persists via REST API, validation errors show inline, fallback to full form if REST unavailable

### Implementation for User Story 3

- [x] T016 [US3] Add Vue app mount in `theme/assets/js/app.js` — new `createApp().mount()` on `.vb-segurado-edit` element, reads initial values from `data-*` attributes, implements inline edit toggle
- [x] T017 [US3] Implement auto-save in Vue app — on field blur, send `POST /wp-json/wp/v2/posts/{id}` with updated meta and `X-WP-Nonce` header from `vbData.nonce`, show success/error feedback
- [x] T018 [US3] Implement client-side validation in Vue app — real-time validation on input/blur using same rules as server (`vb_validate_segurado_data()`), show inline error messages from `vbData.i18n`
- [x] T019 [US3] Add REST API fallback handling in Vue app — if REST request fails, show user-friendly error and suggest full form submission
- [x] T020 [US3] Add `data-*` attributes to display template in `theme/template-parts/segurado/segurado-display.php` — `data-segurado-field`, `data-segurado-value` for each field to enable Vue inline editing

**Checkpoint**: All user stories independently functional — display, edit, and Vue enhancement all working

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements affecting multiple user stories

- [x] T021 [P] Add RTL language support in `theme/style.css` — logical properties for segurado section margins/padding
- [x] T022 [P] Add keyboard navigation styles in `theme/style.css` — visible focus states for all segurado form fields and edit triggers
- [x] T023 Run quickstart.md validation — execute all 7 scenarios, document results
- [x] T024 Run Theme Check — verify no errors for new template parts, functions, and CSS

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion — BLOCKS all user stories
- **User Stories (Phase 3+)**: All depend on Foundational phase completion
  - US1 (P1) → US2 (P2) → US3 (P3) sequential recommended
- **Polish (Final Phase)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational — no dependencies on other stories
- **User Story 2 (P2)**: Can start after Foundational — integrates with US1 display template
- **User Story 3 (P3)**: Can start after Foundational — integrates with US1 display and US2 edit form

### Within Each User Story

- Templates before styles
- Server-side before client-side
- Core implementation before integration

### Parallel Opportunities

- T002, T003 can run in parallel with T001 (different files)
- T005, T006 can run in parallel (different functions in same file — sequential edit)
- T008, T009 can run in parallel (template and styles)
- T011, T014 can run in parallel (template and styles)
- T016, T018 can run in parallel (Vue app and validation)
- T021, T022 can run in parallel (RTL and keyboard styles)

---

## Parallel Example: User Story 1

```bash
# Launch template and styles in parallel:
Task: "Create theme/template-parts/segurado/segurado-display.php"
Task: "Add display styles to theme/style.css"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: Test display without JavaScript
5. Deploy/demo if ready

### Incremental Delivery

1. Setup + Foundational → Foundation ready
2. Add US1 → Test display → Deploy (MVP!)
3. Add US2 → Test edit form → Deploy
4. Add US3 → Test Vue enhancement → Deploy
5. Polish → Final validation → Release

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story
- Each user story independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- All output escaped per Constitution Technical Constraints
- All CSS uses custom properties from `:root`