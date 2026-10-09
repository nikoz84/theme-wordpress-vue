import { test, expect } from '@playwright/test';
import { expectDockerHarnessUp } from './fixtures';

test.describe('Navbar menu', () => {
  test('desktop nav renders flat links with no list bullets', async ({ page, baseURL }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await expectDockerHarnessUp(page, baseURL!);

    const navLinks = page.locator('.navbar .nav-links');
    await expect(navLinks).toBeVisible();
    await expect(navLinks.locator('ul, li')).toHaveCount(0);
    expect(await navLinks.locator('> a').count()).toBeGreaterThan(0);

    await expect(page.locator('#safe-midia-mobileMenu ul, #safe-midia-mobileMenu li')).toHaveCount(0);
  });

  test('mobile hamburger expands and collapses the menu', async ({ page, baseURL }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await expectDockerHarnessUp(page, baseURL!);

    const hamburger = page.locator('#safe-midia-navHamburger');
    const menu = page.locator('#safe-midia-mobileMenu');

    await expect(page.locator('.navbar .nav-links')).toBeHidden();
    await expect(hamburger).toBeVisible();
    await expect(menu).toBeHidden();
    await expect(hamburger).toHaveAttribute('aria-expanded', 'false');

    await hamburger.click();
    await expect(menu).toBeVisible();
    await expect(menu).toHaveClass(/\bopen\b/);
    await expect(hamburger).toHaveAttribute('aria-expanded', 'true');
    expect(await menu.locator('> a').count()).toBeGreaterThan(0);

    await hamburger.click();
    await expect(menu).toBeHidden();
    await expect(hamburger).toHaveAttribute('aria-expanded', 'false');
  });

  test('mobile menu closes on Escape', async ({ page, baseURL }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await expectDockerHarnessUp(page, baseURL!);

    const hamburger = page.locator('#safe-midia-navHamburger');
    const menu = page.locator('#safe-midia-mobileMenu');

    await hamburger.click();
    await expect(menu).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(menu).toBeHidden();
    await expect(hamburger).toBeFocused();
  });
});
