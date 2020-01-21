<?php

declare(strict_types=1);


use Breeze\Breeze;
use Breeze\Entity\Like as LikeEntity;

class Like extends Base
{
	function insert(array $data, int $id = 0): int
	{
		// TODO: Implement insert() method.
	}

	function update(array $data, int $id = 0): array
	{
		global $smcFunc;

		$smcFunc['db_query'](
		    '',
		    'UPDATE {db_prefix}' . ($this->_tables[$type]['table']) . '
			SET likes = {int:num_likes}
			WHERE ' . ($type) . '_id = {int:id_content}',
		    [
		        'id_content' => $content,
		        'num_likes' => $numLikes,
		    ]
		);
	}

	public function userLikes(string $type, int $userId = 0): array
	{
		$likes = [];

		$request = $this->db['db_query'](
		    '',
		    'SELECT ' . LikeEntity::COLUMN_CONTENT_ID . '
			FROM {db_prefix}' . $this->getTableName() . '
			WHERE ' . $this->getColumnId() . ' = {int:userId}
				AND ' . LikeEntity::COLUMN_CONTENT_TYPE . ' = {string:type}',
		    [
		        'userId' => $userId,
		        'type' => $type,
		    ]
		);

		// @todo fetch all the columns and not just the content_id, for statistics and stuff...
		while ($row = $this->db['db_fetch_assoc']($request))
			$likes[$userId][$type][] = (int) $row[LikeEntity::COLUMN_CONTENT_ID];

		$this->db['db_free_result']($request);

		return $likes;
	}

	function getTableName(): string
	{
		return LikeEntity::TABLE;
	}

	function getColumnId(): string
	{
		return LikeEntity::COLUMN_ID_MEMBER;
	}

	function getColumns(): array
	{
		return LikeEntity::getColumns();
	}
}
