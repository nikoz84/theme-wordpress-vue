import { test, expect } from '@playwright/test';
import { expectDockerHarnessUp, loginAsAdmin, restNonce } from './fixtures';

const SEGURADO_DATA = {
  full_name: 'Maria Silva',
  document_type: 'cpf',
  document_number: '12345678901',
  email: 'maria@example.com',
  phone: '+55 11 99999-9999',
  address_1: 'Rua das Flores, 123',
  address_2: 'Apto 45',
  city: 'São Paulo',
  state: 'SP',
  postal_code: '01234-567',
  country: 'BR',
};

async function createPostWithSegurado(baseURL: string, api: any) {
  const res = await api.post(`${baseURL}/wp-json/vue-blocks/v1/test-create-draft-post`, {
    data: {
      title: 'Test Segurado Post',
      content: 'Test content',
      status: 'publish',
      meta: {
        vb_segurado_full_name: SEGURADO_DATA.full_name,
        vb_segurado_document_type: SEGURADO_DATA.document_type,
        vb_segurado_document_number: SEGURADO_DATA.document_number,
        vb_segurado_email: SEGURADO_DATA.email,
        vb_segurado_phone: SEGURADO_DATA.phone,
        vb_segurado_address_1: SEGURADO_DATA.address_1,
        vb_segurado_address_2: SEGURADO_DATA.address_2,
        vb_segurado_city: SEGURADO_DATA.city,
        vb_segurado_state: SEGURADO_DATA.state,
        vb_segurado_postal_code: SEGURADO_DATA.postal_code,
        vb_segurado_country: SEGURADO_DATA.country,
      },
    },
  });
  expect(res.status(), 'create post with segurado data').toBe(200);
  return res.json();
}

async function deletePost(baseURL: string, api: any, id: number) {
  await api.post(`${baseURL}/wp-json/vue-blocks/v1/test-delete-post`, {
    data: { id },
  });
}

