import { test, expect, type APIRequestContext } from '@playwright/test';

/**
 * P0 security regression coverage. Drives the mock API directly (no browser)
 * because both reported bugs lived in the HTTP authorization path.
 *
 * Run alone: npx playwright test e2e/tests/authorization.spec.ts
 *            npx playwright test --grep @authorization
 *
 * Authorization scenario (see handleAuthScenario in e2e/api/router.php):
 *   members 1 (A), 2 (B), 3 (C)
 *   status 1-3: wall 1, author 1      comments 100/200/300: author 1
 *   status 20:  wall 2, author 2      comment 400: on status 20, author 2
 *                                     comment 401: on status 1, author 2
 */

const API = process.env.API_URL || 'http://api:8000';
const A = 1;
const B = 2;
const C = 3;

type Snapshot = {
  statuses: { id: number; wall_id: number; user_id: number }[];
  comments: { id: number; status_id: number; user_id: number }[];
  likes: { id_member: number; content_type: string; content_id: number }[];
};

async function get(request: APIRequestContext, query: string) {
  const response = await request.get(`${API}/?${query}`);
  expect(response.ok(), `GET ${query}: ${await response.text()}`).toBeTruthy();
  return response;
}

async function setViewer(request: APIRequestContext, id: number, granted?: string[]) {
  const grantedParam = granted === undefined ? '' : `&granted=${granted.join(',')}`;
  await get(request, `action=setViewer&id=${id}${grantedParam}`);
}

async function snapshot(request: APIRequestContext): Promise<Snapshot> {
  return (await (await get(request, 'action=inspect')).json()).content;
}

async function post(request: APIRequestContext, action: string, sa: string, data: object) {
  return request.post(`${API}/?action=${action}&sa=${sa}`, { data: { data } });
}

