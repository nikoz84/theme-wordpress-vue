import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './fixtures';

/**
 * Customizer → "Demo content": import, verify the home sections, remove.
 *
 * Logs in with the admin credentials from the repo-root `.env` (the
 * same values the Docker bootstrap uses). Skips if the demo content
 * is already imported, so the local site is left as it was found.
 */

test.describe('Demo content (Customizer)', () => {
  test.describe.configure({ timeout: 120_000 });

  test('imports and removes the demo content', async ({ page, baseURL }) => {
    await loginAsAdmin(page, baseURL!);
    await page.goto(`${baseURL}/wp-admin/customize.php?autofocus[section]=vb_demo`);

    const importBtn = page.locator('.vb-demo-btn[data-demo-action="import"]');
    const removeBtn = page.locator('.vb-demo-btn[data-demo-action="remove"]');
    const status = page.locator('.vb-demo-status');
    await expect(importBtn).toBeVisible();
    test.skip(await importBtn.isDisabled(), 'Demo content already imported on this site');

    await importBtn.click();
    await expect(status).toContainText('Demo content imported', { timeout: 60_000 });
    await expect(removeBtn).toBeEnabled();
    await expect(importBtn).toBeDisabled();

    // Home sections that depend on the demo categories/authors render.
    const home = await page.context().newPage();
    await home.goto(baseURL!);
    await expect(home.locator('.colunistas-section .col-name', { hasText: 'Mariana Costa' })).toBeVisible();
    await expect(home.locator('.boletim-section')).toBeVisible();
    await expect(home.locator('img[src*="picsum.photos"]')).toHaveCount(0);
    await home.close();

    page.once('dialog', (dialog) => dialog.accept());
    await removeBtn.click();
    await expect(status).toContainText('Demo content removed', { timeout: 60_000 });
    await expect(importBtn).toBeEnabled();

    const after = await page.context().newPage();
    await after.goto(baseURL!);
    await expect(after.locator('.col-name', { hasText: 'Mariana Costa' })).toHaveCount(0);
    await after.close();
  });
});
