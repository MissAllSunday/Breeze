<?php


use Breeze\Entity\Mood as MoodEntity;

class Mood extends Base
{

	function insert(array $data, int $id = 0): int
	{
		// TODO: Implement insert() method.
	}

	function update(array $data, int $id = 0): array
	{
		// TODO: Implement update() method.
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