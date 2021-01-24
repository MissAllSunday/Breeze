<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\MemberEntity;
use Breeze\Entity\OptionsEntity;
use Breeze\Entity\UserSettingsEntity;

class UserModel extends BaseModel implements UserModelInterface
{
	public function insert(array $data, int $id = 0): int
	{
		if (empty($data)) {
			return 0;
		}

		$this->dbClient->replace(
			OptionsEntity::TABLE,
			[
				OptionsEntity::COLUMN_MEMBER_ID => 'int',
				OptionsEntity::COLUMN_VARIABLE => 'string',
				OptionsEntity::COLUMN_VALUE => 'string',
			],
			$data,
			OptionsEntity::COLUMN_MEMBER_ID
		);

		return 1;
	}

	public function update(array $data, int $userId = 0): array
	{
		return [];
	}

	public function loadMinData(array $userIds): array
	{
		$userIds = array_unique($userIds);
		$loadedUsers = [];

		if (empty($userIds)) {
			return $loadedUsers;
		}

		$request = $this->dbClient->query(
			'
			SELECT ' . implode(', ', MemberEntity::getColumns()) . '
			FROM {db_prefix}' . MemberEntity::TABLE . '
			WHERE ' . MemberEntity::ID . ' IN ({array_int:userIds})',
			[
				'userIds' => array_map('intval', $userIds)
			]
		);

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$loadedUsers[$row[MemberEntity::ID]] = [
				'username' => $row[MemberEntity::NAME],
				'name' => $row[MemberEntity::REAL_NAME],
				'id' => $row[MemberEntity::ID],
			];
		}

		$this->dbClient->freeResult($request);

		foreach ($userIds as $userId) {
			if (!isset($loadedUsers[$userId])) {
				$loadedUsers[$userId] = [];
			}
		}

		return $loadedUsers;
	}

	public function getUserSettings(int $userId): array
	{
		$userData = [];

		$result = $this->dbClient->query(
			'SELECT op.' . (implode(', op.', OptionsEntity::getColumns())) . ',
			mem.' . (implode(', mem.', MemberEntity::getColumns())) . '
			FROM {db_prefix}' . OptionsEntity::TABLE . ' AS op
				LEFT JOIN {db_prefix}' . MemberEntity::TABLE . '
				AS mem ON (mem.' . MemberEntity::ID . ' = {int:userId})
			WHERE ' . MemberEntity::ID . ' = {int:userId}',
			[
				'userId' => $userId,
			]
		);

		while ($row = $this->dbClient->fetchAssoc($result)) {
			$userData[$row[OptionsEntity::COLUMN_VARIABLE]] = is_numeric($row[OptionsEntity::COLUMN_VALUE]) ?
				(int) $row[OptionsEntity::COLUMN_VALUE] : (string) $row[OptionsEntity::COLUMN_VALUE];

			if (in_array($row[OptionsEntity::COLUMN_VARIABLE], self::JSON_VALUES)) {
				$userData[$row[OptionsEntity::COLUMN_VARIABLE]] = !empty($row[OptionsEntity::COLUMN_VALUE]) ?
					json_decode($row[OptionsEntity::COLUMN_VALUE], true) : [];
			}

			if (in_array($row[OptionsEntity::COLUMN_VARIABLE], self::ARRAY_VALUES)) {
				$userData[$row[OptionsEntity::COLUMN_VARIABLE]] = !empty($row[OptionsEntity::COLUMN_VALUE]) ?
					explode(',', $row[OptionsEntity::COLUMN_VALUE]) : [];
			}

			$userData += [
				UserSettingsEntity::BUDDIES => !empty($row[MemberEntity::BUDDY_LIST]) ?
					explode(',', $row[MemberEntity::BUDDY_LIST]) : [],
				UserSettingsEntity::BLOCK_LIST => !empty($row[MemberEntity::IGNORE_LIST]) ?
					explode(',', $row[MemberEntity::IGNORE_LIST]) : [],
			];
		}

		$this->dbClient->freeResult($result);

		return $userData;
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

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$boards[] = $row['id_board'];
		}

		$this->dbClient->freeResult($request);

		return $boards;
	}

	public function getTableName(): string
	{
		return MemberEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return MemberEntity::ID;
	}

	public function getColumns(): array
	{
		return MemberEntity::getColumns();
	}
}