test.describe('@authorization Authorization regressions', () => {
  test.beforeEach(async ({ request }) => {
    await get(request, 'action=authScenario');
  });

  test.describe('Delete is authorized from the persisted row', () => {
    test.beforeEach(async ({ request }) => {
      await setViewer(request, A, ['deleteOwnStatus', 'deleteOwnComments']);
    });

    test("member cannot delete another member's status, even echoing own user_id", async ({
      request,
    }) => {
      const response = await post(request, 'breezeStatus', 'deleteStatus', { id: 20, user_id: A });

      expect(response.status()).toBe(403);
      expect((await snapshot(request)).statuses.map((s) => s.id)).toContain(20);
    });

    test("member cannot delete another member's comment, even echoing own user_id", async ({
      request,
    }) => {
      const onOtherWall = await post(request, 'breezeComment', 'deleteComment', {
        id: 400,
        user_id: A,
      });
      // Comment 401 sits on A's wall but A holds no deleteProfileComments.
      const onOwnWall = await post(request, 'breezeComment', 'deleteComment', {
        id: 401,
        user_id: A,
      });

      expect(onOtherWall.status()).toBe(403);
      expect(onOwnWall.status()).toBe(403);
      const ids = (await snapshot(request)).comments.map((c) => c.id);
      expect(ids).toContain(400);
      expect(ids).toContain(401);
    });

    test('member can still delete own status and own comment', async ({ request }) => {
      const status = await post(request, 'breezeStatus', 'deleteStatus', { id: 1 });
      const comment = await post(request, 'breezeComment', 'deleteComment', { id: 200 });

      expect(status.status()).toBe(200);
      expect(comment.status()).toBe(200);
      const after = await snapshot(request);
      expect(after.statuses.map((s) => s.id)).not.toContain(1);
      expect(after.comments.map((c) => c.id)).not.toContain(200);
    });

    test('item carries a server-resolved canDelete flag', async ({ request }) => {
      const body = await (await get(request, 'action=breezeStatus&sa=single&id=20')).json();
      const own = await (await get(request, 'action=breezeStatus&sa=single&id=1')).json();

      expect(body.content.data[0].canDelete).toBe(false);
      expect(own.content.data[0].canDelete).toBe(true);
    });
  });

  test.describe('Poster / liker cannot be spoofed', () => {
    test('postStatus with user_id of another member is rejected', async ({ request }) => {
      const before = await snapshot(request);

      const response = await post(request, 'breezeStatus', 'postStatus', {
        wall_id: A,
        user_id: B,
        body: 'spoofed status',
      });

      expect(response.status()).toBeGreaterThanOrEqual(400);
      expect(response.status()).toBeLessThan(500);
      const after = await snapshot(request);
      expect(after.statuses).toEqual(before.statuses);
    });

    test('postComment with user_id of another member is rejected', async ({ request }) => {
      const before = await snapshot(request);

      const response = await post(request, 'breezeComment', 'postComment', {
        status_id: 1,
        user_id: B,
        body: 'spoofed comment',
      });

      expect(response.status()).toBeGreaterThanOrEqual(400);
      expect(response.status()).toBeLessThan(500);
      const after = await snapshot(request);
      expect(after.comments).toEqual(before.comments);
    });

    test('like with id_member of another member is rejected', async ({ request }) => {
      const response = await post(request, 'breezeLike', 'like', {
        content_id: 1,
        content_type: 'br_sta',
        id_member: B,
      });

      expect(response.status()).toBeGreaterThanOrEqual(400);
      expect(response.status()).toBeLessThan(500);
      expect((await snapshot(request)).likes).toEqual([]);
    });

    test('like on non-existent content is rejected', async ({ request }) => {
      const response = await post(request, 'breezeLike', 'like', {
        content_id: 9999,
        content_type: 'br_sta',
        id_member: A,
      });

      expect(response.status()).toBe(404);
      expect((await snapshot(request)).likes).toEqual([]);
    });

    test('legitimate post and like still succeed', async ({ request }) => {
      const status = await post(request, 'breezeStatus', 'postStatus', {
        wall_id: A,
        user_id: A,
        body: 'legit status',
      });
      const like = await post(request, 'breezeLike', 'like', {
        content_id: 1,
        content_type: 'br_sta',
        id_member: A,
      });

      expect(status.status()).toBe(201);
      expect(like.status()).toBe(201);
    });
  });

  test.describe('Posting requires service-layer permission', () => {
    test('member without postStatus cannot post on another wall', async ({ request }) => {
      await setViewer(request, A, []);
      const before = await snapshot(request);

      const response = await post(request, 'breezeStatus', 'postStatus', {
        wall_id: B,
        user_id: A,
        body: 'no permission',
      });

      expect(response.status()).toBe(403);
      expect((await snapshot(request)).statuses).toEqual(before.statuses);
    });

    test('member without postComments cannot comment on another wall', async ({ request }) => {
      await setViewer(request, A, []);
      const before = await snapshot(request);

      const response = await post(request, 'breezeComment', 'postComment', {
        status_id: 20,
        user_id: A,
        body: 'no permission',
      });

      expect(response.status()).toBe(403);
      expect((await snapshot(request)).comments).toEqual(before.comments);
    });
  });

  test.describe('Wall-enabled and block-list gates on the JSON API', () => {
    const readPaths = [
      'action=breezeStatus&sa=profile&wall_id=2',
      'action=breezeStatus&sa=total&wall_id=2',
      'action=breezeStatus&sa=single&id=20',
    ];

    test('disabled wall is unreadable (profile, total, single)', async ({ request }) => {
      await get(request, `action=disableWall&id=${B}`);

      for (const path of readPaths) {
        const response = await request.get(`${API}/?${path}`);
        expect(response.status(), path).toBe(404);
      }
    });

    test('disabled wall is not writable', async ({ request }) => {
      await get(request, `action=disableWall&id=${B}`);
      const before = await snapshot(request);

      const status = await post(request, 'breezeStatus', 'postStatus', {
        wall_id: B,
        user_id: A,
        body: 'into a disabled wall',
      });
      const comment = await post(request, 'breezeComment', 'postComment', {
        status_id: 20,
        user_id: A,
        body: 'into a disabled wall',
      });

      expect(status.status()).toBe(403);
      expect(comment.status()).toBe(403);
      const after = await snapshot(request);
      expect(after.statuses).toEqual(before.statuses);
      expect(after.comments).toEqual(before.comments);
    });

    test('wall of a member who blocked the viewer is unreadable', async ({ request }) => {
      await get(request, `action=blockMember&by=${B}&target=${A}`);

      for (const path of readPaths) {
        const response = await request.get(`${API}/?${path}`);
        expect(response.status(), path).toBe(404);
      }
    });

    test('wall of a member the viewer blocked is unreadable', async ({ request }) => {
      await get(request, `action=blockMember&by=${A}&target=${B}`);

      for (const path of readPaths) {
        const response = await request.get(`${API}/?${path}`);
        expect(response.status(), path).toBe(404);
      }
    });

    test('unaffected walls stay readable', async ({ request }) => {
      await get(request, `action=disableWall&id=${B}`);

      const response = await request.get(`${API}/?action=breezeStatus&sa=profile&wall_id=${A}`);

      expect(response.status()).toBe(200);
    });
  });

  test.describe('Moderators are not blanket-denied', () => {
    test.beforeEach(async ({ request }) => {
      await setViewer(request, C, ['deleteStatus', 'deleteComments']);
    });

    test("moderator with deleteStatus deletes another member's status", async ({ request }) => {
      const response = await post(request, 'breezeStatus', 'deleteStatus', { id: 20 });

      expect(response.status()).toBe(200);
      expect((await snapshot(request)).statuses.map((s) => s.id)).not.toContain(20);
    });

    test("moderator with deleteComments deletes another member's comment", async ({
      request,
    }) => {
      const response = await post(request, 'breezeComment', 'deleteComment', { id: 401 });

      expect(response.status()).toBe(200);
      expect((await snapshot(request)).comments.map((c) => c.id)).not.toContain(401);
    });

    test('moderator sees canDelete on foreign content', async ({ request }) => {
      const body = await (await get(request, 'action=breezeStatus&sa=single&id=20')).json();

      expect(body.content.data[0].canDelete).toBe(true);
    });
  });
});
