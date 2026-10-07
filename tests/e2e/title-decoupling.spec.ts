import { test, expect, request } from '@playwright/test';
import { expectDockerHarnessUp } from './fixtures';

test('Customizer Site title updates <title> but NOT the navbar brand (US2 / FR-009)', async ({ page, baseURL, request: api }) => {
  const base = baseURL!;

  // 1. Default state — brand is "Safe Mídia" and <title> is "Safe Mídia"
  await expectDockerHarnessUp(page, base);
  const defaultTitle = await page.title();
  expect(defaultTitle.trim()).toBe('Safe Mídia');

  // 2. Mutate the blogname via the WP REST API (admin-only option)
  // Note: requires the WP admin user. The default admin in this stack is
  // admin / change-me-locally-admin (per .env.example). The Docker harness
  // is on a private network so we POST via the rest_no_cookie endpoint
  // by editing the option directly via wp-cli for portability.
  await page.evaluate(async () => {
    // Use a server-side request through WordPress's admin endpoint by
    // posting to the update_option REST route (requires nonce). Fallback:
    // rely on the test seeding that 'Safe Mídia' is the default and
    // verify the assertion below.
  });

  // For portability across WP versions, we update the blogname via a
  // server-side PHP shim exposed by the harness bootstrap: the option
  // update happens via the WP REST endpoint below; for that we POST to
  // /wp-json/vue-blocks/v1/test-set-blogname with no auth required.
  const setRes = await api.post(`${base}/wp-json/vue-blocks/v1/test-set-blogname`, {
    data: { blogname: 'Anything Here' },
  });
  if (setRes.status() === 404) {
    test.skip(true, 'harness does not provide the vue-blocks test shim');
  }
  expect(setRes.status(), 'set blogname shim').toBe(200);

  // 3. Reload home and assert <title> reflects "Anything Here"
  await page.goto(base, { waitUntil: 'domcontentloaded' });
  const newTitle = await page.title();
  expect(newTitle.trim()).toBe('Anything Here');

  // 4. Navbar brand MUST remain "Safe Mídia" (decoupled)
  const brandText = await page.locator('.logo-text').first().innerText();
  expect(brandText.trim()).toBe('Safe Mídia');

  // 4b. Footer brand MUST remain "Safe Mídia" too (US2 — feature 007)
  const footerBrand = await page.locator('.footer-bottom > span').first().innerText();
  expect(footerBrand).toContain('Safe Mídia');
  expect(footerBrand).not.toContain('Anything Here');

  // 5. Restore default blogname for downstream tests
  await api.post(`${base}/wp-json/vue-blocks/v1/test-set-blogname`, {
    data: { blogname: 'Safe Mídia' },
  });
});