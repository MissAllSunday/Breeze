<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Entity\BuddyRequestEntity;
use Breeze\Entity\EntityInterface;

class BuddyRequestRepository extends BaseRepository implements BuddyRequestRepositoryInterface
{
	public function getTableName(): string
	{
		return BuddyRequestEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return BuddyRequestEntity::ID;
	}

	public function getColumnPosterId(): string
	{
		return BuddyRequestEntity::SENDER_ID;
	}

	public function getColumns(): array
	{
		return array_keys(BuddyRequestEntity::getColumns());
	}

	public function getById(int $id): ?EntityInterface
	{
		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:table}
			WHERE {raw:idColumn} = {int:idValue}',
			[
				'table' => BuddyRequestEntity::TABLE,
				'columns' => implode(', ', $this->getColumns()),
				'idColumn' => BuddyRequestEntity::ID,
				'idValue' => $id,
			]
		);

		$results = $this->prepareData($request);

		return $results[0] ?? null;
	}

	public function insert(int $senderId, int $receiverId): void
	{
		$this->dbClient->insert(
			BuddyRequestEntity::TABLE,
			[
				BuddyRequestEntity::SENDER_ID => 'int',
				BuddyRequestEntity::RECEIVER_ID => 'int',
				BuddyRequestEntity::STATUS => 'int',
				BuddyRequestEntity::CREATED_AT => 'int',
			],
			[
				BuddyRequestEntity::SENDER_ID => $senderId,
				BuddyRequestEntity::RECEIVER_ID => $receiverId,
				BuddyRequestEntity::STATUS => BuddyRequestEntity::PENDING,
				BuddyRequestEntity::CREATED_AT => time(),
			],
			BuddyRequestEntity::ID
		);
	}

	public function deleteByUsers(int $senderId, int $receiverId): void
	{
		$this->dbClient->query(
			'
			DELETE FROM {db_prefix}{raw:table}
			WHERE {raw:sender} = {int:senderId}
				AND {raw:receiver} = {int:receiverId}',
			[
				'table' => BuddyRequestEntity::TABLE,
				'sender' => BuddyRequestEntity::SENDER_ID,
				'receiver' => BuddyRequestEntity::RECEIVER_ID,
				'senderId' => $senderId,
				'receiverId' => $receiverId,
			]
		);
	}

	public function updateStatus(int $senderId, int $receiverId, int $status): void
	{
		$this->dbClient->query(
			'
			UPDATE {db_prefix}{raw:table}
			SET {raw:status} = {int:statusValue}
			WHERE {raw:sender} = {int:senderId}
				AND {raw:receiver} = {int:receiverId}',
			[
				'table' => BuddyRequestEntity::TABLE,
				'status' => BuddyRequestEntity::STATUS,
				'sender' => BuddyRequestEntity::SENDER_ID,
				'receiver' => BuddyRequestEntity::RECEIVER_ID,
				'statusValue' => $status,
				'senderId' => $senderId,
				'receiverId' => $receiverId,
			]
		);
	}

	public function getBy(string $columnName, array $data = []): array
	{
		if ($data === [] || !in_array($columnName, $this->getColumns(), true)) {
			return [];
		}

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:table}
			WHERE {raw:columnName} IN ({array_int:data})',
			[
				'table' => BuddyRequestEntity::TABLE,
				'columns' => implode(', ', $this->getColumns()),
				'columnName' => $columnName,
				'data' => array_map('intval', $data),
			]
		);

		return $this->prepareData($request);
	}

	public function getStatusBy(int $status, string $columnName = '', array $data = []): array
	{
		$where = '{raw:status} = {int:statusValue}';
		$params = [
			'table' => BuddyRequestEntity::TABLE,
			'columns' => implode(', ', $this->getColumns()),
			'status' => BuddyRequestEntity::STATUS,
			'statusValue' => $status,
		];

		if ($columnName !== '' && $data !== [] && in_array($columnName, $this->getColumns(), true)) {
			$where .= ' AND {raw:columnName} IN ({array_int:data})';
			$params['columnName'] = $columnName;
			$params['data'] = array_map('intval', $data);
		}

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:table}
			WHERE ' . $where,
			$params
		);

		return $this->prepareData($request);
	}

	protected function prepareData(object $request): array
	{
		$results = [];

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$results[] = BuddyRequestEntity::from($row);
		}

		$this->dbClient->freeResult($request);

		return $results;
	}
}
