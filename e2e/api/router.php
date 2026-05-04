<?php

declare(strict_types=1);

// ── Response helpers ──────────────────────────────────────────────────

function respond(array $content, string $message = '', int $code = 200): void
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

function respondEmpty(string $message, int $code = 204): void
{
    global $token;

    http_response_code($code);
    echo json_encode([
        'content' => [],
        'message' => $message,
        'token' => $token,
    ]);
    exit;
}

function getPostData(): array
{
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true) ?? [];

    return $decoded['data'] ?? $decoded;
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

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM smf_user_likes WHERE content_id = ? AND content_type = ? AND id_member = 1');
    $stmt->execute([$contentId, $type]);
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
        'canLike' => true,
    ];
}

function fetchCommentsForStatus(int $statusId, PDO $pdo): array
{
    global $userData;

    $stmt = $pdo->prepare('SELECT * FROM smf_breeze_comments WHERE status_id = ? ORDER BY id ASC');
    $stmt->execute([$statusId]);

    $comments = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $commentId = (int) $row['id'];
        $comments[] = [
            'id' => $commentId,
            'status_id' => $statusId,
            'user_id' => (int) $row['user_id'],
            'likes' => (int) $row['likes'],
            'body' => $row['body'],
            'likesInfo' => buildLikeInfo($commentId, 'brz_comment', $pdo),
            'created_at' => $row['created_at'],
            'userData' => $userData,
            'isNew' => false,
        ];
    }

    return $comments;
}

function fetchStatuses(PDO $pdo, ?int $statusId = null): array
{
    global $userData;

    $sql = 'SELECT * FROM smf_breeze_status';
    $params = [];
    if ($statusId !== null) {
        $sql .= ' WHERE id = ?';
        $params[] = $statusId;
    }
    $sql .= ' ORDER BY created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $statuses = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = (int) $row['id'];
        $statuses[] = [
            'id' => $id,
            'wall_id' => (int) $row['wall_id'],
            'user_id' => (int) $row['user_id'],
            'likes' => (int) $row['likes'],
            'body' => $row['body'],
            'created_at' => formatDate((int) $row['created_at']),
            'likesInfo' => buildLikeInfo($id, 'brz_status', $pdo),
            'comments' => fetchCommentsForStatus($id, $pdo),
            'userData' => $userData,
            'isNew' => false,
        ];
    }

    return $statuses;
}

// ── Dispatch ─────────────────────────────────────────────────────────

match ($action) {
    'breezeStatus' => handleStatus($subAction),
    'breezeComment' => handleComment($subAction),
    'breezeLike' => handleLike($subAction),
    'reset' => handleReset(),
    default => respond([], 'Unknown action', 404),
};

// ── Handlers ──────────────────────────────────────────────────────────

function handleStatus(string $subAction): void
{
    global $pdo, $permissions;

    match ($subAction) {
        'profile', 'wall' => respond([
            'data' => fetchStatuses($pdo),
            'permissions' => $permissions,
            'pagination' => [
                'nextCursor' => null,
                'hasMore' => false,
            ],
            'total' => count(fetchStatuses($pdo)),
        ]),
        'single' => (function () use ($pdo, $permissions) {
            $id = (int) ($_GET['id'] ?? 0);
            if ($id === 0) {
                respond([], 'No status ID provided', 400);
            }
            $statuses = fetchStatuses($pdo, $id);
            if ($statuses === []) {
                respond([], 'Status not found', 404);
            }
            respond([
                'data' => $statuses,
                'permissions' => $permissions,
                'pagination' => [
                    'nextCursor' => null,
                    'hasMore' => false,
                ],
                'total' => 1,
            ]);
        })(),
        'postStatus' => handlePostStatus(),
        'deleteStatus' => handleDeleteStatus(),
        'total' => respond(['total' => count(fetchStatuses($pdo))]),
        default => respond([], 'Unknown sub-action', 404),
    };
}

function handlePostStatus(): void
{
    global $pdo, $userData;

    $data = getPostData();
    $wallId = (int) ($data['wall_id'] ?? 1);
    $userId = (int) ($data['user_id'] ?? 1);
    $body = $data['body'] ?? 'New status';

    $stmt = $pdo->prepare('INSERT INTO smf_breeze_status (wall_id, user_id, created_at, body) VALUES (?, ?, ?, ?)');
    $stmt->execute([$wallId, $userId, time(), $body]);
    $newId = (int) $pdo->lastInsertId();

    $newStatus = [
        'id' => $newId,
        'wall_id' => $wallId,
        'user_id' => $userId,
        'likes' => 0,
        'body' => $body,
        'created_at' => formatDate(time()),
        'likesInfo' => buildLikeInfo($newId, 'brz_status', $pdo),
        'comments' => [],
        'userData' => $userData,
        'isNew' => true,
    ];

    respond([$newId => $newStatus], 'Status posted', 201);
}

