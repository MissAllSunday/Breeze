<?php

declare(strict_types=1);

namespace Breeze\Entity;

class CommentEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'breeze_comments';

	public const ID ='id';

	public const STATUS_ID = 'statusId';

	public const USER_ID = 'userId';

	public const CREATED_AT = 'createdAt';

	public const BODY = 'body';

	public const LIKES = 'likes';

	public static function getColumns(): array
	{
		return [
			self::ID,
			self::STATUS_ID,
			self::USER_ID,
			self::CREATED_AT,
			self::BODY,
			self::LIKES,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
