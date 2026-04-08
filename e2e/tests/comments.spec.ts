import { test, expect, type Page } from '@playwright/test';

/** Wait for the initial 3 mock statuses to be rendered. */
async function waitForStatuses(page: Page) {
  await expect(page.locator('li.status')).toHaveCount(3, { timeout: 10_000 });
}

// ---------------------------------------------------------------------------
// Display
// ---------------------------------------------------------------------------
test.describe('Comments - Display', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await waitForStatuses(page);
  });

  test('each status shows its comments', async ({ page }) => {
    for (let i = 1; i <= 3; i++) {
      const status = page.locator(`#status-${i}`);
      await expect(status.locator('.comment')).toHaveCount(1);
    }
  });

  test('comment shows the commenter avatar', async ({ page }) => {
    const firstComment = page.locator('#status-1 .comment').first();
    const avatar = firstComment.locator('.avatar_compact img');
    await expect(avatar).toBeVisible();
  });

  test('comment shows the commenter name', async ({ page }) => {
    const firstComment = page.locator('#status-1 .comment').first();
    await expect(firstComment.locator('.avatar_compact')).toContainText('Test User');
  });

  test('comment shows a timestamp', async ({ page }) => {
    const firstComment = page.locator('#status-1 .comment').first();
    await expect(firstComment.locator('.time_stamp')).toBeVisible();
  });
});

// ---------------------------------------------------------------------------
// Post Comment
// ---------------------------------------------------------------------------
test.describe('Comments - Post Comment', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await waitForStatuses(page);
  });

  test('comment editor is visible inside each status', async ({ page }) => {
    for (let i = 1; i <= 3; i++) {
      const status = page.locator(`#status-${i}`);
      const commentEditor = status.locator('.comment_posting [data-testid="content"]');
      await expect(commentEditor).toBeVisible();
    }
  });

  test('type content and submit adds a new comment', async ({ page }) => {
    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    const firstStatus = page.locator('#status-1');
    const editor = firstStatus.locator('.comment_posting [data-testid="content"]');
    const sendButton = firstStatus.locator('.comment_posting [data-testid="send"]');

    await editor.fill('My new E2E comment');
    await sendButton.click();

    // Should go from 1 to 2 comments on the first status
    await expect(firstStatus.locator('.comment')).toHaveCount(2, { timeout: 10_000 });

    // The new comment should contain the posted text
    await expect(firstStatus.locator('.comment').last()).toContainText('My new E2E comment');
  });

  test('comment editor clears after successful post', async ({ page }) => {
    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    const firstStatus = page.locator('#status-1');
    const editor = firstStatus.locator('.comment_posting [data-testid="content"]');
    const sendButton = firstStatus.locator('.comment_posting [data-testid="send"]');

    await editor.fill('Comment that should clear');
    await sendButton.click();

    // Wait for the new comment to appear
    await expect(firstStatus.locator('.comment')).toHaveCount(2, { timeout: 10_000 });

    // Editor should be empty
    await expect(editor).toHaveValue('');
  });

  test('empty comment shows error and does not submit', async ({ page }) => {
    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    const firstStatus = page.locator('#status-1');
    const sendButton = firstStatus.locator('.comment_posting [data-testid="send"]');

    // Click send without typing anything
    await sendButton.click();

    await page.waitForTimeout(1000);

    // Comment count should remain at 1
    await expect(firstStatus.locator('.comment')).toHaveCount(1);

    // Error toast should appear
    const toast = page.locator('.errorbox');
    await expect(toast).toContainText('You need to type something!');
  });
});

// ---------------------------------------------------------------------------
// Delete Comment
// ---------------------------------------------------------------------------
test.describe('Comments - Delete Comment', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await waitForStatuses(page);
  });

  test('delete button is visible on comments', async ({ page }) => {
    const deleteButtons = page.locator('[data-testid="deleteComment"]');
    // 3 statuses × 1 comment each = 3 delete buttons
    await expect(deleteButtons).toHaveCount(3);
  });

  test('clicking delete shows confirmation dialog', async ({ page }) => {
    const deleteButton = page.locator('[data-testid="deleteComment"]').first();

    let dialogType = '';
    page.once('dialog', async (dialog) => {
      dialogType = dialog.type();
      await dialog.dismiss();
    });

    await deleteButton.dispatchEvent('click');

    expect(dialogType).toBe('confirm');
  });

  test('confirming removes the comment', async ({ page }) => {
    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    const firstStatus = page.locator('#status-1');
    const deleteButton = firstStatus.locator('[data-testid="deleteComment"]');

    await deleteButton.dispatchEvent('click');

    // Comment should be removed
    await expect(firstStatus.locator('.comment')).toHaveCount(0, { timeout: 10_000 });
  });

  test('cancelling keeps the comment', async ({ page }) => {
    page.on('dialog', async (dialog) => {
      await dialog.dismiss();
    });

    const firstStatus = page.locator('#status-1');
    const deleteButton = firstStatus.locator('[data-testid="deleteComment"]');

    await deleteButton.dispatchEvent('click');

    await page.waitForTimeout(1000);

    // Comment should still be there
    await expect(firstStatus.locator('.comment')).toHaveCount(1);
  });
});
