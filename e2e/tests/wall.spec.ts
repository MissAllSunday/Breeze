import { test, expect } from '@playwright/test';

test.describe('Wall - Display Statuses', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('page loads successfully', async ({ page }) => {
    await expect(page).toHaveTitle('Breeze - E2E Testing');
  });

  test('displays mock statuses from the API', async ({ page }) => {
    const statusItems = page.locator('li.status');

    await expect(statusItems).toHaveCount(3, { timeout: 10_000 });
  });

  test('each status contains the expected body text', async ({ page }) => {
    const statusItems = page.locator('li.status');
    await expect(statusItems).toHaveCount(3, { timeout: 10_000 });

    // Each status has id="status-{n}", target text within the status itself
    for (let i = 1; i <= 3; i++) {
      await expect(page.locator(`#status-${i}`)).toContainText(
        `This is mock status #${i} for E2E testing.`,
      );
    }
  });

  test('each status displays user info', async ({ page }) => {
    const statusItems = page.locator('li.status');
    await expect(statusItems).toHaveCount(3, { timeout: 10_000 });

    // Use the first .poster child (status-level, not comment-level)
    const firstStatus = page.locator('#status-1');
    await expect(firstStatus.locator('.poster').first()).toContainText('Test User');
  });

  test('each status displays a timestamp', async ({ page }) => {
    const statusItems = page.locator('li.status');
    await expect(statusItems).toHaveCount(3, { timeout: 10_000 });

    // Each status has at least one .time_stamp element
    for (let i = 1; i <= 3; i++) {
      await expect(page.locator(`#status-${i} .time_stamp`).first()).toBeVisible();
    }
  });

  test('each status has a comment section', async ({ page }) => {
    const statusItems = page.locator('li.status');
    await expect(statusItems).toHaveCount(3, { timeout: 10_000 });

    // The first status should show its comment
    const firstStatusComments = statusItems.nth(0).locator('.comment_posting');
    await expect(firstStatusComments).toBeVisible();
  });

  test('statuses display comments from the API', async ({ page }) => {
    const statusItems = page.locator('li.status');
    await expect(statusItems).toHaveCount(3, { timeout: 10_000 });

    // Each mock status has one comment - check the first one
    const firstStatus = statusItems.nth(0);
    await expect(firstStatus).toContainText('A comment on status #1');
  });

  test('editor is visible for posting new statuses', async ({ page }) => {
    // The editor should be visible since permissions.Status.post is true
    const editor = page.locator('[data-testid="content"]').first();
    await expect(editor).toBeVisible({ timeout: 10_000 });
  });

  test('Go Up button is visible', async ({ page }) => {
    const statusItems = page.locator('li.status');
    await expect(statusItems).toHaveCount(3, { timeout: 10_000 });

    const goUpButton = page.locator('input[name="Go Up"]');
    await expect(goUpButton).toBeVisible();
  });
});
