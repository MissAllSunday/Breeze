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
			MoodEntity::EMOJI => 'string',
			MoodEntity::DESC => 'string',
			MoodEntity::STATUS => 'string',
		], $data, MoodEntity::ID);

		return $this->getInsertedId();
	}

	public function update(array $data, int $id = 0): array
	{
		if (empty($data)) {
			return [];
		}

		$updated = $this->dbClient->update(
			MoodEntity::TABLE,
			'SET
				' . MoodEntity::EMOJI . ' = {string:emoji},
				' . MoodEntity::DESC . ' = {string:description},
				' . MoodEntity::STATUS . ' = {string:isActive}
				WHERE ' . MoodEntity::ID . ' = {int:id}',
			$data
		);

		return !$updated ? [] : $this->getByIDs([$id])[$id];
	}

	public function getByIDs(array $moodIds): array
	{
		$moods = [];

		if (empty($moodIds)) {
			return $moods;
		}

		$request = $this->dbClient->query(
			'
			SELECT ' . implode(', ', MoodEntity::getColumns()) . '
			FROM {db_prefix}' . MoodEntity::TABLE . '
			WHERE ' . MoodEntity::ID . ' IN ({array_int:moodIds})',
			['moodIds' => array_map('intval', $moodIds)]
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$moods[$row[MoodEntity::ID]] = $row;
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
			$moods[$row[MoodEntity::ID]] = $row;
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
			WHERE ' . MoodEntity::STATUS . ' = {int:status}',
			[
				'status' => $status,
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$moods[$row[MoodEntity::ID]] = array_map(function ($item) {
				return is_numeric($item) ? ((int)$item) : $item;
			}, $row);
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
		return MoodEntity::ID;
	}

	public function getColumns(): array
	{
		return MoodEntity::getColumns();
	}
}
