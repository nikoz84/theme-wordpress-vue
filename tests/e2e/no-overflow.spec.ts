import { test, expect } from '@playwright/test';
import { expectDockerHarnessUp } from './fixtures';

const VIEWPORTS = [
  { width: 1280, height: 800, name: 'desktop' },
  { width: 1024, height: 800, name: 'tablet' },
  { width: 768, height: 1024, name: 'mobile' },
  { width: 414, height: 896, name: 'mobile-small' },
  { width: 375, height: 812, name: 'mobile-xs' },
];

test.describe('Home page: no horizontal overflow at Safe Mídia breakpoints (US1 / SC-001)', () => {
  for (const vp of VIEWPORTS) {
    test(`viewport ${vp.width}x${vp.height} (${vp.name})`, async ({ page, baseURL }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await expectDockerHarnessUp(page, baseURL!);
      const overflow = await page.evaluate(() => ({
        scroll: document.documentElement.scrollWidth,
        client: document.documentElement.clientWidth,
      }));
      expect(overflow.scroll, `scrollWidth at ${vp.width}px`).toBeLessThanOrEqual(overflow.client);
    });
  }
});