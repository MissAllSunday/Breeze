<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\MentionEntity as MentionEntity;

class MentionModel extends BaseModel implements MentionModelInterface
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
		return MentionEntity::TABLE;
	}

	function getColumnId(): string
	{
		return MentionEntity::COLUMN_CONTENT_ID;
	}

	function getColumns(): array
	{
		return MentionEntity::getColumns();
	}
}
