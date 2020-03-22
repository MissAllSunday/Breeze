<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\MemberEntity as MemberEntity;
use Breeze\Entity\OptionsEntity as OptionsEntity;

class UserModel extends BaseModel implements ModelInterface
{
	const JSON_VALUES = ['cover', 'petitionList', 'moodHistory'];
	const ARRAY_VALUES = ['blockListIDs'];

	function insert(array $data, int $userId = 0): int
	{
		if (empty($data) || empty($userId))
			return 0;

		$inserts = [];

		foreach ($data as $variable => $value)
		{
			if (in_array($variable, self::JSON_VALUES))
				$value = !empty($value) ? json_encode($value) : '';

			$inserts[] = [$userId, $variable, $value];
		}

		if (!empty($inserts))
			$this->dbClient->replace(
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

		$request = $this->dbClient->query(
			'
			SELECT ' . implode(', ', MemberEntity::getColumns()) . '
			FROM {db_prefix}' . MemberEntity::TABLE . '
			WHERE ' . MemberEntity::COLUMN_ID . ' IN ({array_int:userIds})',
			[
				'userIds' => array_map('intval', $userIds)
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request))
			$loadedUsers[$row[MemberEntity::COLUMN_ID]] = [
				'username' => $row[MemberEntity::COLUMN_MEMBER_NAME],
				'name' => $row[MemberEntity::COLUMN_REAL_NAME],
				'id' => $row[MemberEntity::COLUMN_ID],
			];

		$this->dbClient->freeResult($request);

		foreach ($userIds as $userId)
			if (!isset($loadedUsers[$userId]))
				$loadedUsers[$userId] = [];

		return $loadedUsers;
	}

	public function updateProfileViews(array $data, int $userId): int
	{
		if (empty($data) || empty($userId))
			return 0;

		return $this->dbClient->update(
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
		$userData = [];

		$result = $this->dbClient->query(
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

		while ($row = $this->dbClient->fetchAssoc($result))
		{
			$userData[$row[OptionsEntity::COLUMN_VARIABLE]] = is_numeric($row[OptionsEntity::COLUMN_VALUE]) ?
				(int) $row[OptionsEntity::COLUMN_VALUE] : (string) $row[OptionsEntity::COLUMN_VALUE];

			if (in_array($row[OptionsEntity::COLUMN_VARIABLE], self::JSON_VALUES))
				$userData[$row[OptionsEntity::COLUMN_VARIABLE]] = !empty($row[OptionsEntity::COLUMN_VALUE]) ?
					json_decode($row[OptionsEntity::COLUMN_VALUE], true) : [];

			if (in_array($row[OptionsEntity::COLUMN_VARIABLE], self::ARRAY_VALUES))
				$userData[$row[OptionsEntity::COLUMN_VARIABLE]] = !empty($row[OptionsEntity::COLUMN_VALUE]) ?
					explode(',', $row[OptionsEntity::COLUMN_VALUE]) : [];

			$userData += [
				'buddiesList' => !empty($row[MemberEntity::COLUMN_BUDDY_LIST]) ?
					explode(',', $row[MemberEntity::COLUMN_BUDDY_LIST]) : [],
				'ignoredList' => !empty($row[MemberEntity::COLUMN_IGNORE_LIST]) ?
					explode(',', $row[MemberEntity::COLUMN_IGNORE_LIST]) : [],
				'profileViews' => $row[MemberEntity::COLUMN_PROFILE_VIEWS],
			];
		}

		$this->dbClient->freeResult($result);

		return $userData;
	}

	public function getViews($userId = 0): array
	{
		$views = [];

		if (empty($userId))
			return $views;

		$result = $this->dbClient->query(
			'
			SELECT ' . MemberEntity::COLUMN_PROFILE_VIEWS . '
			FROM {db_prefix}' . MemberEntity::TABLE . '
			WHERE ' . MemberEntity::COLUMN_ID . ' = {int:userId}',
			[
				'userId' => (int) $userId
			]
		);

		$views = $this->dbClient->fetchAssoc($result);
		$views = !empty($views) ? json_decode($views[0], true) : [];

		$this->dbClient->freeResult($result);

		return $views;
	}

	public function deleteViews($userId): void
	{
		$this->dbClient->update(
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

		$request = $this->dbClient->query(
			'
			SELECT id_board
			FROM {db_prefix}boards as b
			WHERE {query_wanna_see_board}',
			[]
		);

		while ($row = $this->dbClient->fetchAssoc($request))
			$boards[] = $row['id_board'];

		$this->dbClient->freeResult($request);

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