function handleDeleteStatus(): void
{
    global $pdo;

    $data = getPostData();
    $id = (int) ($data['id'] ?? 0);

    // Delete associated comments first, then the status
    $pdo->prepare('DELETE FROM smf_breeze_comments WHERE status_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM smf_user_likes WHERE content_id = ? AND content_type = "brz_status"')->execute([$id]);
    $pdo->prepare('DELETE FROM smf_breeze_status WHERE id = ?')->execute([$id]);

    respondEmpty('Status deleted', 200);
}

function handleComment(string $subAction): void
{
    global $pdo, $userData;

    match ($subAction) {
        'postComment' => (function () use ($pdo, $userData) {
            $data = getPostData();
            $statusId = (int) ($data['status_id'] ?? 1);
            $userId = (int) ($data['user_id'] ?? 1);
            $body = $data['body'] ?? 'New comment';

            $stmt = $pdo->prepare('INSERT INTO smf_breeze_comments (status_id, user_id, likes, body, created_at) VALUES (?, ?, 0, ?, ?)');
            $stmt->execute([$statusId, $userId, $body, formatDate(time())]);
            $newId = (int) $pdo->lastInsertId();

            $newComment = [
                'id' => $newId,
                'status_id' => $statusId,
                'user_id' => $userId,
                'likes' => 0,
                'body' => $body,
                'likesInfo' => buildLikeInfo($newId, 'brz_comment', $pdo),
                'created_at' => formatDate(time()),
                'userData' => $userData,
                'isNew' => true,
            ];

            respond([$newId => $newComment], 'Comment posted', 201);
        })(),
        'deleteComment' => (function () use ($pdo) {
            $data = getPostData();
            $id = (int) ($data['id'] ?? 0);

            $pdo->prepare('DELETE FROM smf_user_likes WHERE content_id = ? AND content_type = "brz_comment"')->execute([$id]);
            $pdo->prepare('DELETE FROM smf_breeze_comments WHERE id = ?')->execute([$id]);

            respondEmpty('Comment deleted', 200);
        })(),
        default => respond([], 'Unknown sub-action', 404),
    };
}

function handleLike(string $subAction): void
{
    global $pdo, $userData;

    match ($subAction) {
        'like' => (function () use ($pdo, $userData) {
            $data = getPostData();
            $contentId = (int) ($data['content_id'] ?? 0);
            $contentType = $data['content_type'] ?? 'brz_status';
            $userId = (int) ($data['id_member'] ?? 1);

            if ($contentId === 0) {
                respond([], 'No content ID provided', 400);
            }

            // Toggle like
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM smf_user_likes WHERE content_id = ? AND content_type = ? AND id_member = ?');
            $stmt->execute([$contentId, $contentType, $userId]);
            $alreadyLiked = (int) $stmt->fetchColumn() > 0;

            if ($alreadyLiked) {
                $pdo->prepare('DELETE FROM smf_user_likes WHERE content_id = ? AND content_type = ? AND id_member = ?')
                    ->execute([$contentId, $contentType, $userId]);
            } else {
                $pdo->prepare('INSERT INTO smf_user_likes (id_member, content_type, content_id, like_time) VALUES (?, ?, ?, ?)')
                    ->execute([$userId, $contentType, $contentId, time()]);
            }

            respond(buildLikeInfo($contentId, $contentType, $pdo), 'Like toggled', 201);
        })(),
        default => respond([], 'Unknown sub-action', 404),
    };
}

function handleReset(): void
{
    global $pdo;

    $pdo->exec('TRUNCATE TABLE smf_breeze_status');
    $pdo->exec('TRUNCATE TABLE smf_breeze_comments');
    $pdo->exec('TRUNCATE TABLE smf_user_likes');
    $pdo->exec('TRUNCATE TABLE smf_members');
    $pdo->exec("INSERT INTO smf_members (id_member, member_name, real_name) VALUES (1, 'testuser', 'Test User')");

    $now = time();
    $insertStatus = $pdo->prepare('INSERT INTO smf_breeze_status (id, wall_id, user_id, created_at, body, likes) VALUES (?, ?, ?, ?, ?, ?)');
    $insertStatus->execute([1, 1, 1, $now - 3600, 'This is mock status #1 for E2E testing.', 0]);
    $insertStatus->execute([2, 1, 1, $now - 7200, 'This is mock status #2 for E2E testing.', 0]);
    $insertStatus->execute([3, 1, 1, $now - 10800, 'This is mock status #3 for E2E testing.', 0]);

    $insertComment = $pdo->prepare('INSERT INTO smf_breeze_comments (id, status_id, user_id, likes, body, created_at) VALUES (?, ?, ?, ?, ?, ?)');
    $insertComment->execute([100, 1, 1, 0, 'A comment on status #1', date('M d, Y h:i A', $now - 3600)]);
    $insertComment->execute([200, 2, 1, 0, 'A comment on status #2', date('M d, Y h:i A', $now - 7200)]);
    $insertComment->execute([300, 3, 1, 0, 'A comment on status #3', date('M d, Y h:i A', $now - 10800)]);

    respond(['reset' => true], 'Database reset', 200);
}
