import { test, expect, type Page } from '@playwright/test';

/** Wait for the initial 3 mock statuses to be rendered. */
async function waitForStatuses(page: Page) {
  await expect(page.locator('li.status')).toHaveCount(3, { timeout: 10_000 });
}

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


test.describe('Wall - Post Status', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await waitForStatuses(page);
  });

  test('confirmation dialog appears before posting', async ({ page }) => {
    const editor = page.locator('[data-testid="content"]').first();
    const sendButton = page.locator('[data-testid="send"]').first();

    await editor.fill('Test status for confirmation');

    // Capture dialog info and dismiss it immediately so click() can resolve
    let dialogType = '';
    let dialogMessage = '';
    page.once('dialog', async (dialog) => {
      dialogType = dialog.type();
      dialogMessage = dialog.message();
      await dialog.dismiss();
    });

    await sendButton.click();

    expect(dialogType).toBe('confirm');
    expect(dialogMessage).toBeTruthy();
  });

  test('cancelling confirmation does not post', async ({ page }) => {
    const editor = page.locator('[data-testid="content"]').first();
    const sendButton = page.locator('[data-testid="send"]').first();

    await editor.fill('This should not be posted');

    // Dismiss the confirmation dialog
    page.on('dialog', async (dialog) => {
      await dialog.dismiss();
    });

    await sendButton.click();

    // Wait a moment to confirm nothing was added
    await page.waitForTimeout(1000);

    // Status count should remain at 3
    await expect(page.locator('li.status')).toHaveCount(3);

    // The editor should still have the content (not cleared)
    await expect(editor).toHaveValue('This should not be posted');
  });

  test('type content and submit adds a new status', async ({ page }) => {
    const editor = page.locator('[data-testid="content"]').first();
    const sendButton = page.locator('[data-testid="send"]').first();

    // Accept all dialogs (confirm)
    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    await editor.fill('My new E2E status post');
    await sendButton.click();

    // A 4th status should appear in the list
    await expect(page.locator('li.status')).toHaveCount(4, { timeout: 10_000 });

    // The new status should contain the posted text
    const allStatuses = page.locator('li.status');
    await expect(allStatuses.last()).toContainText('My new E2E status post');
  });

  test('editor clears after successful post', async ({ page }) => {
    const editor = page.locator('[data-testid="content"]').first();
    const sendButton = page.locator('[data-testid="send"]').first();

    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    await editor.fill('Status that should clear');
    await sendButton.click();

    // Wait for the new status to appear (confirms post succeeded)
    await expect(page.locator('li.status')).toHaveCount(4, { timeout: 10_000 });

    // Editor should be empty
    await expect(editor).toHaveValue('');
  });

  test('empty content shows error and does not submit', async ({ page }) => {
    const sendButton = page.locator('[data-testid="send"]').first();

    // Accept confirm dialog
    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    // Click send without typing anything
    await sendButton.click();

    // Wait briefly for potential error toast
    await page.waitForTimeout(1000);

    // Status count should remain at 3 (nothing posted)
    await expect(page.locator('li.status')).toHaveCount(3);

    // An error toast should be visible (rendered as div.errorbox by react-hot-toast)
    const toast = page.locator('.errorbox');
    await expect(toast).toContainText('You need to type something!');
  });
});
