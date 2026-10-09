import { test, expect, request as pwRequest } from '@playwright/test';

/**
 * News list (`/noticias/`) + article page (layouts-html/02 and 03),
 * the "Uncategorized" filter and the "no e-mail in bylines" rule.
 *
 * Uses the demo content (imported via the dev-only shim if needed and
 * removed again afterwards, so the site is left as it was found).
 */

const DEMO_ARTICLE = '/demo-susep-consulta-publica/';
const MOST_VIEWED_TITLE = 'SUSEP abre consulta pública sobre seguros de danos';
const EMAIL_NAME = 'pauta.teste@example.com';

let importedHere = false;

test.beforeAll(async ({ baseURL }) => {
  const api = await pwRequest.newContext();
  const res = await api.post(`${baseURL}/wp-json/vue-blocks/v1/test-demo`, { data: { action: 'import' } });
  expect(res.status(), 'demo import shim').toBe(200);
  importedHere = (await res.json()).already === false;
  await api.dispose();
});

test.afterAll(async ({ baseURL }) => {
  if (!importedHere) return;
  const api = await pwRequest.newContext();
  await api.post(`${baseURL}/wp-json/vue-blocks/v1/test-demo`, { data: { action: 'remove' } });
  await api.dispose();
});

test.describe('News list (/noticias/)', () => {
  test('renders hero, pills, list, pagination and sidebar', async ({ page, baseURL }) => {
    await page.goto(`${baseURL}/noticias/`);
    await expect(page.locator('.page-hero .page-title')).toHaveText('Últimas Notícias');
    await expect(page.locator('.filter-pills .pill.active')).toHaveText('Todas');
    expect(await page.locator('.news-list .list-item').count()).toBeGreaterThan(0);
    await expect(page.locator('.pagination .page-btn.active')).toHaveText('1');
    await expect(page.locator('.sidebar .sidebar-nl form')).toBeVisible();
    await expect(page.locator('.sidebar .trend-item')).toHaveCount(5);
    await expect(page.locator('.sidebar .ad-label')).toHaveText('Publicidade');
  });

  test('category pill opens the category with the pill active', async ({ page, baseURL }) => {
    await page.goto(`${baseURL}/noticias/`);
    const pill = page.locator('.filter-pills').getByRole('link', { name: 'Mercado', exact: true });
    await pill.click();
    await expect(page).toHaveURL(/\/category\/mercado\//);
    await expect(page.locator('.filter-pills .pill.active')).toHaveText('Mercado');
    for (const badge of await page.locator('.list-cat-badge').allInnerTexts()) {
      expect(badge.toLowerCase()).not.toBe('uncategorized');
    }
  });

  test('"Mais lidas" sort orders by views and keeps ?ordem on pagination', async ({ page, baseURL }) => {
    await page.goto(`${baseURL}/noticias/`);
    await page.selectOption('#vb-sort', 'lidas');
    await expect(page).toHaveURL(/ordem=lidas/);
    await expect(page.locator('.list-item .list-title').first()).toHaveText(MOST_VIEWED_TITLE);
    const next = page.locator('.pagination a.page-btn.prev-next');
    if (await next.count()) {
      expect(await next.last().getAttribute('href')).toContain('ordem=lidas');
    }
  });
});

test.describe('Uncategorized + author privacy', () => {
  test('uncategorized posts are hidden from lists but reachable directly', async ({ page, baseURL, request }) => {
    const res = await request.post(`${baseURL}/wp-json/vue-blocks/v1/test-create-draft-post`, {
      data: { title: 'Uncategorized Hidden Post', content: 'x', status: 'publish' },
    });
    const post = await res.json();
    try {
      for (const path of ['/', '/noticias/']) {
        await page.goto(`${baseURL}${path}`);
        await expect(page.locator('main', { hasText: 'Uncategorized Hidden Post' })).toHaveCount(0);
      }
      await page.goto(`${baseURL}/?p=${post.id}`);
      await expect(page.locator('.article-title')).toHaveText('Uncategorized Hidden Post');
    } finally {
      await request.post(`${baseURL}/wp-json/vue-blocks/v1/test-delete-post`, { data: { id: post.id } });
    }
  });

  test('an author whose display name is an e-mail is never printed', async ({ page, baseURL, request }) => {
    const res = await request.post(`${baseURL}/wp-json/vue-blocks/v1/test-create-draft-post`, {
      data: { title: 'Email Author Post', content: 'x', status: 'publish', email_author: true },
    });
    const post = await res.json();
    try {
      await page.goto(`${baseURL}/?p=${post.id}`);
      await expect(page.locator('.meta-name')).toHaveText('Redação');
      expect(await page.locator('body').innerText()).not.toContain(EMAIL_NAME);
      expect(await page.content()).not.toContain(EMAIL_NAME);
    } finally {
      await request.post(`${baseURL}/wp-json/vue-blocks/v1/test-delete-post`, { data: { id: post.id } });
    }
  });
});

test.describe('Article page', () => {
  test('header, share, tags, editor box, Leia também and sidebar widgets', async ({ page, baseURL }) => {
    await page.goto(`${baseURL}${DEMO_ARTICLE}`);
    await expect(page.locator('.article-title')).toHaveText(MOST_VIEWED_TITLE);
    await expect(page.locator('.article-cat')).toContainText(/Há .* · /);
    await expect(page.locator('.meta-readtime')).toContainText('Leitura de');

    const permalink = encodeURIComponent(`${baseURL}${DEMO_ARTICLE}`);
    const shares = await page.locator('.share-bar a.share-btn').evaluateAll((as) => as.map((a) => (a as HTMLAnchorElement).href));
    expect(shares).toHaveLength(4);
    for (const host of ['facebook.com', 'x.com', 'linkedin.com', 'whatsapp.com']) {
      const href = shares.find((h) => h.includes(host));
      expect(href, host).toBeDefined();
      expect(href).toContain(permalink);
    }
    await expect(page.locator('.share-btn.copy')).toBeVisible();

    await expect(page.locator('.article-tags .tag')).toHaveText(['Regulação', 'SUSEP']);
    await expect(page.locator('.author-box .author-box-name')).toBeVisible();
    await expect(page.locator('.read-also .ralso-card')).toHaveCount(2);

    await expect(page.locator('.sidebar .sidebar-nl-title')).toHaveText('Fique por dentro');
    await expect(page.locator('.sidebar .trend-item')).toHaveCount(5);
    await expect(page.locator('.sidebar .rel-item')).toHaveCount(4);
    const readAlso = await page.locator('.ralso-card').evaluateAll((as) => as.map((a) => (a as HTMLAnchorElement).href));
    const related = await page.locator('.rel-item').evaluateAll((as) => as.map((a) => (a as HTMLAnchorElement).href));
    expect(readAlso.filter((h) => related.includes(h))).toHaveLength(0);
    await expect(page.locator('.sidebar .vb-ad-slot')).toBeVisible();
  });

  test('editor box shows the job title of a columnist', async ({ page, baseURL }) => {
    await page.goto(`${baseURL}/demo-coluna-mercado-em-transformacao/`);
    await expect(page.locator('.meta-name')).toHaveText('Fernanda Lima');
    await expect(page.locator('.meta-role')).toHaveText('Editora de Mercado');
    await expect(page.locator('.author-box-role')).toContainText('Editora de Mercado');
    await expect(page.locator('.author-box-bio')).toContainText('Jornalista de economia');
  });

  test('copy link button copies the permalink', async ({ page, baseURL, context }) => {
    await context.grantPermissions(['clipboard-read', 'clipboard-write']);
    await page.goto(`${baseURL}${DEMO_ARTICLE}`);
    await page.locator('.share-btn.copy').click();
    await expect(page.locator('.share-btn.copy')).toContainText('Link copiado!');
    expect(await page.evaluate(() => navigator.clipboard.readText())).toBe(`${baseURL}${DEMO_ARTICLE}`);
  });
});

test.describe('Newsletter', () => {
  const email = 'e2e-newsletter@example.com';

  test.afterEach(async ({ baseURL, request }) => {
    await request.post(`${baseURL}/wp-json/vue-blocks/v1/test-newsletter-cleanup`, { data: { email } });
  });

  test('subscribes once and rejects a duplicate', async ({ page, baseURL, request }) => {
    await request.post(`${baseURL}/wp-json/vue-blocks/v1/test-newsletter-cleanup`, { data: { email } });
    await page.goto(`${baseURL}${DEMO_ARTICLE}`);
    const form = page.locator('.sidebar .sidebar-nl form');
    await form.locator('input[name="vb_email"]').fill(email);
    await form.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/newsletter=ok/);
    await expect(page.locator('.sidebar-nl-msg')).toContainText('Quase lá');

    await page.locator('.sidebar .sidebar-nl form input[name="vb_email"]').fill(email);
    await page.locator('.sidebar .sidebar-nl form button[type="submit"]').click();
    await expect(page).toHaveURL(/newsletter=exists/);
    await expect(page.locator('.sidebar-nl-msg')).toContainText('já está inscrito');
  });

  test('footer form posts to the same handler', async ({ page, baseURL }) => {
    await page.goto(`${baseURL}/noticias/`);
    const form = page.locator('#footer-newsletter');
    await form.locator('input[name="vb_email"]').fill(email);
    await form.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/nl=footer-newsletter/);
    await expect(page.locator('.vb-nl-inline-msg')).toContainText('Quase lá');
    await expect(page.locator('.sidebar-nl-msg')).toHaveCount(0);
  });
});
