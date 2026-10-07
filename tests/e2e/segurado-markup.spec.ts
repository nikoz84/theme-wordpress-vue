import { test, expect } from '@playwright/test';
import { expectDockerHarnessUp } from './fixtures';

test('"Para o Segurado" section renders the Safe Mídia structure (US1 / SC-001)', async ({ page, baseURL }) => {
  const base = baseURL!;
  await expectDockerHarnessUp(page, base);

  // The wrapper is present
  await expect(page.locator('.segurado-hd').first()).toBeVisible();

  // The heading reads exactly "Para o Segurado"
  await expect(page.locator('.segurado-title').first()).toHaveText('Para o Segurado');

  // The subtitle contains the Safe Mídia subtitle text
  await expect(page.locator('.segurado-sub').first()).toContainText(
    'Direitos, dicas e orientações para quem já tem ou quer contratar um seguro.',
  );

  // The "Ver todos" link has a non-`#` href
  const btn = page.getByRole('link', { name: 'Ver todos »' });
  const href = await btn.first().getAttribute('href');
  expect(href, 'home-page-href').not.toBeNull();
  expect(href, 'home-page-href-not-placeholder').not.toBe('#');
  expect(href, 'home-page-href-is-http').toMatch(/^https?:\/\//);
});