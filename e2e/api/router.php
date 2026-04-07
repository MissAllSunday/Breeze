<?php

declare(strict_types=1);

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

match ($action) {
    'breezeStatus' => handleStatus($subAction),
    'breezeComment' => handleComment($subAction),
    'breezeLike' => handleLike($subAction),
    default => respond([], 'Unknown action', 404),
};

function handleStatus(string $subAction): void
{
    global $mockStatuses, $permissions;

    match ($subAction) {
        'profile', 'wall' => respond([
            'data' => $mockStatuses,
            'permissions' => $permissions,
            'pagination' => [
                'nextCursor' => null,
                'hasMore' => false,
            ],
            'total' => count($mockStatuses),
        ]),
        'single' => respond([
            'data' => [$mockStatuses[0]],
            'permissions' => $permissions,
            'pagination' => [
                'nextCursor' => null,
                'hasMore' => false,
            ],
            'total' => 1,
        ]),
        'postStatus' => handlePostStatus(),
        'deleteStatus' => respondEmpty('Status deleted', 204),
        'total' => respond(['total' => count($mockStatuses)]),
        default => respond([], 'Unknown sub-action', 404),
    };
}

function handlePostStatus(): void
{
    global $userData, $likesInfo;

    $data = getPostData();

    $newStatus = [
        'id' => random_int(1000, 9999),
        'wall_id' => (int) ($data['wall_id'] ?? 1),
        'user_id' => (int) ($data['user_id'] ?? 1),
        'likes' => 0,
        'body' => $data['body'] ?? 'New status',
        'created_at' => date('M d, Y h:i A'),
        'likesInfo' => $likesInfo,
        'comments' => [],
        'userData' => $userData,
        'isNew' => true,
    ];

    respond($newStatus, 'Status posted', 201);
}

function handleComment(string $subAction): void
{
    global $userData, $likesInfo;

    match ($subAction) {
        'postComment' => (function () use ($userData, $likesInfo) {
            $data = getPostData();

            $newComment = [
                'id' => random_int(1000, 9999),
                'status_id' => (int) ($data['status_id'] ?? 1),
                'user_id' => (int) ($data['user_id'] ?? 1),
                'likes' => 0,
                'body' => $data['body'] ?? 'New comment',
                'likesInfo' => array_merge($likesInfo, ['type' => 'brz_comment']),
                'created_at' => date('M d, Y h:i A'),
                'userData' => $userData,
                'isNew' => true,
            ];

            respond($newComment, 'Comment posted', 201);
        })(),
        'deleteComment' => respondEmpty('Comment deleted', 204),
        default => respond([], 'Unknown sub-action', 404),
    };
}

function handleLike(string $subAction): void
{
    global $likesInfo;

    match ($subAction) {
        'like' => respond(
            array_merge($likesInfo, ['count' => 1, 'alreadyLiked' => true]),
            'Like toggled',
            201
        ),
        default => respond([], 'Unknown sub-action', 404),
    };
}
