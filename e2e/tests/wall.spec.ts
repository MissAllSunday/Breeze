import { test, expect, type Page, type APIRequestContext } from '@playwright/test';

/** Wait for statuses to be rendered. Defaults to 3 (fixture count). */
async function waitForStatuses(page: Page, count: number = 3) {
  await expect(page.locator('li.status')).toHaveCount(count, { timeout: 10_000 });
}

/** Reset the mock API database back to its initial fixture state. */
async function resetDatabase(request: APIRequestContext) {
  const response = await request.get('http://api:8000/?action=reset');
  if (!response.ok()) {
    console.error('Database reset failed:', await response.text());
    throw new Error('Database reset failed');
  }
}

/**
 * Set up the block-visibility scenario: the DB contains 4 statuses but user 2
 * is in user 1's block list, so only 3 statuses should appear in the feed.
 */
async function setupBlockScenario(request: APIRequestContext) {
  const response = await request.get('http://api:8000/?action=blockScenario');
  if (!response.ok()) {
    console.error('Block scenario setup failed:', await response.text());
    throw new Error('Block scenario setup failed');
  }
}

/**
 * Revoke the viewGeneralWall permission. Subsequent wall feed requests
 * from the mock API will return 403 until the next reset.
 */
async function setupNoViewGeneralWall(request: APIRequestContext) {
  const response = await request.get('http://api:8000/?action=noViewGeneralWall');
  if (!response.ok()) {
    throw new Error('noViewGeneralWall setup failed: ' + (await response.text()));
  }
}

/**
 * Revoke the profile_view permission. Subsequent profile feed requests
 * from the mock API will return 403 until the next reset.
 */
async function setupNoProfileView(request: APIRequestContext) {
  const response = await request.get('http://api:8000/?action=noProfileView');
  if (!response.ok()) {
    throw new Error('noProfileView setup failed: ' + (await response.text()));
  }
}

/**
 * Activate the admin bypass. All permission checks are skipped, mirroring
 * SMF's behaviour for the admin member group.
 */
async function setupAdminScenario(request: APIRequestContext) {
  const response = await request.get('http://api:8000/?action=adminScenario');
  if (!response.ok()) {
    throw new Error('adminScenario setup failed: ' + (await response.text()));
  }
}

test.beforeAll(async ({ request }) => {
  await resetDatabase(request);
});

test.beforeEach(async ({ request }) => {
  await resetDatabase(request);
});

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
    // Posting requires a profile wall — the general wall is a read-only feed.
    await page.goto('/profile.html');
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

    // The new status should appear at the top (newest-first ordering)
    const allStatuses = page.locator('li.status');
    await expect(allStatuses.first()).toContainText('My new E2E status post');
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