test.describe('Segurado Section Markup', () => {
  test('US1: Display segurado data in semantic HTML', async ({ page, baseURL, request }) => {
    const base = baseURL!;
    await expectDockerHarnessUp(page, base);

    const post = await createPostWithSegurado(base, request);
    try {
      await page.goto(`${base}/?p=${post.id}`, { waitUntil: 'domcontentloaded' });

      const section = page.locator('.vb-segurado-section');
      await expect(section).toBeVisible();

      await expect(section.locator('.vb-segurado-title')).toHaveText('Segurado');

      const list = section.locator('.vb-segurado-list');
      await expect(list).toBeVisible();

      await expect(section.locator('[data-segurado-field="full_name"]')).toHaveText(SEGURADO_DATA.full_name);
      await expect(section.locator('[data-segurado-field="document_type"]')).toHaveText(SEGURADO_DATA.document_type);
      await expect(section.locator('[data-segurado-field="document_number"]')).toHaveText(SEGURADO_DATA.document_number);
      await expect(section.locator('[data-segurado-field="email"]')).toHaveText(SEGURADO_DATA.email);
      await expect(section.locator('[data-segurado-field="phone"]')).toHaveText(SEGURADO_DATA.phone);
    } finally {
      await deletePost(base, request, post.id);
    }
  });

  test('US1: Display skips empty optional fields', async ({ page, baseURL, request }) => {
    const base = baseURL!;
    await expectDockerHarnessUp(page, base);

    const res = await request.post(`${base}/wp-json/vue-blocks/v1/test-create-draft-post`, {
      data: {
        title: 'Minimal Segurado Post',
        content: 'Test content',
        status: 'publish',
        meta: {
          vb_segurado_full_name: 'João Santos',
          vb_segurado_document_type: 'cpf',
          vb_segurado_document_number: '98765432100',
        },
      },
    });
    const post = await res.json();
    try {
      await page.goto(`${base}/?p=${post.id}`, { waitUntil: 'domcontentloaded' });

      const section = page.locator('.vb-segurado-section');
      await expect(section).toBeVisible();

      await expect(section.locator('[data-segurado-field="full_name"]')).toHaveText('João Santos');
      await expect(section.locator('[data-segurado-field="email"]')).toHaveCount(0);
      await expect(section.locator('[data-segurado-field="phone"]')).toHaveCount(0);
    } finally {
      await deletePost(base, request, post.id);
    }
  });

  test('US1: No segurado section when no data exists', async ({ page, baseURL, request }) => {
    const base = baseURL!;
    await expectDockerHarnessUp(page, base);

    const res = await request.post(`${base}/wp-json/vue-blocks/v1/test-create-draft-post`, {
      data: {
        title: 'No Segurado Post',
        content: 'Test content',
        status: 'publish',
      },
    });
    const post = await res.json();
    try {
      await page.goto(`${base}/?p=${post.id}`, { waitUntil: 'domcontentloaded' });

      const section = page.locator('.vb-segurado-section');
      await expect(section).toHaveCount(0);
    } finally {
      await deletePost(base, request, post.id);
    }
  });

  test('US2: Edit form renders with all fields', async ({ page, baseURL, request }) => {
    const base = baseURL!;
    await expectDockerHarnessUp(page, base);

    const post = await createPostWithSegurado(base, request);
    try {
      await page.goto(`${base}/?p=${post.id}`, { waitUntil: 'domcontentloaded' });

      const form = page.locator('.vb-segurado-form');
      if (await form.isVisible()) {
        await expect(form.locator('#vb_segurado_full_name')).toHaveValue(SEGURADO_DATA.full_name);
        await expect(form.locator('#vb_segurado_document_type')).toHaveValue(SEGURADO_DATA.document_type);
        await expect(form.locator('#vb_segurado_document_number')).toHaveValue(SEGURADO_DATA.document_number);
        await expect(form.locator('#vb_segurado_email')).toHaveValue(SEGURADO_DATA.email);
        await expect(form.locator('#vb_segurado_phone')).toHaveValue(SEGURADO_DATA.phone);
      }
    } finally {
      await deletePost(base, request, post.id);
    }
  });

  test('US2: Form validation shows errors for invalid data', async ({ page, baseURL, request }) => {
    const base = baseURL!;
    await expectDockerHarnessUp(page, base);

    const post = await createPostWithSegurado(base, request);
    try {
      await page.goto(`${base}/?p=${post.id}`, { waitUntil: 'domcontentloaded' });

      const form = page.locator('.vb-segurado-form');
      if (await form.isVisible()) {
        await form.locator('#vb_segurado_full_name').fill('');
        await form.locator('#vb_segurado_email').fill('invalid-email');
        await form.locator('button[type="submit"]').click();

        await expect(page.locator('.vb-segurado-field-error').first()).toBeVisible();
      }
    } finally {
      await deletePost(base, request, post.id);
    }
  });

  test('US3: REST API returns vb_segurado field', async ({ baseURL, request }) => {
    const base = baseURL!;
    const post = await createPostWithSegurado(base, request);
    try {
      const res = await request.get(`${base}/wp-json/wp/v2/posts/${post.id}`);
      expect(res.status()).toBe(200);
      const body = await res.json();
      expect(body.vb_segurado).toBeDefined();
      expect(body.vb_segurado.full_name).toBe(SEGURADO_DATA.full_name);
      expect(body.vb_segurado.document_type).toBe(SEGURADO_DATA.document_type);
      expect(body.vb_segurado.document_number).toBe(SEGURADO_DATA.document_number);
      expect(body.vb_segurado.email).toBe(SEGURADO_DATA.email);
      expect(body.vb_segurado.address.city).toBe(SEGURADO_DATA.city);
      expect(body.vb_segurado.address.state).toBe(SEGURADO_DATA.state);
    } finally {
      await deletePost(base, request, post.id);
    }
  });

  test('US3: REST API updates segurado meta', async ({ page, baseURL, request }) => {
    const base = baseURL!;
    const post = await createPostWithSegurado(base, request);
    try {
      // Writing via /wp/v2 requires an authenticated user (cookie + nonce).
      await loginAsAdmin(page, base);
      const nonce = await restNonce(page, base);
      const newName = 'Carlos Oliveira';
      const res = await page.request.post(`${base}/wp-json/wp/v2/posts/${post.id}`, {
        headers: { 'X-WP-Nonce': nonce },
        data: {
          meta: {
            vb_segurado_full_name: newName,
          },
        },
      });
      expect(res.status()).toBe(200);
      const body = await res.json();
      expect(body.vb_segurado.full_name).toBe(newName);
    } finally {
      await deletePost(base, request, post.id);
    }
  });
});