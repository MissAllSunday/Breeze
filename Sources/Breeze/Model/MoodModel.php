<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\MoodBaseEntity as MoodEntity;

class MoodModel extends BaseBaseModel implements MoodModelInterface
{
	function insert(array $data, int $id = 0): int
	{
		if (empty($data))
			return 0;

		$this->dbClient->insert(MoodBaseEntity::TABLE, [
			MoodBaseEntity::COLUMN_EMOJI => 'string',
			MoodBaseEntity::COLUMN_DESC => 'string',
			MoodBaseEntity::COLUMN_STATUS => 'string'
		], $data, MoodBaseEntity::COLUMN_ID);

		return $this->getInsertedId();
	}

	function update(array $data, int $id = 0): array
	{
		if (empty($data))
			return [];

		$this->dbClient->update(
			MoodBaseEntity::TABLE,
			'SET 
				' . MoodBaseEntity::COLUMN_EMOJI . ' = {string:name}, 
				' . MoodBaseEntity::COLUMN_DESC . ' = {string:description}, 
				' . MoodBaseEntity::COLUMN_STATUS . ' = {string:enable}
				WHERE ' . MoodBaseEntity::COLUMN_ID . ' = {int:moods_id}',
			$data
		);

		return $this->getMoodByIDs([$id])[0];
	}

	public function getMoodByIDs(array $moodIds): array
	{
		$moods = [];

		if (empty($moodIds))
			return $moods;

		$request = $this->dbClient->query(
			'
			SELECT ' . implode(', ', MoodBaseEntity::getColumns()) . '
			FROM {db_prefix}' . MoodBaseEntity::TABLE . '
			WHERE ' . MoodBaseEntity::COLUMN_ID . ' IN ({array_int:moodIds})',
			['moodIds' => array_map('intval', $moodIds)]
		);

		while ($row = $this->dbClient->fetchAssoc($request))
			$moods[$row[MoodBaseEntity::COLUMN_ID]] = $row;

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

		while ($row = $this->dbClient->fetchAssoc($request))
			$moods[$row[MoodBaseEntity::COLUMN_ID]] = $row;

		$this->dbClient->freeResult($request);

		return $moods;
	}

	public function getMoodsByStatus(int $status): array
	{
		$moods = [];

		$request = $this->dbClient->query(
			'SELECT ' . implode(', ', $this->getColumns()) . '
			FROM {db_prefix}' . $this->getTableName() . '
			WHERE ' . MoodBaseEntity::COLUMN_STATUS . ' = {int:status}',
			[
				'status' => $status
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request))
			$moods[$row[MoodBaseEntity::COLUMN_ID]] = $row;

		$this->dbClient->freeResult($request);

		return $moods;
	}

	function getTableName(): string
	{
		return MoodBaseEntity::TABLE;
	}

	function getColumnId(): string
	{
		return MoodBaseEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return MoodBaseEntity::getColumns();
	}
}
