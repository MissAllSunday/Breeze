<?php

declare(strict_types=1);

// Path to the per-test state file. Written by setup actions, cleared by
// handleReset(). Default (file absent) = viewer 1 with every permission granted.
define('PERM_STATE_FILE', '/tmp/breeze_e2e_perm_state.json');

// Mirrors PermissionsEnum::ALL_PERMISSIONS plus the SMF forum permissions the
// API surfaces under `permissions.Forum`.
const ALL_BREEZE_PERMISSIONS = [
	'deleteStatus', 'deleteOwnStatus', 'deleteProfileStatus',
	'deleteComments', 'deleteOwnComments', 'deleteProfileComments',
	'postStatus', 'postComments', 'viewGeneralWall',
	'likes_like', 'admin_forum', 'profile_view',
];

// LikesEnum values. The legacy `brz_*` spellings are accepted as aliases so
// older fixtures keep working; everything is normalised to the canonical value.
const LIKE_TYPE_ALIASES = [
	'br_sta' => 'br_sta',
	'br_com' => 'br_com',
	'brz_status' => 'br_sta',
	'brz_comment' => 'br_com',
];

const DEFAULT_PERM_STATE = [
	'viewerId' => 1,
	// null = every permission granted (the historical default).
	'granted' => null,
	'viewGeneralWall' => true,
	'profileView' => true,
	'isAdmin' => false,
	// Member ids whose wall setting is switched off (UserSettingsEntity::getWall() === 0).
	'disabledWalls' => [],
];

// ── State helpers ─────────────────────────────────────────────────────

function loadPermState(): array
{
	if (!file_exists(PERM_STATE_FILE)) {
		return DEFAULT_PERM_STATE;
	}

	$data = json_decode((string) file_get_contents(PERM_STATE_FILE), true) ?? [];

	return array_merge(DEFAULT_PERM_STATE, $data);
}

function savePermState(array $state): void
{
	file_put_contents(PERM_STATE_FILE, json_encode(array_merge(DEFAULT_PERM_STATE, $state)));
}

function isAdminE2E(): bool
{
	return loadPermState()['isAdmin'] === true;
}

/** The member id the mock API treats as the logged-in session user. */
function viewerId(): int
{
	return (int) loadPermState()['viewerId'];
}

function hasPermission(string $permissionName): bool
{
	if (isAdminE2E()) {
		return true;
	}

	$granted = loadPermState()['granted'];

	if ($granted === null) {
		return true;
	}

	return in_array($permissionName, $granted, true);
}

function canViewGeneralWall(): bool
{
	if (isAdminE2E()) {
		return true;
	}

	$state = loadPermState();

	if ($state['granted'] !== null) {
		return in_array('viewGeneralWall', $state['granted'], true);
	}

	return $state['viewGeneralWall'] === true;
}

function canViewProfile(): bool
{
	if (isAdminE2E()) {
		return true;
	}

	$state = loadPermState();

	if ($state['granted'] !== null) {
		return in_array('profile_view', $state['granted'], true);
	}

	return $state['profileView'] === true;
}

// ── Authorization (mirrors PermissionsService) ────────────────────────

/**
 * PermissionsEnum::getDeletePermission() — `delete{Own|Profile}{Status|Comments}`.
 *
 * @param string $type 'Status' | 'Comments'
 * @param string $subType '' | 'own' | 'profile'
 */
function deletePermissionName(string $type, string $subType = ''): string
{
	return 'delete' . ucfirst($subType) . ucfirst($type);
}

/**
 * PermissionsService::canDelete() — resolves from DB-derived ids only.
 *
 * @param string $type 'Status' | 'Comments'
 */
function canDeleteItem(string $type, int $authorId, int $wallOwnerId): bool
{
	$viewer = viewerId();

	if ($viewer === 0) {
		return false;
	}

	if ($authorId !== 0
		&& $authorId === $viewer
		&& hasPermission(deletePermissionName($type, 'own'))) {
		return true;
	}

	if ($wallOwnerId !== 0
		&& $wallOwnerId === $viewer
		&& hasPermission(deletePermissionName($type, 'profile'))) {
		return true;
	}

	return hasPermission(deletePermissionName($type));
}