test.describe('Wall - Likes', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await waitForStatuses(page);
  });

  test('like button is visible on each status', async ({ page }) => {
    // Each status has a Like component; comments also have likes nested inside,
    // so we assert per-status to avoid counting comment likes.
    for (let i = 1; i <= 3; i++) {
      const statusLike = page.locator(`#status-${i} .breeze_anchor.pointer_cursor`).first();
      await expect(statusLike).toBeVisible();
    }
  });

  test('like button is visible on each comment', async ({ page }) => {
    // Each comment has a Like component
    const commentLikes = page.locator('.comment .breeze_anchor.pointer_cursor');
    await expect(commentLikes).toHaveCount(3);
  });

  test('clicking status like shows confirmation dialog', async ({ page }) => {
    // .first() because the comment's like button is also nested inside #status-1
    const likeButton = page.locator('#status-1 .breeze_anchor.pointer_cursor').first();

    let dialogType = '';
    page.once('dialog', async (dialog) => {
      dialogType = dialog.type();
      await dialog.dismiss();
    });

    await likeButton.click();

    expect(dialogType).toBe('confirm');
  });

  test('confirming like on status changes emoji and shows like info', async ({ page }) => {
    // Accept all confirmation dialogs
    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    const firstStatus = page.locator('#status-1');
    // .first() to avoid matching the nested comment like button
    const likeButton = firstStatus.locator('.breeze_anchor.pointer_cursor').first();

    // Initial state: thumbs up (not yet liked)
    await expect(likeButton).toContainText('👍');

    // Like info should not be visible initially (count is 0)
    await expect(firstStatus.locator('[data-testid="likesInfo"]')).toHaveCount(0);

    await likeButton.click();

    // After liking: emoji changes to thumbs down
    await expect(likeButton).toContainText('👎');

    // Like info link should appear showing the like count
    const likesInfo = firstStatus.locator('[data-testid="likesInfo"]');
    await expect(likesInfo).toBeVisible();
  });

  test('clicking like info opens likers modal', async ({ page }) => {
    // Accept all confirmation dialogs
    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    const firstStatus = page.locator('#status-1');
    const likeButton = firstStatus.locator('.breeze_anchor.pointer_cursor').first();

    // Like the status first
    await likeButton.click();

    // Click the likes info link to open the modal
    const likesInfo = firstStatus.locator('[data-testid="likesInfo"]');
    await likesInfo.click();

    // Modal should be visible
    const modal = page.locator('#smf_popup.show');
    await expect(modal).toBeVisible();

    // Modal header should contain the like emoji
    await expect(modal.locator('.popup_heading')).toContainText('👍');

    // Close the modal — the icon span is 0×0 in headless E2E because the SMF
    // icon font never loads, so Playwright can't compute a click point even with
    // force:true. We use a JS click instead.
    await modal.locator('.hide_popup').evaluate((el) => (el as HTMLElement).click());
    await expect(modal).toHaveCount(0);
  });

  test('confirming like on comment changes emoji', async ({ page }) => {
    // Accept all confirmation dialogs
    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    const firstComment = page.locator('#comment-100');
    const likeButton = firstComment.locator('.breeze_anchor.pointer_cursor');

    // Initial state: thumbs up
    await expect(likeButton).toContainText('👍');

    await likeButton.click();

    // After liking: emoji changes to thumbs down
    await expect(likeButton).toContainText('👎');
  });

  test('clicking like again unlikes the status', async ({ page }) => {
    // Accept all confirmation dialogs
    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    const firstStatus = page.locator('#status-1');
    const likeButton = firstStatus.locator('.breeze_anchor.pointer_cursor').first();

    // Like the status first
    await likeButton.click();
    await expect(likeButton).toContainText('👎');

    // Click again to unlike
    await likeButton.click();

    // Emoji changes back to thumbs up
    await expect(likeButton).toContainText('👍');

    // Like info should be gone again
    await expect(firstStatus.locator('[data-testid="likesInfo"]')).toHaveCount(0);
  });

  test('clicking like again unlikes the comment', async ({ page }) => {
    // Accept all confirmation dialogs
    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    const firstComment = page.locator('#comment-100');
    const likeButton = firstComment.locator('.breeze_anchor.pointer_cursor');

    // Like the comment first
    await likeButton.click();
    await expect(likeButton).toContainText('👎');

    // Click again to unlike
    await likeButton.click();

    // Emoji changes back to thumbs up
    await expect(likeButton).toContainText('👍');
  });

  test('posted status survives a page refresh', async ({ page }) => {
    // The general wall has no editor, so navigate to the profile wall to post.
    await page.goto('/profile.html');
    await waitForStatuses(page);

    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    const editor = page.locator('[data-testid="content"]').first();
    const sendButton = page.locator('[data-testid="send"]').first();

    await editor.fill('Persistent status test');
    await sendButton.click();

    // Wait for the new status to appear on the profile wall.
    await expect(page.locator('li.status')).toHaveCount(4, { timeout: 10_000 });

    // Navigate back to the general wall and verify the status persisted.
    await page.goto('/');
    await waitForStatuses(page, 4);

    // All 4 statuses should still be present (including the new one)
    const allStatuses = page.locator('li.status');
    await expect(allStatuses).toHaveCount(4);

    // New statuses are sorted newest-first, so the posted status is first
    await expect(allStatuses.first()).toContainText('Persistent status test');
  });
});

test.describe('Wall - Delete Status', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await waitForStatuses(page);
  });

  test('delete button is visible on each status', async ({ page }) => {
    const deleteButtons = page.locator('[data-testid="deleteStatus"]');

    // Each of the 3 statuses should have a delete button
    await expect(deleteButtons).toHaveCount(3);
  });

  test('clicking delete shows confirmation dialog', async ({ page }) => {
    const deleteButton = page.locator('[data-testid="deleteStatus"]').first();

    let dialogType = '';
    let dialogMessage = '';
    page.once('dialog', async (dialog) => {
      dialogType = dialog.type();
      dialogMessage = dialog.message();
      await dialog.dismiss();
    });

    await deleteButton.dispatchEvent('click');

    expect(dialogType).toBe('confirm');
    expect(dialogMessage).toBeTruthy();
  });

  test('confirming removes the status from the list', async ({ page }) => {
    // Accept all dialogs
    page.on('dialog', async (dialog) => {
      await dialog.accept();
    });

    // Click delete on the first status
    const deleteButton = page.locator('#status-1 [data-testid="deleteStatus"]');
    await deleteButton.dispatchEvent('click');

    // Should go from 3 to 2 statuses
    await expect(page.locator('li.status')).toHaveCount(2, { timeout: 10_000 });

    // The deleted status (#status-1) should no longer exist
    await expect(page.locator('#status-1')).toHaveCount(0);
  });

  test('cancelling keeps the status in the list', async ({ page }) => {
    // Dismiss all dialogs
    page.on('dialog', async (dialog) => {
      await dialog.dismiss();
    });

    // Click delete on the first status
    const deleteButton = page.locator('#status-1 [data-testid="deleteStatus"]');
    await deleteButton.dispatchEvent('click');

    // Wait a moment to confirm nothing was removed
    await page.waitForTimeout(1000);

    // All 3 statuses should still be present
    await expect(page.locator('li.status')).toHaveCount(3);

    // The first status should still exist
    await expect(page.locator('#status-1')).toBeVisible();
  });
});


