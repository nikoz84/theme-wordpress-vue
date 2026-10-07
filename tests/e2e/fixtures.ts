import { test, expect, request, type Page, type APIRequestContext } from '@playwright/test';

/**
 * Fixture helpers for the Vue Blocks e2e suite.
 *
 * - `expectDockerHarnessUp` runs in beforeAll; asserts the Docker
 *   harness is reachable at baseURL and the navbar shows "Safe Mídia".
 * - `createSyntheticLongTitlePost` creates a draft post via WP REST
 *   with a 100-char title; returns `{ id, title }`.
 * - `deleteSyntheticLongTitlePost` deletes the post by id via WP REST.
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