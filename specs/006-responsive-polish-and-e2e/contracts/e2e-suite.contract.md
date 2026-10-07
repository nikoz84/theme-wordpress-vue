# E2E Suite Contract: Home-Page Responsive Polish & Playwright E2E

**Date**: 2026-10-06
**Spec**: `specs/006-responsive-polish-and-e2e/spec.md`
**Branch**: `006-responsive-polish-and-e2e`

Defines the Playwright e2e suite's configuration, fixtures, and
test-file contract.

## Configuration contract — `playwright.config.ts`

The file MUST be at the repo root and export a `defineConfig({ ... })`
with at minimum:

```ts
{
  testDir: './tests/e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  reporter: process.env.CI ? 'github' : 'list',
  use: {
    baseURL: process.env.PW_URL ?? 'http://localhost:8080',
    headless: !process.env.PWDEBUG,
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
}
```

## Fixture contract — `tests/e2e/fixtures.ts`

The file MUST export two helpers:

| Helper | Signature | Purpose |
|---|---|---|
| `expectDockerHarnessUp` | `async ({ page, baseURL }: { page: Page; baseURL: string }) => Promise<void>` | `test.beforeAll` hook that GETs the base URL and asserts HTTP 200 + `<title>` contains "Safe Mídia". |
| `createSyntheticLongTitlePost` | `async ({ request }: { request: APIRequestContext; baseURL: string }) => Promise<{ id: number; title: string }>` | Creates a draft post with title = 100 × `'A'`; returns `{ id, title }`. |
| `deleteSyntheticLongTitlePost` | `async ({ request }: { request: APIRequestContext; baseURL: string }, id: number) => Promise<void>` | Deletes the post by ID via `DELETE /wp-json/wp/v2/posts/<id>?force=true`. |

All WP REST calls in fixtures use the admin user's `X-WP-Nonce`
header. For v1 the nonce is read once per fixture call (no caching).

## Test-file contract

| File | Stories covered | Required assertions |
|---|---|---|
| `tests/e2e/navbar-brand.spec.ts` | US2 | `.logo-text` reads "Safe Mídia" on `/`. |
| `tests/e2e/title-decoupling.spec.ts` | US2 | `<title>` reads "Safe Mídia" by default; setting `blogname` to "Anything Here" updates `<title>` only, not the navbar. |
| `tests/e2e/no-overflow.spec.ts` | US1 | `document.documentElement.scrollWidth === document.documentElement.clientWidth` at viewports `[1280, 1024, 768, 414, 375]` × width. |
| `tests/e2e/long-title.spec.ts` | US3 | Synthetic long-title post is created; card title element's offsetHeight is not 0 (placeholder is rendered); teardown deletes the post. |

## Viewport matrix

Documented in `no-overflow.spec.ts` via `test.use({ viewport })`:

| Width (CSS px) | Height (CSS px) | Safe Mídia breakpoint covered |
|---|---|---|
| 1280 | 800 | desktop reference |
| 1024 | 800 | ≤1024 (tablet) |
| 768 | 1024 | ≤768 (mobile) |
| 414 | 896 | mobile reference |
| 375 | 812 | ≤420 (small mobile) |

## Validation

Per spec SC-004: `npm run test:e2e` exits 0 against a healthy Docker
harness; exits non-zero when any of the responsive / brand-text /
long-title assertions fail.

Verification (manual run):

```bash
docker compose up -d
npm install
npx playwright install chromium
npm run test:e2e
echo "exit=$?"
```

Expected: `exit=0` on success; `exit=1` (or any non-zero) on any
assertion failure.