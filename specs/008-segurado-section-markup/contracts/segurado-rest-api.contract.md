# REST API Contract: Segurado Fields

**Date**: 2026-10-07
**Spec**: `specs/008-segurado-section-markup/spec.md`
**Branch**: `008-segurado-section-markup`

This contract defines the REST API interface for segurado data
exposed by the WordPress REST API.

---

## Endpoint: Get Segurado Data

**Method**: `GET`
**Path**: `/wp-json/wp/v2/posts/{id}`
**Authentication**: None (public read)

### Response

The response includes a `vb_segurado` field:

```json
{
  "id": 123,
  "title": { "rendered": "Policy #12345" },
  "vb_segurado": {
    "full_name": "Maria Silva",
    "document_type": "cpf",
    "document_number": "123.456.789-00",
    "email": "maria@example.com",
    "phone": "+55 11 99999-9999",
    "address": {
      "line_1": "Rua das Flores, 123",
      "line_2": "Apto 45",
      "city": "São Paulo",
      "state": "SP",
      "postal_code": "01234-567",
      "country": "BR"
    }
  }
}
```

### Error Responses

| Status | Code | Description |
|---|---|---|
| 404 | `rest_post_invalid_id` | Post does not exist |
| 401 | `rest_forbidden` | User cannot view this post |

---

## Endpoint: Update Segurado Data

**Method**: `POST`
**Path**: `/wp-json/wp/v2/posts/{id}`
**Authentication**: Requires `X-WP-Nonce` header (from `vbData.nonce`)

### Request Body

```json
{
  "meta": {
    "vb_segurado_full_name": "Maria Silva",
    "vb_segurado_document_type": "cpf",
    "vb_segurado_document_number": "123.456.789-00",
    "vb_segurado_email": "maria@example.com",
    "vb_segurado_phone": "+55 11 99999-9999",
    "vb_segurado_address_1": "Rua das Flores, 123",
    "vb_segurado_address_2": "Apto 45",
    "vb_segurado_city": "São Paulo",
    "vb_segurado_state": "SP",
    "vb_segurado_postal_code": "01234-567",
    "vb_segurado_country": "BR"
  }
}
```

### Response

Returns the updated post object with the `vb_segurado` field.

### Error Responses

| Status | Code | Description |
|---|---|---|
| 400 | `rest_invalid_param` | Invalid meta value (e.g., invalid email) |
| 401 | `rest_forbidden` | Missing or invalid nonce |
| 404 | `rest_post_invalid_id` | Post does not exist |

---

## Validation Rules

| Field | Rule |
|---|---|
| `vb_segurado_full_name` | Required, max 200 chars |
| `vb_segurado_document_type` | Required, one of: `cpf`, `cnpj`, `passport`, `other` |
| `vb_segurado_document_number` | Required, format per type |
| `vb_segurado_email` | Optional, valid email format |
| `vb_segurado_phone` | Optional, max 20 chars |
| `vb_segurado_address_1` | Optional, max 200 chars |
| `vb_segurado_address_2` | Optional, max 200 chars |
| `vb_segurado_city` | Optional, max 100 chars |
| `vb_segurado_state` | Optional, max 100 chars |
| `vb_segurado_postal_code` | Optional, max 20 chars |
| `vb_segurado_country` | Optional, default: `BR` |

---

## Non-JS Fallback

When JavaScript is unavailable, the edit form submits via standard
POST to `admin-post.php` with action `vb_update_segurado`. The
form includes a nonce field (`vb_segurado_nonce`) for security.