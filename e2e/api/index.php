<?php

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-SMF-AJAX');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = $_GET['action'] ?? '';
$subAction = $_GET['sa'] ?? '';

$token = [
    'var' => 'breeze_token_var',
    'value' => 'mock_csrf_token_' . bin2hex(random_bytes(8)),
];

$userData = [
    'avatar' => [
        'href' => 'https://via.placeholder.com/120',
        'image' => '<img src="https://via.placeholder.com/120" alt="avatar">',
        'name' => 'avatar',
        'url' => 'https://via.placeholder.com/120',
    ],
    'buddies' => ['2', '3'],
    'custom_fields' => [],
    'email' => 'testuser@example.com',
    'group' => 'Regular Members',
    'group_color' => '#666',
    'group_icons' => '',
    'group_id' => '1',
    'href' => '?action=profile;u=1',
    'id' => 1,
    'is_activated' => '1',
    'is_banned' => false,
    'is_buddy' => false,
    'is_guest' => false,
    'is_reverse_buddy' => false,
    'last_login_timestamp' => (string) time(),
    'legacy_url' => '?action=profile;area=legacy;u=1',
    'link' => '<a href="?action=profile;u=1">Test User</a>',
    'link_color' => '<a href="?action=profile;u=1" style="color: #333;">Test User</a>',
    'name' => 'Test User',
    'name_color' => '<span style="color: #333;">Test User</span>',
    'online' => [
        'href' => '#',
        'is_online' => true,
        'label' => 'Online',
        'link' => '<a href="#">Online</a>',
        'member_online_text' => 'Online',
        'text' => 'Online',
    ],
    'signature' => 'E2E test user',
    'title' => 'Member',
    'username' => 'testuser',
    'username_color' => '<span>testuser</span>',
];

$likesInfo = [
    'text' => '',
    'href' => '',
    'likes' => [],
    'contentId' => 0,
    'count' => 0,
    'type' => 'brz_status',
    'alreadyLiked' => false,
    'canLike' => true,
];

$permissions = [
    'Status' => ['edit' => true, 'delete' => true, 'post' => true],
    'Comments' => ['edit' => true, 'delete' => true, 'post' => true],
    'isEnable' => ['enableLikes' => true],
    'Forum' => ['likesLike' => true, 'adminForum' => false, 'profileView' => true],
];

$mockStatuses = [];
for ($i = 1; $i <= 3; $i++) {
    $commentLikesInfo = array_merge($likesInfo, [
        'contentId' => $i * 100,
        'type' => 'brz_comment',
    ]);

    $mockStatuses[] = [
        'id' => $i,
        'wall_id' => 1,
        'user_id' => 1,
        'likes' => 0,
        'body' => "This is mock status #{$i} for E2E testing.",
        'created_at' => date('M d, Y h:i A', time() - ($i * 3600)),
        'likesInfo' => array_merge($likesInfo, ['contentId' => $i]),
        'comments' => [
            [
                'id' => $i * 100,
                'status_id' => $i,
                'user_id' => 1,
                'likes' => 0,
                'body' => "A comment on status #{$i}",
                'likesInfo' => $commentLikesInfo,
                'created_at' => date('M d, Y h:i A', time() - ($i * 1800)),
                'userData' => $userData,
                'isNew' => false,
            ],
        ],
        'userData' => $userData,
        'isNew' => false,
    ];
}

require __DIR__ . '/router.php';
