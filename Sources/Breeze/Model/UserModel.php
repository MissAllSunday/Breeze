<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\MemberBaseEntity as MemberEntity;
use Breeze\Entity\OptionsBaseEntity as OptionsEntity;

class UserModel extends BaseBaseModel implements UserModelInterface
{
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
				OptionsBaseEntity::TABLE,
				[
					MemberBaseEntity::COLUMN_ID => 'int',
					OptionsBaseEntity::COLUMN_VARIABLE => 'string',
					OptionsBaseEntity::COLUMN_VALUE => 'string'
				],
				$inserts,
				MemberBaseEntity::COLUMN_ID
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
			SELECT ' . implode(', ', MemberBaseEntity::getColumns()) . '
			FROM {db_prefix}' . MemberBaseEntity::TABLE . '
			WHERE ' . MemberBaseEntity::COLUMN_ID . ' IN ({array_int:userIds})',
			[
				'userIds' => array_map('intval', $userIds)
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request))
			$loadedUsers[$row[MemberBaseEntity::COLUMN_ID]] = [
				'username' => $row[MemberBaseEntity::COLUMN_MEMBER_NAME],
				'name' => $row[MemberBaseEntity::COLUMN_REAL_NAME],
				'id' => $row[MemberBaseEntity::COLUMN_ID],
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
			MemberBaseEntity::TABLE,
			'
			SET ' . MemberBaseEntity::COLUMN_PROFILE_VIEWS . ' = {string:jsonData}
			WHERE ' . MemberBaseEntity::COLUMN_ID . ' = ({int:userId})',
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
			'SELECT op.' . (implode(', op.', OptionsBaseEntity::getColumns())) . ', 
			mem.' . (implode(', mem.', MemberBaseEntity::getColumns())) . '
			FROM {db_prefix}' . OptionsBaseEntity::TABLE . ' AS op
				LEFT JOIN {db_prefix}' . MemberBaseEntity::TABLE . ' 
				AS mem ON (mem.' . MemberBaseEntity::COLUMN_ID . ' = {int:userId})
			WHERE ' . MemberBaseEntity::COLUMN_ID . ' = {int:userId}',
			[
				'userId' => $userId,
			]
		);

		while ($row = $this->dbClient->fetchAssoc($result))
		{
			$userData[$row[OptionsBaseEntity::COLUMN_VARIABLE]] = is_numeric($row[OptionsBaseEntity::COLUMN_VALUE]) ?
				(int) $row[OptionsBaseEntity::COLUMN_VALUE] : (string) $row[OptionsBaseEntity::COLUMN_VALUE];

			if (in_array($row[OptionsBaseEntity::COLUMN_VARIABLE], self::JSON_VALUES))
				$userData[$row[OptionsBaseEntity::COLUMN_VARIABLE]] = !empty($row[OptionsBaseEntity::COLUMN_VALUE]) ?
					json_decode($row[OptionsBaseEntity::COLUMN_VALUE], true) : [];

			if (in_array($row[OptionsBaseEntity::COLUMN_VARIABLE], self::ARRAY_VALUES))
				$userData[$row[OptionsBaseEntity::COLUMN_VARIABLE]] = !empty($row[OptionsBaseEntity::COLUMN_VALUE]) ?
					explode(',', $row[OptionsBaseEntity::COLUMN_VALUE]) : [];

			$userData += [
				'buddiesList' => !empty($row[MemberBaseEntity::COLUMN_BUDDY_LIST]) ?
					explode(',', $row[MemberBaseEntity::COLUMN_BUDDY_LIST]) : [],
				'ignoredList' => !empty($row[MemberBaseEntity::COLUMN_IGNORE_LIST]) ?
					explode(',', $row[MemberBaseEntity::COLUMN_IGNORE_LIST]) : [],
				'profileViews' => $row[MemberBaseEntity::COLUMN_PROFILE_VIEWS],
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
			SELECT ' . MemberBaseEntity::COLUMN_PROFILE_VIEWS . '
			FROM {db_prefix}' . MemberBaseEntity::TABLE . '
			WHERE ' . MemberBaseEntity::COLUMN_ID . ' = {int:userId}',
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
			MemberBaseEntity::TABLE,
			'
			SET ' . MemberBaseEntity::COLUMN_PROFILE_VIEWS . ' = {string:empty}
			WHERE ' . MemberBaseEntity::COLUMN_ID . ' = {int:userId}',
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
		return MemberBaseEntity::TABLE;
	}

	function getColumnId(): string
	{
		return MemberBaseEntity::COLUMN_ID;
	}

	function getColumns(): array
	{
		return MemberBaseEntity::getColumns();
	}
}
