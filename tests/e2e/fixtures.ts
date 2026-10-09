import { test, expect, request, type Page, type APIRequestContext } from '@playwright/test';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

/**
 * Fixture helpers for the Vue Blocks e2e suite.
 *
 * - `expectDockerHarnessUp` runs in beforeAll; asserts the Docker
 *   harness is reachable at baseURL and the navbar shows "Safe Mídia".
 * - `createSyntheticLongTitlePost` creates a draft post via WP REST
 *   with a 100-char title; returns `{ id, title }`.
 * - `deleteSyntheticLongTitlePost` deletes the post by id via WP REST.
 * - `loginAsAdmin` logs the page's browser context into wp-admin with
 *   the credentials from the repo-root `.env`.
 * - `restNonce` returns a `X-WP-Nonce` for authenticated REST calls
 *   made with `page.request` after `loginAsAdmin`.
 */

export const LONG_TITLE = 'A'.repeat(100);
export const SYNTHETIC_BODY = 'placeholder';

export async function expectDockerHarnessUp(page: Page, baseURL: string) {
  const res = await page.goto(baseURL, { waitUntil: 'domcontentloaded' });
  expect(res?.status(), `Docker harness reachable`).toBeLessThan(400);
  const brandText = await page.locator('.logo-text').first().innerText();
  expect(brandText.trim(), 'navbar brand reads Safe Mídia').toBe('Safe Mídia');
}

export async function createSyntheticLongTitlePost(
  baseURL: string,
  api: APIRequestContext,
): Promise<{ id: number; title: string }> {
  const res = await api.post(`${baseURL}/wp-json/vue-blocks/v1/test-create-draft-post`, {
    data: { title: LONG_TITLE, content: SYNTHETIC_BODY },
  });
  expect(res.status(), 'create synthetic post').toBe(200);
  const body = await res.json();
  return { id: body.id, title: body.title };
}

export async function deleteSyntheticLongTitlePost(
  baseURL: string,
  api: APIRequestContext,
  id: number,
): Promise<void> {
  await api.post(`${baseURL}/wp-json/vue-blocks/v1/test-delete-post`, {
    data: { id },
  });
}
function adminCredentials(): { user: string; pass: string } {
  const env: Record<string, string> = {};
  try {
    for (const line of readFileSync(resolve(process.cwd(), '.env'), 'utf8').split('\n')) {
      const m = line.match(/^\s*([A-Z_]+)\s*=\s*(.*)\s*$/);
      if (m) env[m[1]] = m[2];
    }
  } catch {
    /* no .env — fall back to process.env */
  }
  return {
    user: process.env.WP_ADMIN_USER ?? env.WP_ADMIN_USER ?? 'admin',
    pass: process.env.WP_ADMIN_PASSWORD ?? env.WP_ADMIN_PASSWORD ?? '',
  };
}

export async function loginAsAdmin(page: Page, baseURL: string) {
  const { user, pass } = adminCredentials();
  await page.goto(`${baseURL}/wp-login.php`);
  await page.fill('#user_login', user);
  await page.fill('#user_pass', pass);
  await page.click('#wp-submit');
  await expect(page).toHaveURL(/wp-admin/);
}

export async function restNonce(page: Page, baseURL: string): Promise<string> {
  const res = await page.request.get(`${baseURL}/wp-admin/admin-ajax.php?action=rest-nonce`);
  expect(res.status(), 'rest nonce').toBe(200);
  return (await res.text()).trim();
}
