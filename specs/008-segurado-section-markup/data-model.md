# Phase 1 Data Model: Segurado Section Markup

**Date**: 2026-10-07
**Spec**: `specs/008-segurado-section-markup/spec.md`
**Branch**: `008-segurado-section-markup`

This file enumerates the entities introduced or modified by this
feature, with attributes, validation rules, and lifecycle notes.

---

## E1 — Segurado (Insured/Policyholder)

Represents the insured person associated with a policy. Stored as
post meta on the `post` post type (or `policy` CPT if introduced).

### Fields

| Field | Meta Key | Type | Required | Validation |
|---|---|---|---|---|
| Full name | `vb_segurado_full_name` | string | Yes | `sanitize_text_field()`, max 200 chars |
| Document type | `vb_segurado_document_type` | enum | Yes | One of: `cpf`, `cnpj`, `passport`, `other` |
| Document number | `vb_segurado_document_number` | string | Yes | Format per type (CPF: 11 digits, CNPJ: 14 digits, Passport: alphanumeric) |
| Email | `vb_segurado_email` | string | No | `sanitize_email()`, valid email format |
| Phone | `vb_segurado_phone` | string | No | `sanitize_phone()` (custom), max 20 chars |
| Address line 1 | `vb_segurado_address_1` | string | No | `sanitize_text_field()`, max 200 chars |
| Address line 2 | `vb_segurado_address_2` | string | No | `sanitize_text_field()`, max 200 chars |
| City | `vb_segurado_city` | string | No | `sanitize_text_field()`, max 100 chars |
| State | `vb_segurado_state` | string | No | `sanitize_text_field()`, max 100 chars |
| Postal code | `vb_segurado_postal_code` | string | No | `sanitize_text_field()`, max 20 chars |
| Country | `vb_segurado_country` | string | No | `sanitize_text_field()`, default: `BR` |

### REST API Representation

The `vb_segurado` field on the `post` post type returns:

```json
{
  "full_name": "string",
  "document_type": "cpf|cnpj|passport|other",
  "document_number": "string",
  "email": "string|null",
  "phone": "string|null",
  "address": {
    "line_1": "string|null",
    "line_2": "string|null",
    "city": "string|null",
    "state": "string|null",
    "postal_code": "string|null",
    "country": "string"
  }
}
```

### Lifecycle

- **Created**: When a policy post is created with segurado data.
- **Updated**: Via REST API (Vue inline edit) or admin form.
- **Deleted**: When the associated policy post is deleted (meta is
  automatically cleaned up by WordPress).

---

## E2 — Policy (Insurance Policy)

The insurance policy post type that references a Segurado.
Relationship: one Policy has one Segurado (stored as post meta).

### Fields (existing + new)

| Field | Type | Notes |
|---|---|---|
| Post title | string | Policy name/number |
| Post content | string | Policy description |
| Featured image | image | Policy thumbnail |
| `vb_segurado_*` meta | various | Segurado data (see E1) |

### Lifecycle

- **Created**: Via WordPress admin or programmatically.
- **Updated**: Via WordPress admin or REST API.
- **Deleted**: Segurado meta is automatically cleaned up.

---

## Cross-entity invariants

1. A Policy MUST have at least `full_name` and `document_number`
   in its segurado meta (enforced by server-side validation).
2. The `vb_segurado` REST field returns `null` if no segurado meta
   exists on the post.
3. All segurado meta values are sanitized on save and escaped on
   output (per Constitution Technical Constraints).

---

## State transitions

### Segurado data states

```
[No data] --(admin adds data)--> [Partial data] --(admin completes)--> [Full data]
[Full data] --(admin edits)--> [Full data] (updated)
[Full data] --(policy deleted)--> [No data] (meta cleaned up)
```

### Edit form states

```
[Display mode] --(click edit trigger)--> [Edit mode]
[Edit mode] --(submit valid)--> [Display mode] (updated)
[Edit mode] --(submit invalid)--> [Edit mode] (errors shown)
[Edit mode] --(cancel)--> [Display mode] (unchanged)
```

---

## Out-of-scope entities (deliberately omitted)

- A separate `segurado` CPT (deferred; see R1 in research.md).
- A `claim` CPT (not part of this feature).
- Historical versions of segurado data (not required).