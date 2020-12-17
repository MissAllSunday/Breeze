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

	public function insert(array $data, int $id = 0): int
	{
		return 1;
	}

	public function update(array $data, int $id = 0): array
	{
		return [];
	}

	public function getTableName(): string
	{
		return MentionEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return MentionEntity::COLUMN_CONTENT_ID;
	}

	public function getColumns(): array
	{
		return MentionEntity::getColumns();
	}
}