/**
 * PermissionsService::canDeleteAny() — the wall-level "may delete anything
 * here" flag emitted under `permissions.*.delete`.
 *
 * @param string $type 'Status' | 'Comments'
 */
function canDeleteAny(string $type, int $wallOwnerId): bool
{
	$isProfileOwner = $wallOwnerId !== 0 && $wallOwnerId === viewerId();

	if ($isProfileOwner && hasPermission(deletePermissionName($type, 'profile'))) {
		return true;
	}

	return hasPermission(deletePermissionName($type));
}

/**
 * PermissionsService::canPost().
 *
 * @param string $type 'Status' | 'Comments'
 */
function canPostItem(string $type, int $wallOwnerId): bool
{
	if ($wallOwnerId !== 0 && $wallOwnerId === viewerId()) {
		return true;
	}

	return hasPermission($type === 'Status' ? 'postStatus' : 'postComments');
}

/** PermissionsService::permissions() payload for one wall. */
function permissionsPayload(int $wallOwnerId): array
{
	return [
		'Status' => [
			'edit' => hasPermission('postStatus'),
			'delete' => canDeleteAny('Status', $wallOwnerId),
			'post' => canPostItem('Status', $wallOwnerId),
		],
		'Comments' => [
			'edit' => hasPermission('postComments'),
			'delete' => canDeleteAny('Comments', $wallOwnerId),
			'post' => canPostItem('Comments', $wallOwnerId),
		],
		'isEnable' => ['enableLikes' => true],
		'Forum' => [
			'likesLike' => hasPermission('likes_like'),
			'adminForum' => hasPermission('admin_forum'),
			'profileView' => hasPermission('profile_view'),
		],
	];
}

// ── Wall access (mirrors WallVisibilityService::canAccessWall) ────────

function getBlockList(PDO $pdo, int $memberId): array
{
	$stmt = $pdo->prepare('SELECT pm_ignore_list FROM smf_members WHERE id_member = ?');
	$stmt->execute([$memberId]);
	$list = (string) $stmt->fetchColumn();

	return array_values(array_filter(array_map('intval', explode(',', $list))));
}

function isBlockedEitherWay(PDO $pdo, int $a, int $b): bool
{
	if ($a === 0 || $b === 0 || $a === $b) {
		return false;
	}

	return in_array($b, getBlockList($pdo, $a), true)
		|| in_array($a, getBlockList($pdo, $b), true);
}

function canAccessWall(PDO $pdo, int $wallOwnerId): bool
{
	// The general wall has no single owner: no wall-enabled gate.
	if ($wallOwnerId === 0) {
		return true;
	}

	if (in_array($wallOwnerId, loadPermState()['disabledWalls'], true)) {
		return false;
	}

	return !isBlockedEitherWay($pdo, $wallOwnerId, viewerId());
}

// ── Response helpers ──────────────────────────────────────────────────

function respond(array $content, string $message = '', int $code = 200): never
{
	global $token;

	http_response_code($code);
	echo json_encode([
		'content' => $content,
		'message' => $message,
		'token' => $token,
	]);
	exit;
}

function respondEmpty(string $message, int $code = 204): never
{
	respond([], $message, $code);
}

function getPostData(): array
{
	$decoded = json_decode((string) file_get_contents('php://input'), true) ?? [];

	return $decoded['data'] ?? $decoded;
}

/** Require integer fields; any missing/zero field is a 400 (Data::compare/isInt). */
function requireInts(array $data, array $fields): array
{
	$values = [];

	foreach ($fields as $field) {
		if (!isset($data[$field]) || !is_numeric($data[$field])) {
			respond([], 'error_empty_values', 400);
		}

		$values[$field] = (int) $data[$field];
	}

	return $values;
}

// ── Data helpers ─────────────────────────────────────────────────────

function formatDate(int $timestamp): string
{
	return date('M d, Y h:i A', $timestamp);
}