test.describe('Wall - Block Visibility', () => {
  test.beforeEach(async ({ request, page }) => {
    // Sets up 4 DB statuses (3 from user 1, 1 from blocked user 2).
    // The mock API filters out blocked users before returning the feed.
    await setupBlockScenario(request);
    await page.goto('/');
    await waitForStatuses(page, 3);
  });

  test('posts from a blocked user do not appear in the feed', async ({ page }) => {
    // The DB has 4 statuses but user 2 is blocked, so only 3 should render.
    await expect(page.locator('li.status')).toHaveCount(3, { timeout: 10_000 });

    // The blocked user's post body must not appear anywhere on the page.
    await expect(page.locator('body')).not.toContainText(
      'This post is from a blocked user and should not be visible.',
    );
  });

  test('own posts remain visible when a block is active', async ({ page }) => {
    // User 1's own 3 fixture posts must all still be present.
    for (let i = 1; i <= 3; i++) {
      await expect(page.locator(`#status-${i}`)).toContainText(
        `This is mock status #${i} for E2E testing.`,
      );
    }
  });
});


// ── Permission: viewGeneralWall ───────────────────────────────────────────────
//
// Three scenarios: permission denied, permission granted (default), and admin
// bypass. The mock API returns 403 for ?sa=wall when the permission is absent,
// so the React app receives no data and must render zero statuses.

test.describe('Wall - viewGeneralWall Permission', () => {
  test('access denied: no statuses rendered when permission is not granted', async ({
    request,
    page,
  }) => {
    await setupNoViewGeneralWall(request);
    await page.goto('/');

    // Give the app time to finish its fetch attempt; it should end up empty.
    await page.waitForTimeout(3_000);

    await expect(page.locator('li.status')).toHaveCount(0);
  });

  test('access granted: feed loads normally when permission is present', async ({ page }) => {
    // Default state — permission is granted, no extra setup required.
    await page.goto('/');
    await waitForStatuses(page, 3);

    await expect(page.locator('li.status')).toHaveCount(3);
  });

  test('admin bypass: feed loads even when viewGeneralWall is revoked for non-admins', async ({
    request,
    page,
  }) => {
    // Revoke the permission first, then activate admin mode.
    await setupNoViewGeneralWall(request);
    await setupAdminScenario(request);
    await page.goto('/');
    await waitForStatuses(page, 3);

    // Admin users bypass the permission check — all 3 statuses must appear.
    await expect(page.locator('li.status')).toHaveCount(3);
  });
});


// ── Permission: profile_view ──────────────────────────────────────────────────
//
// The E2E test suite has no browser page for profile walls, so these scenarios
// exercise the mock API directly via APIRequestContext. They verify that the
// server enforces the gate at the HTTP level: 403 when denied, 200 when allowed.

test.describe('Wall - profile_view Permission', () => {
  const profileUrl = 'http://api:8000/?action=breezeStatus&sa=profile';

  test('access denied: profile endpoint returns 403 when permission is not granted', async ({
    request,
  }) => {
    await setupNoProfileView(request);

    const response = await request.get(profileUrl);

    expect(response.status()).toBe(403);
  });

  test('access granted: profile endpoint returns 200 when permission is present', async ({
    request,
  }) => {
    // Default state — permission is granted, no extra setup required.
    const response = await request.get(profileUrl);

    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.content.data).toBeDefined();
  });

  test('admin bypass: profile endpoint returns 200 even when profile_view is revoked for non-admins', async ({
    request,
  }) => {
    // Revoke the permission first, then activate admin mode.
    await setupNoProfileView(request);
    await setupAdminScenario(request);

    const response = await request.get(profileUrl);

    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.content.data).toBeDefined();
  });
});
