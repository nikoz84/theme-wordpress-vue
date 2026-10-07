import { test, expect } from '@playwright/test';
import { expectDockerHarnessUp } from './fixtures';

test('navbar brand reads "Safe Mídia" on the home page (US2 / FR-008)', async ({ page, baseURL }) => {
  await expectDockerHarnessUp(page, baseURL!);
  // expectDockerHarnessUp already asserts .logo-text's textContent.
  // Belt-and-suspenders: assert explicitly here too.
  const brandText = await page.locator('.logo-text').first().innerText();
  expect(brandText.trim()).toBe('Safe Mídia');
});