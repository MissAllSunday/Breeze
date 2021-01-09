<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\MoodEntity as MoodEntity;

class MoodModel extends BaseModel implements MoodModelInterface
{
	public function insert(array $data, int $id = 0): int
	{
		if (empty($data)) {
			return 0;
		}

		$this->dbClient->insert(MoodEntity::TABLE, [
			MoodEntity::COLUMN_EMOJI => 'string',
			MoodEntity::COLUMN_DESC => 'string',
			MoodEntity::COLUMN_STATUS => 'string'
		], $data, MoodEntity::COLUMN_ID);

		return $this->getInsertedId();
	}

	public function update(array $data, int $id = 0): array
	{
		if (empty($data)) {
			return [];
		}

		$this->dbClient->update(
			MoodEntity::TABLE,
			'SET
				' . MoodEntity::COLUMN_EMOJI . ' = {string:name},
				' . MoodEntity::COLUMN_DESC . ' = {string:description},
				' . MoodEntity::COLUMN_STATUS . ' = {string:isActive}
				WHERE ' . MoodEntity::COLUMN_ID . ' = {int:id}',
			$data
		);

		return $this->getMoodByIDs([$id])[0];
	}

	public function getMoodByIDs(array $moodIds): array
	{
		$moods = [];

		if (empty($moodIds)) {
			return $moods;
		}

		$request = $this->dbClient->query(
			'
			SELECT ' . implode(', ', MoodEntity::getColumns()) . '
			FROM {db_prefix}' . MoodEntity::TABLE . '
			WHERE ' . MoodEntity::COLUMN_ID . ' IN ({array_int:moodIds})',
			['moodIds' => array_map('intval', $moodIds)]
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$moods[$row[MoodEntity::COLUMN_ID]] = $row;
		}

		$this->dbClient->freeResult($request);

		return $moods;
	}

	public function getAllMoods(): array
	{
		$moods = [];

		$request = $this->dbClient->query(
			'SELECT ' . implode(', ', $this->getColumns()) . '
			FROM {db_prefix}' . $this->getTableName(),
			[]
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$moods[$row[MoodEntity::COLUMN_ID]] = $row;
		}

		$this->dbClient->freeResult($request);

		return $moods;
	}

	public function getMoodsByStatus(int $status): array
	{
		$moods = [];

		$request = $this->dbClient->query(
			'SELECT ' . implode(', ', $this->getColumns()) . '
			FROM {db_prefix}' . $this->getTableName() . '
			WHERE ' . MoodEntity::COLUMN_STATUS . ' = {int:status}',
			[
				'status' => $status
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$moods[$row[MoodEntity::COLUMN_ID]] = $row;
		}

		$this->dbClient->freeResult($request);

		return $moods;
	}

	public function getTableName(): string
	{
		return MoodEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return MoodEntity::COLUMN_ID;
	}

	public function getColumns(): array
	{
		return MoodEntity::getColumns();
	}
}
