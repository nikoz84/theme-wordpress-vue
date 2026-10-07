# Phase 1 Data Model: Home-Page Responsive Polish & Playwright E2E

**Date**: 2026-10-06
**Spec**: `specs/006-responsive-polish-and-e2e/spec.md`
**Branch**: `006-responsive-polish-and-e2e`

This file enumerates the entities defined in the spec's `### Key
Entities` section with attributes, validation rules, and lifecycle
notes.

---

## E1 — Synthetic Long-Title Post

A temporary WP post created by the e2e suite to verify long-title
truncation.

| Field | Attribute | Description |
|---|---|---|
| `title` | string | 100 repeated `'A'` characters (per research R6). |
| `content` | string | The literal `'placeholder'`. |
| `status` | enum | `draft` (the test creates it as a draft so it never appears on the live home). |
| `post_author` | int | Default WP admin user (`post_author` defaults to 1 if unset). |
| `category` | list<int> | `wp_set_object_terms([], 'category')` — uncategorized, so the post does not appear in any category archive. |

### Validation

- The post is created via `wp_insert_post` and tagged via
  `wp_set_object_terms` with an empty term list.
- The suite MUST delete the post via `wp_delete_post` (or REST
  `DELETE /wp/v2/posts/<id>?force=true`) in the test's `afterEach`
  hook.
- The post is `draft` so it never appears in any test's
  category-archive query (which could pollute other specs).

### Lifecycle

| State | Trigger |
|---|---|
| **Created** | `beforeEach` of `long-title.spec.ts` (or first `beforeEach` of the test). |
| **Queried** | The test loads `/` and asserts the post card's title element height. |
| **Deleted** | `afterEach` of the same test (cleanup). |

---

## E2 — Playwright Test Fixture

A shared module that bootstraps the e2e suite.

| Field | Attribute | Description |
|---|---|---|
| `expectDockerHarnessUp` | function | `test.beforeAll(async ({ page, baseURL }) => { ... })` — fetches `GET baseURL`, asserts HTTP 200 + `<title>` contains the Site title. |
| `createSyntheticLongTitlePost` | async function | Creates the synthetic post; returns `{ id, title }`. Uses WP REST API (`POST /wp-json/wp/v2/posts`). |
| `deleteSyntheticLongTitlePost` | async function | Deletes the synthetic post; called from `afterEach`. Uses WP REST API `DELETE /wp-json/wp/v2/posts/<id>?force=true`. |

### Validation

- `expectDockerHarnessUp` MUST fail fast if the harness is
  unreachable (HTTP non-200 or non-Safe Mídia title); the suite
  reports "Docker harness unreachable" and skips the test.
- The synthetic post MUST be deleted even when assertions fail
  (`afterEach`, not `afterAll`).
- `createSyntheticLongTitlePost` uses the WP REST API with the
  `X-WP-Nonce` header (admin user authenticated) — this requires
  the Docker harness's auth WP admin credentials.

### Lifecycle

| State | Trigger |
|---|---|
| **Loaded** | Once per test file via `import { ... } from './fixtures'`. |
| **`expectDockerHarnessUp` invoked** | `beforeAll` of every spec. |
| **`createSyntheticLongTitlePost` invoked** | `beforeEach` of `long-title.spec.ts`. |
| **`deleteSyntheticLongTitlePost` invoked** | `afterEach` of `long-title.spec.ts`. |

---

## Cross-entity invariants

1. The synthetic post (E1) is NEVER published (`status: 'draft'`) so
   it does not pollute the news grid or any category archive. The
   home grid queries published posts only.
2. The Playwright fixture (E2) assumes the WP REST API is reachable
   (no auth required for reads; nonce write required for the synthetic
   post's create / delete).
3. All visual tokens in the theme continue to come from
   `theme/style.css`'s `:root` — the e2e suite does NOT modify the
   tokens (per FR-013).

---

## Out-of-scope entities (deliberately omitted)

- **Visual snapshot fixtures** — not in v1 (Playwright's `toHaveScreenshot`
  is deferred).
- **CI pipeline configuration** — the suite is `npm run test:e2e`,
  the runner is dev-time; CI integration deferred.
- **Cross-browser projects** — Chromium only in v1; Firefox /
  WebKit deferred.