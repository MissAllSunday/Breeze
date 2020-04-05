<?php

declare(strict_types=1);

namespace Breeze\Model;

use \Breeze\Entity\MemberBaseEntity as MemberEntity;
use Breeze\Entity\MentionBaseEntity as MentionEntity;

class MentionModel extends BaseBaseModel implements MentionModelInterface
{
	public function userMention(string $match): array
	{
		return $mention = [
			'name' => '',
			'id' => ''
		];
	}

	function insert(array $data, int $id = 0): int
	{
		return 1;
	}

	function update(array $data, int $id = 0): array
	{
		return [];
	}

	function getTableName(): string
	{
		return MentionBaseEntity::TABLE;
	}

	function getColumnId(): string
	{
		return MentionBaseEntity::COLUMN_CONTENT_ID;
	}

	function getColumns(): array
	{
		return MentionBaseEntity::getColumns();
	}
}