function buildLikeInfo(int $contentId, string $type, PDO $pdo): array
{
	global $userData;

	$stmt = $pdo->prepare('SELECT COUNT(*) FROM smf_user_likes WHERE content_id = ? AND content_type = ?');
	$stmt->execute([$contentId, $type]);
	$count = (int) $stmt->fetchColumn();

	$stmt = $pdo->prepare('SELECT COUNT(*) FROM smf_user_likes WHERE content_id = ? AND content_type = ? AND id_member = ?');
	$stmt->execute([$contentId, $type, viewerId()]);
	$alreadyLiked = (int) $stmt->fetchColumn() > 0;

	$likes = [];
	if ($count > 0) {
		$stmt = $pdo->prepare('SELECT id_member, like_time FROM smf_user_likes WHERE content_id = ? AND content_type = ? ORDER BY like_time DESC');
		$stmt->execute([$contentId, $type]);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$likes[$row['id_member']] = [
				'userData' => $userData,
				'likeTime' => formatDate((int) $row['like_time']),
			];
		}
	}

	$text = $count === 0 ? '' : "{$count} " . ($count === 1 ? 'person likes this' : 'people like this');

	return [
		'text' => $text,
		'href' => '',
		'likes' => $likes,
		'contentId' => $contentId,
		'count' => $count,
		'type' => $type,
		'alreadyLiked' => $alreadyLiked,
		'canLike' => hasPermission('likes_like'),
	];
}

function fetchStatusRow(PDO $pdo, int $id): ?array
{
	$stmt = $pdo->prepare('SELECT * FROM smf_breeze_status WHERE id = ?');
	$stmt->execute([$id]);

	return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function fetchCommentRow(PDO $pdo, int $id): ?array
{
	$stmt = $pdo->prepare('SELECT * FROM smf_breeze_comments WHERE id = ?');
	$stmt->execute([$id]);

	return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function fetchCommentsForStatus(int $statusId, int $wallId, PDO $pdo): array
{
	global $userData;

	$stmt = $pdo->prepare('SELECT * FROM smf_breeze_comments WHERE status_id = ? ORDER BY id ASC');
	$stmt->execute([$statusId]);

	$comments = [];
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$authorId = (int) $row['user_id'];

		if (isBlockedEitherWay($pdo, $authorId, viewerId())) {
			continue;
		}

		$commentId = (int) $row['id'];
		$comments[] = [
			'id' => $commentId,
			'status_id' => $statusId,
			'user_id' => $authorId,
			'likes' => (int) $row['likes'],
			'body' => $row['body'],
			'likesInfo' => buildLikeInfo($commentId, 'br_com', $pdo),
			'canDelete' => canDeleteItem('Comments', $authorId, $wallId),
			'created_at' => $row['created_at'],
			'userData' => $userData,
			'isNew' => false,
		];
	}

	return $comments;
}

function buildStatus(array $row, PDO $pdo, bool $isNew = false): array
{
	global $userData;

	$id = (int) $row['id'];
	$wallId = (int) $row['wall_id'];
	$authorId = (int) $row['user_id'];

	return [
		'id' => $id,
		'wall_id' => $wallId,
		'user_id' => $authorId,
		'likes' => (int) $row['likes'],
		'body' => $row['body'],
		'created_at' => formatDate((int) $row['created_at']),
		'likesInfo' => buildLikeInfo($id, 'br_sta', $pdo),
		'canDelete' => canDeleteItem('Status', $authorId, $wallId),
		'comments' => fetchCommentsForStatus($id, $wallId, $pdo),
		'userData' => $userData,
		'isNew' => $isNew,
	];
}

/** Statuses visible to the viewer; authors blocked in either direction are dropped. */
function fetchStatuses(PDO $pdo, ?int $statusId = null, ?int $wallId = null): array
{
	$conditions = [];
	$params = [];

	if ($statusId !== null) {
		$conditions[] = 'id = ?';
		$params[] = $statusId;
	}

	if ($wallId !== null && $wallId > 0) {
		$conditions[] = 'wall_id = ?';
		$params[] = $wallId;
	}

	$sql = 'SELECT * FROM smf_breeze_status';
	if ($conditions !== []) {
		$sql .= ' WHERE ' . implode(' AND ', $conditions);
	}
	$sql .= ' ORDER BY created_at DESC';

	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);

	$statuses = [];
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		if (isBlockedEitherWay($pdo, (int) $row['user_id'], viewerId())) {
			continue;
		}

		$statuses[] = buildStatus($row, $pdo);
	}

	return $statuses;
}

