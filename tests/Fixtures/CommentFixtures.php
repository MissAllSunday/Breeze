<?php

declare(strict_types=1);

namespace Breeze\Fixtures;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\SharedEntity;

class CommentFixtures
{
    public static function basic(): array
    {
        return [
            CommentEntity::ID => 666,
            CommentEntity::STATUS_ID => 1,
            CommentEntity::USER_ID => 2,
            CommentEntity::BODY => 'This is a basic test comment',
            CommentEntity::LIKES => 0,
            SharedEntity::CREATED_AT => time(),
        ];
    }

    public static function withCustomData(array $overrides = []): array
    {
        return array_merge(self::basic(), $overrides);
    }

    public static function forInsertion(): array
    {
        $data = self::basic();
        unset($data[CommentEntity::ID]); // Remove ID for insertion
        return $data;
    }

    public static function multipleComments(): array
    {
        return [
            self::withCustomData([
                CommentEntity::ID => 1,
                CommentEntity::BODY => 'First comment',
            ]),
            self::withCustomData([
                CommentEntity::ID => 2,
                CommentEntity::BODY => 'Second comment',
                CommentEntity::USER_ID => 3,
            ]),
            self::withCustomData([
                CommentEntity::ID => 3,
                CommentEntity::BODY => 'Third comment',
                CommentEntity::STATUS_ID => 2,
            ]),
        ];
    }

    public static function invalidComment(): array
    {
        return [
            CommentEntity::STATUS_ID => 0, // Invalid status ID
            CommentEntity::USER_ID => 0,   // Invalid user ID
            CommentEntity::BODY => '',     // Empty body
        ];
    }
}
