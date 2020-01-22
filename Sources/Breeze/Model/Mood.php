<?php

declare(strict_types=1);


use Breeze\Entity\Mood as MoodEntity;

class Mood extends Base
{
	function insert(array $data, int $id = 0): int
	{
		if (empty($data))
			return 0;

		$this->db->insert(MoodEntity::TABLE, [
		    MoodEntity::COLUMN_NAME => 'string',
		    MoodEntity::COLUMN_FILE => 'string',
		    MoodEntity::COLUMN_EXT => 'string',
		    MoodEntity::COLUMN_DESC => 'string',
		    MoodEntity::COLUMN_ENABLE => 'string'
		], $data, MoodEntity::COLUMN_ID);

		return $this->getInsertedId();
	}

	function update(array $data, int $id = 0): array
	{
		if (empty($data))
			return [];

		$this->db->update(
		    MoodEntity::TABLE,
		    'SET 
				' . MoodEntity::COLUMN_NAME . ' = {string:name}, 
				' . MoodEntity::COLUMN_FILE . ' = {string:file}, 
				' . MoodEntity::COLUMN_EXT . ' = {string:ext}, 
				' . MoodEntity::COLUMN_DESC . ' = {string:description}, 
				' . MoodEntity::COLUMN_ENABLE . ' = {string:enable}
				WHERE ' . MoodEntity::COLUMN_ID . ' = {int:moods_id}',
		    $data
		);

		return $this->getMoodByIDs([$id])[0];
	}

	public function getMoodByIDs(array $moodIds): array
	{
		$moods = [];

		if (empty($moodIds))
			return $moods;

		$request = $this->db->query(
		    '
			SELECT ' . implode(', ', $this->getColumns()) . '
			FROM {db_prefix}' . $this->getTableName() . '
			WHERE ' . $this->$this->getColumnId() . ' IN ({array_int:moodIds})',
		    ['moodIds' => array_map('intval', $moodIds)]
		);

		while ($row = $this->db->fetchAssoc($request))
			$moods[$row[MoodEntity::COLUMN_ID]] = $row;

		$this->db->freeResult($request);

		return $moods;
	}

	public function getAllMoods(): array
	{
		$moods = [];

		$request = $this->db['db_query'](
		    '',
		    'SELECT ' . implode(', ', $this->getColumns()) . '
			FROM {db_prefix}' . $this->getTableName(),
		    []
		);

		while ($row = $this->db['db_fetch_assoc']($request))
			$moods[$row['moods_id']] = $row;

		$this->db['db_free_result']($request);

		return $moods;
	}

	function getTableName(): string
	{
		return MoodEntity::TABLE;
	}

	function getColumnId(): string
	{
		return MoodEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return MoodEntity::getColumns();
	}
}