function statusListResponse(array $statuses, int $wallOwnerId): never
{
	respond([
		'data' => $statuses,
		'permissions' => permissionsPayload($wallOwnerId),
		'pagination' => ['nextCursor' => null, 'hasMore' => false],
		'total' => count($statuses),
	]);
}

// ── Dispatch ─────────────────────────────────────────────────────────

match ($action) {
	'breezeStatus' => handleStatus($subAction),
	'breezeComment' => handleComment($subAction),
	'breezeLike' => handleLike($subAction),
	'reset' => handleReset(),
	'blockScenario' => handleBlockScenario(),
	'noViewGeneralWall' => handleNoViewGeneralWall(),
	'noProfileView' => handleNoProfileView(),
	'adminScenario' => handleAdminScenario(),
	'authScenario' => handleAuthScenario(),
	'setViewer' => handleSetViewer(),
	'disableWall' => handleDisableWall(),
	'blockMember' => handleBlockMember(),
	'inspect' => handleInspect(),
	default => respond([], 'Unknown action', 404),
};

// ── Handlers: status ──────────────────────────────────────────────────

function handleStatus(string $subAction): void
{
	global $pdo;

	switch ($subAction) {
		case 'wall':
			if (!canViewGeneralWall()) {
				respond([], 'Access denied: viewGeneralWall permission required', 403);
			}

			statusListResponse(fetchStatuses($pdo), 0);

		// no break: statusListResponse() exits
		case 'profile':
			if (!canViewProfile()) {
				respond([], 'Access denied: profile_view permission required', 403);
			}

			$wallId = (int) ($_GET['wall_id'] ?? 0);

			// Same 404 shape as a missing status so existence is not leaked.
			if (!canAccessWall($pdo, $wallId)) {
				respond([], 'error_no_status', 404);
			}

			statusListResponse(fetchStatuses($pdo, null, $wallId), $wallId);

		case 'total':
			$wallId = (int) ($_GET['wall_id'] ?? 0);

			if (!canAccessWall($pdo, $wallId)) {
				respond([], 'error_no_status', 404);
			}

			respond(['total' => count(fetchStatuses($pdo, null, $wallId))]);

		case 'single':
			$id = (int) ($_GET['id'] ?? 0);
			if ($id === 0) {
				respond([], 'error_no_status', 400);
			}

			$row = fetchStatusRow($pdo, $id);
			if ($row === null || !canAccessWall($pdo, (int) $row['wall_id'])) {
				respond([], 'error_no_status', 404);
			}

			$statuses = fetchStatuses($pdo, $id);
			if ($statuses === []) {
				respond([], 'error_no_status', 404);
			}

			statusListResponse($statuses, (int) $row['wall_id']);

		case 'postStatus':
			handlePostStatus();

		case 'deleteStatus':
			handleDeleteStatus();

		default:
			respond([], 'Unknown sub-action', 404);
	}
}

function handlePostStatus(): never
{
	global $pdo;

	$data = getPostData();
	$ids = requireInts($data, ['wall_id', 'user_id']);
	$body = trim((string) ($data['body'] ?? ''));

	if ($body === '') {
		respond([], 'error_empty_values', 400);
	}

	if (!canAccessWall($pdo, $ids['wall_id']) || !canPostItem('Status', $ids['wall_id'])) {
		respond([], 'postStatus', 403);
	}

	// User::isSameUser() — the poster must be the session user.
	if ($ids['user_id'] !== viewerId()) {
		respond([], 'error_wrong_values', 403);
	}

	$stmt = $pdo->prepare('INSERT INTO smf_breeze_status (wall_id, user_id, created_at, body) VALUES (?, ?, ?, ?)');
	$stmt->execute([$ids['wall_id'], $ids['user_id'], time(), $body]);

	$row = fetchStatusRow($pdo, (int) $pdo->lastInsertId());

	respond([buildStatus($row, $pdo, true)], 'Status posted', 201);
}

