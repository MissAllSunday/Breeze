<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\Member as MemberEntity;
use Breeze\Entity\Options as OptionsEntity;

class User extends Base
{
	const JSON_VALUES = ['cover', 'petitionList', 'moodHistory'];
	const ARRAY_VALUES = ['blockListIDs'];

	function insert(array $data, int $userId = 0): int
	{
		if (empty($data) || empty($userID))
			return 0;

		$inserts = [];

		foreach ($data as $variable => $value)
		{
			if (in_array($variable, self::JSON_VALUES))
				$value = !empty($value) ? json_encode($value) : '';

			$inserts[] = [$userId, $variable, $value];
		}

		if (!empty($inserts))
			$this->db->replace(
			    OptionsEntity::TABLE,
			    [
			        MemberEntity::COLUMN_ID => 'int',
			        OptionsEntity::COLUMN_VARIABLE => 'string',
			        OptionsEntity::COLUMN_VALUE => 'string'
			    ],
			    $inserts,
			    MemberEntity::COLUMN_ID
			);

		return 1;
	}

	function update(array $data, int $userId = 0): array
	{
		return [];
	}

	public function loadMinData(array $userIds): array
	{
		$userIds = array_unique($userIds);
		$loadedUsers = [];

		if (empty($userIds))
			return $loadedUsers;

		$request = $this->db->query(
		    '
			SELECT ' . implode(', ', MemberEntity::getColumns()) . '
			FROM {db_prefix}' . MemberEntity::TABLE . '
			WHERE ' . MemberEntity::COLUMN_ID . ' IN ({array_int:userIds})',
		    [
		        'userIds' => array_map('intval', $userIds)
		    ]
		);

		while ($row = $this->db->fetchAssoc($request))
			$loadedUsers[$row[MemberEntity::COLUMN_ID]] = [
			    'username' => $row[MemberEntity::COLUMN_MEMBER_NAME],
			    'name' => $row[MemberEntity::COLUMN_REAL_NAME],
			    'id' => $row[MemberEntity::COLUMN_ID],
			];

		$this->db->freeResult($request);

		foreach ($userIds as $userId)
			if (!isset($loadedUsers[$userId]))
				$loadedUsers[$userId] = [];

		return $loadedUsers;
	}

	public function updateProfileViews(array $data, int $userId): int
	{
		if (empty($data) || empty($userId))
			return 0;

		return $this->db->update(
		    MemberEntity::TABLE,
		    '
			SET ' . MemberEntity::COLUMN_PROFILE_VIEWS . ' = {string:jsonData}
			WHERE ' . MemberEntity::COLUMN_ID . ' = ({int:userId})',
		    [
		        'userId' => (int) $userId,
		        'jsonData' => json_encode($data),
		    ]
		);
	}

	public function getUserSettings(int $userId): array
	{
		$data = [];

		$result = $this->db->query(
		    'SELECT op.' . (implode(', op.', OptionsEntity::getColumns())) . ', 
			mem.' . (implode(', mem.', MemberEntity::getColumns())) . '
			FROM {db_prefix}' . OptionsEntity::TABLE . ' AS op
				LEFT JOIN {db_prefix}' . MemberEntity::TABLE . ' 
				AS mem ON (mem.' . MemberEntity::COLUMN_ID . ' = {int:user})
			WHERE ' . MemberEntity::COLUMN_ID . ' = {int:userId}',
		    [
		        'userId' => $userId,
		    ]
		);

		while ($row = $this->db->fetchAssoc($result))
		{
			$data[$row[OptionsEntity::COLUMN_VARIABLE]] = is_numeric($row[OptionsEntity::COLUMN_VALUE]) ?
				(int) $row[OptionsEntity::COLUMN_VALUE] : (string) $row[OptionsEntity::COLUMN_VALUE];

			if (in_array($row[OptionsEntity::COLUMN_VARIABLE], self::JSON_VALUES))
				$data[$row[OptionsEntity::COLUMN_VARIABLE]] = !empty($row[OptionsEntity::COLUMN_VALUE]) ?
					json_decode($row[OptionsEntity::COLUMN_VALUE], true) : [];

			if (in_array($row[OptionsEntity::COLUMN_VARIABLE], self::ARRAY_VALUES))
				$data[$row[OptionsEntity::COLUMN_VARIABLE]] = !empty($row[OptionsEntity::COLUMN_VALUE]) ?
					explode(',', $row[OptionsEntity::COLUMN_VALUE]) : [];

			$data += [
			    'buddiesList' => !empty($row[MemberEntity::COLUMN_BUDDY_LIST]) ?
			    	explode(',', $row[MemberEntity::COLUMN_BUDDY_LIST]) : [],
			    'ignoredList' => !empty($row[MemberEntity::COLUMN_IGNORE_LIST]) ?
			    	explode(',', $row[MemberEntity::COLUMN_IGNORE_LIST]) : [],
			    'profileViews' => $row[MemberEntity::COLUMN_PROFILE_VIEWS],
			];
		}

		$this->db->freeResult($result);

		return $data;
	}

	public function getViews($userId = 0): array
	{
		$views = [];

		if (empty($userId))
			return $views;

		$result = $this->db->query(
		    '
			SELECT ' . MemberEntity::COLUMN_PROFILE_VIEWS . '
			FROM {db_prefix}' . MemberEntity::TABLE . '
			WHERE ' . MemberEntity::COLUMN_ID . ' = {int:userId}',
		    [
		        'userId' => (int) $userId
		    ]
		);

		$views = $this->db->fetchAssoc($result);
		$views = !empty($views) ? json_decode($views[0], true) : [];

		$this->db->freeResult($result);

		return $views;
	}

	public function deleteViews($userId): void
	{
		$this->db->update(
		    MemberEntity::TABLE,
		    '
			SET ' . MemberEntity::COLUMN_PROFILE_VIEWS . ' = {string:empty}
			WHERE ' . MemberEntity::COLUMN_ID . ' = {int:userId}',
		    [
		        'userId' => (int) $userId,
		        'empty' => ''
		    ]
		);
	}

	public function wannaSeeBoards(): array
	{
		$boards = [];

		$request = $this->db->query(
		    '
			SELECT id_board
			FROM {db_prefix}boards as b
			WHERE {query_wanna_see_board}',
		    []
		);

		while ($row = $this->db->fetchAssoc($request))
			$boards[] = $row['id_board'];

		$this->db->freeResult($request);

		return $boards;
	}

	function getTableName(): string
	{
		return MemberEntity::TABLE;
	}

	function getColumnId(): string
	{
		return MemberEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return MemberEntity::getColumns();
	}
}
