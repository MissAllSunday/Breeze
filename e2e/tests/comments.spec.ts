import { test, expect, type Page, type APIRequestContext } from '@playwright/test';

/** Wait for the given number of mock statuses to be rendered. */
async function waitForStatuses(page: Page, count: number = 3) {
  await expect(page.locator('li.status')).toHaveCount(count, { timeout: 10_000 });
}

/**
 * The status-level action bar. Uses a direct-child chain so it never matches
 * the action bars nested inside comment cards.
 */
function statusActionBar(page: Page, statusId: number) {
  return page.locator(
    `#status-${statusId} > .post_wrapper > .postarea > .windowbg > [data-testid="actionBar"]`,
  );
}

/** Open the comment panel for a status by clicking its Comment action button. */
async function openCommentPanel(page: Page, statusId: number) {
  await statusActionBar(page, statusId).locator('[data-action-id="comment"] button').click();
}

/** Reset the mock API database back to its initial fixture state. */
async function resetDatabase(request: APIRequestContext) {
  const response = await request.get('http://api:8000/?action=reset');
  if (!response.ok()) {
    console.error('Database reset failed:', await response.text());
    throw new Error('Database reset failed');
  }
}

test.beforeAll(async ({ request }) => {
  await resetDatabase(request);
});

test.beforeEach(async ({ request }) => {
  await resetDatabase(request);
});

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

  test('comment editor is visible after opening the Comment panel', async ({ page }) => {
    // The editor lives inside a panel that is closed by default; it only appears
    // after clicking the Comment action button in the status action bar.
    for (let i = 1; i <= 3; i++) {
      await openCommentPanel(page, i);
      await expect(
        page.locator(`#status-${i} [data-testid="commentPanel"] [data-testid="content"]`),
      ).toBeVisible();
      // Close the panel before moving on to the next status
      await statusActionBar(page, i).locator('[data-action-id="comment"] button').click();
    }
  });

  test('type content and submit adds a new comment', async ({ page }) => {
    // Editor always shows window.confirm before posting — accept it.
    page.on('dialog', async (dialog) => await dialog.accept());

    const firstStatus = page.locator('#status-1');

    await openCommentPanel(page, 1);
    const panel = firstStatus.locator('[data-testid="commentPanel"]');
    await panel.locator('[data-testid="content"]').fill('My new E2E comment');
    await panel.locator('[data-testid="send"]').click();

    // Should go from 1 to 2 comments on the first status
    await expect(firstStatus.locator('.comment')).toHaveCount(2, { timeout: 10_000 });
    await expect(firstStatus.locator('.comment').last()).toContainText('My new E2E comment');
  });

  test('panel closes and reopening shows an empty editor after successful post', async ({ page }) => {
    // Editor always shows window.confirm before posting — accept it.
    page.on('dialog', async (dialog) => await dialog.accept());

    // The comment panel closes automatically on success (closePanel() is called).
    // Reopening it should show a fresh, empty editor.
    const firstStatus = page.locator('#status-1');

    await openCommentPanel(page, 1);
    const panel = firstStatus.locator('[data-testid="commentPanel"]');
    await panel.locator('[data-testid="content"]').fill('Comment that should clear');
    await panel.locator('[data-testid="send"]').click();

    // Wait for the new comment (confirms success)
    await expect(firstStatus.locator('.comment')).toHaveCount(2, { timeout: 10_000 });
    // Panel auto-closed
    await expect(firstStatus.locator('[data-testid="commentPanel"]')).toHaveCount(0);
    // Reopen — editor must be empty
    await openCommentPanel(page, 1);
    await expect(
      firstStatus.locator('[data-testid="commentPanel"] [data-testid="content"]'),
    ).toHaveValue('');
  });

  test('empty comment shows error and does not submit', async ({ page }) => {
    // confirmPost check fires before the empty-content guard. The error toast only
    // appears when the user *accepts* the dialog and content is still empty.
    page.on('dialog', async (dialog) => await dialog.accept());

    const firstStatus = page.locator('#status-1');

    await openCommentPanel(page, 1);
    const panel = firstStatus.locator('[data-testid="commentPanel"]');
    await panel.locator('[data-testid="send"]').click();

    await page.waitForTimeout(1000);

    // Comment count must remain at 1
    await expect(firstStatus.locator('.comment')).toHaveCount(1);
    // Error toast must appear
    await expect(page.locator('.errorbox')).toContainText('You need to type something!');
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