function handleDeleteStatus(): never
{
	global $pdo;

	$id = requireInts(getPostData(), ['id'])['id'];
	$row = fetchStatusRow($pdo, $id);

	if ($row === null) {
		respond([], 'error_no_status', 404);
	}

	// Authorize from the persisted row only; the payload's user_id is ignored.
	if (!canDeleteItem('Status', (int) $row['user_id'], (int) $row['wall_id'])) {
		respond([], 'deleteStatus', 403);
	}

	$pdo->prepare('DELETE FROM smf_breeze_comments WHERE status_id = ?')->execute([$id]);
	$pdo->prepare('DELETE FROM smf_user_likes WHERE content_id = ? AND content_type = ?')->execute([$id, 'br_sta']);
	$pdo->prepare('DELETE FROM smf_breeze_status WHERE id = ?')->execute([$id]);

	respondEmpty('Status deleted', 200);
}

// ── Handlers: comment ─────────────────────────────────────────────────

function handleComment(string $subAction): void
{
	global $pdo;

	switch ($subAction) {
		case 'postComment':
			$data = getPostData();
			$ids = requireInts($data, ['status_id', 'user_id']);
			$body = trim((string) ($data['body'] ?? ''));

			if ($body === '') {
				respond([], 'error_empty_values', 400);
			}

			$status = fetchStatusRow($pdo, $ids['status_id']);
			if ($status === null) {
				respond([], 'error_no_status', 404);
			}

			$wallId = (int) $status['wall_id'];

			if (!canAccessWall($pdo, $wallId) || !canPostItem('Comments', $wallId)) {
				respond([], 'postComments', 403);
			}

			if ($ids['user_id'] !== viewerId()) {
				respond([], 'error_wrong_values', 403);
			}

			$stmt = $pdo->prepare('INSERT INTO smf_breeze_comments (status_id, user_id, likes, body, created_at) VALUES (?, ?, 0, ?, ?)');
			$stmt->execute([$ids['status_id'], $ids['user_id'], $body, formatDate(time())]);
			$newId = (int) $pdo->lastInsertId();

			$comment = fetchCommentsForStatus($ids['status_id'], $wallId, $pdo);
			$comment = array_values(array_filter($comment, static fn (array $c): bool => $c['id'] === $newId));
			$comment[0]['isNew'] = true;

			respond($comment, 'Comment posted', 201);

		case 'deleteComment':
			$id = requireInts(getPostData(), ['id'])['id'];
			$row = fetchCommentRow($pdo, $id);

			if ($row === null) {
				respond([], 'error_no_comment', 404);
			}

			$status = fetchStatusRow($pdo, (int) $row['status_id']);
			$wallId = $status === null ? 0 : (int) $status['wall_id'];

			if (!canDeleteItem('Comments', (int) $row['user_id'], $wallId)) {
				respond([], 'deleteComments', 403);
			}

			$pdo->prepare('DELETE FROM smf_user_likes WHERE content_id = ? AND content_type = ?')->execute([$id, 'br_com']);
			$pdo->prepare('DELETE FROM smf_breeze_comments WHERE id = ?')->execute([$id]);

			respondEmpty('Comment deleted', 200);

		default:
			respond([], 'Unknown sub-action', 404);
	}
}

// ── Handlers: like ────────────────────────────────────────────────────

function handleLike(string $subAction): void
{
	global $pdo;

	if ($subAction !== 'like') {
		respond([], 'Unknown sub-action', 404);
	}

	$data = getPostData();
	$ids = requireInts($data, ['content_id', 'id_member']);
	$type = LIKE_TYPE_ALIASES[(string) ($data['content_type'] ?? '')] ?? null;

	if (!hasPermission('likes_like')) {
		respond([], 'likesLike', 403);
	}

	if ($type === null) {
		respond([], 'likesTypeInvalid', 404);
	}

	$exists = $type === 'br_sta'
		? fetchStatusRow($pdo, $ids['content_id'])
		: fetchCommentRow($pdo, $ids['content_id']);

	if ($exists === null) {
		respond([], 'error_no_data', 404);
	}

	// User::isSameUser() — the liker must be the session user.
	if ($ids['id_member'] !== viewerId()) {
		respond([], 'error_wrong_values', 403);
	}

	$params = [$ids['content_id'], $type, $ids['id_member']];
	$stmt = $pdo->prepare('SELECT COUNT(*) FROM smf_user_likes WHERE content_id = ? AND content_type = ? AND id_member = ?');
	$stmt->execute($params);

	if ((int) $stmt->fetchColumn() > 0) {
		$pdo->prepare('DELETE FROM smf_user_likes WHERE content_id = ? AND content_type = ? AND id_member = ?')->execute($params);
	} else {
		$pdo->prepare('INSERT INTO smf_user_likes (id_member, content_type, content_id, like_time) VALUES (?, ?, ?, ?)')
			->execute([$ids['id_member'], $type, $ids['content_id'], time()]);
	}

	respond(buildLikeInfo($ids['content_id'], $type, $pdo), 'Like toggled', 201);
}

