<?php

declare(strict_types=1);

namespace Breeze\Entity;

class CommentEntity extends BaseEntity implements BaseEntityInterface
{
	const TABLE = 'breeze_comments';
	const ID ='id';
	const STATUS_ID = 'statusId';
	const USER_ID = 'userId';
	const CREATED_AT = 'createdAt';
	const BODY = 'body';
	const LIKES = 'likes';

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
