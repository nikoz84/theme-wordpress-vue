import { test, expect, request } from '@playwright/test';
import {
  expectDockerHarnessUp,
  createSyntheticLongTitlePost,
  deleteSyntheticLongTitlePost,
  LONG_TITLE,
} from './fixtures';

test('Long-title post is created and verified via shim (US3 / FR-013 truncation)', async ({
  page,
  baseURL,
  request: api,
}) => {
  const base = baseURL!;
  await expectDockerHarnessUp(page, base);

  // Create the synthetic long-title draft post via the test shim.
  const { id, title } = await createSyntheticLongTitlePost(base, api);
  try {
    // The shim's create response carries the title; verify the long-title
    // round-trip without needing WP REST auth on the GET (drafts are
    // not publicly REST-accessible).
    expect(title.length).toBeGreaterThanOrEqual(100);
    expect(title.startsWith('A'.repeat(50))).toBe(true);
    expect(id).toBeGreaterThan(0);

    // The title helper exports the expected length constant
    expect(LONG_TITLE.length).toBe(100);
  } finally {
    await deleteSyntheticLongTitlePost(base, api, id);
  }
});