// ── Fixtures & scenarios ──────────────────────────────────────────────

/**
 * Wipe all tables and seed the standard fixtures: statuses 1-3 on wall 1
 * (author 1), each with one comment by member 1.
 *
 * @param array<int, array{0:int,1:string,2:string,3?:string}> $members id, name, real name, ignore list
 */
function seedFixtures(PDO $pdo, array $members): void
{
	foreach (['smf_breeze_status', 'smf_breeze_comments', 'smf_user_likes', 'smf_members'] as $table) {
		$pdo->exec("TRUNCATE TABLE {$table}");
	}

	$insertMember = $pdo->prepare('INSERT INTO smf_members (id_member, member_name, real_name, pm_ignore_list) VALUES (?, ?, ?, ?)');
	foreach ($members as $member) {
		$insertMember->execute([$member[0], $member[1], $member[2], $member[3] ?? '']);
	}

	$now = time();
	$insertStatus = $pdo->prepare('INSERT INTO smf_breeze_status (id, wall_id, user_id, created_at, body, likes) VALUES (?, ?, ?, ?, ?, ?)');
	$insertStatus->execute([1, 1, 1, $now - 3600, 'This is mock status #1 for E2E testing.', 0]);
	$insertStatus->execute([2, 1, 1, $now - 7200, 'This is mock status #2 for E2E testing.', 0]);
	$insertStatus->execute([3, 1, 1, $now - 10800, 'This is mock status #3 for E2E testing.', 0]);

	$insertComment = $pdo->prepare('INSERT INTO smf_breeze_comments (id, status_id, user_id, likes, body, created_at) VALUES (?, ?, ?, ?, ?, ?)');
	$insertComment->execute([100, 1, 1, 0, 'A comment on status #1', formatDate($now - 3600)]);
	$insertComment->execute([200, 2, 1, 0, 'A comment on status #2', formatDate($now - 7200)]);
	$insertComment->execute([300, 3, 1, 0, 'A comment on status #3', formatDate($now - 10800)]);
}

function handleReset(): void
{
	global $pdo;

	// Clear per-test state so the next test starts clean.
	if (file_exists(PERM_STATE_FILE)) {
		unlink(PERM_STATE_FILE);
	}

	seedFixtures($pdo, [[1, 'testuser', 'Test User']]);

	respond(['reset' => true], 'Database reset', 200);
}

/**
 * Viewer 1 blocks user 2; user 2 has one status that must not appear.
 */
function handleBlockScenario(): void
{
	global $pdo;

	seedFixtures($pdo, [
		[1, 'testuser', 'Test User', '2'],
		[2, 'blockeduser', 'Blocked User'],
	]);

	$pdo->prepare('INSERT INTO smf_breeze_status (id, wall_id, user_id, created_at, body, likes) VALUES (?, ?, ?, ?, ?, ?)')
		->execute([10, 1, 2, time() - 1800, 'This post is from a blocked user and should not be visible.', 0]);

	respond(['blockScenario' => true], 'Block scenario ready', 200);
}

/**
 * Authorization scenario. Members: 1 (A), 2 (B), 3 (C).
 * On top of the standard fixtures:
 *   - status 20: wall 2, author 2 (B's own wall)
 *   - comment 400: on status 20, author 2
 *   - comment 401: on status 1 (A's wall), author 2
 * The viewer defaults to member 1; use setViewer to restrict permissions.
 */
function handleAuthScenario(): void
{
	global $pdo;

	savePermState([]);

	seedFixtures($pdo, [
		[1, 'membera', 'Member A'],
		[2, 'memberb', 'Member B'],
		[3, 'memberc', 'Member C'],
	]);

	$now = time();
	$pdo->prepare('INSERT INTO smf_breeze_status (id, wall_id, user_id, created_at, body, likes) VALUES (?, ?, ?, ?, ?, ?)')
		->execute([20, 2, 2, $now - 600, 'B status on B wall.', 0]);

	$insertComment = $pdo->prepare('INSERT INTO smf_breeze_comments (id, status_id, user_id, likes, body, created_at) VALUES (?, ?, ?, ?, ?, ?)');
	$insertComment->execute([400, 20, 2, 0, 'B comment on B status.', formatDate($now - 600)]);
	$insertComment->execute([401, 1, 2, 0, 'B comment on A status.', formatDate($now - 300)]);

	respond(['authScenario' => true], 'Authorization scenario ready', 200);
}

/**
 * ?action=setViewer&id=1&granted=deleteOwnStatus,deleteOwnComments
 * Omit `granted` to grant everything; pass it empty to grant nothing.
 */
function handleSetViewer(): void
{
	$state = loadPermState();
	$state['viewerId'] = (int) ($_GET['id'] ?? 1);
	$state['granted'] = isset($_GET['granted'])
		? array_values(array_filter(explode(',', (string) $_GET['granted'])))
		: null;
	savePermState($state);

	respond(['viewerId' => $state['viewerId'], 'granted' => $state['granted']], 'Viewer set', 200);
}

/** ?action=disableWall&id=2 */
function handleDisableWall(): void
{
	$state = loadPermState();
	$state['disabledWalls'][] = (int) ($_GET['id'] ?? 0);
	$state['disabledWalls'] = array_values(array_unique($state['disabledWalls']));
	savePermState($state);

	respond(['disabledWalls' => $state['disabledWalls']], 'Wall disabled', 200);
}

/** ?action=blockMember&by=2&target=1 — member `by` adds `target` to their block list. */
function handleBlockMember(): void
{
	global $pdo;

	$by = (int) ($_GET['by'] ?? 0);
	$target = (int) ($_GET['target'] ?? 0);

	$list = array_values(array_unique(array_merge(getBlockList($pdo, $by), [$target])));
	$pdo->prepare('UPDATE smf_members SET pm_ignore_list = ? WHERE id_member = ?')
		->execute([implode(',', $list), $by]);

	respond(['by' => $by, 'blocked' => $list], 'Member blocked', 200);
}

/** Raw DB snapshot (no viewer filtering) so specs can assert rows still exist. */
function handleInspect(): void
{
	global $pdo;

	respond([
		'statuses' => $pdo->query('SELECT id, wall_id, user_id FROM smf_breeze_status ORDER BY id')->fetchAll(),
		'comments' => $pdo->query('SELECT id, status_id, user_id FROM smf_breeze_comments ORDER BY id')->fetchAll(),
		'likes' => $pdo->query('SELECT id_member, content_type, content_id FROM smf_user_likes ORDER BY content_id')->fetchAll(),
	]);
}

// ── Handlers: permission scenarios ────────────────────────────────────

/** Revoke viewGeneralWall; cleared by handleReset(). */
function handleNoViewGeneralWall(): void
{
	$state = loadPermState();
	$state['viewGeneralWall'] = false;
	savePermState($state);
	respond(['viewGeneralWall' => false], 'viewGeneralWall permission revoked', 200);
}

/** Revoke profile_view; cleared by handleReset(). */
function handleNoProfileView(): void
{
	$state = loadPermState();
	$state['profileView'] = false;
	savePermState($state);
	respond(['profileView' => false], 'profileView permission revoked', 200);
}

/** Admin bypass: every permission check passes; cleared by handleReset(). */
function handleAdminScenario(): void
{
	$state = loadPermState();
	$state['isAdmin'] = true;
	savePermState($state);
	respond(['isAdmin' => true], 'Admin scenario active', 200);
}
