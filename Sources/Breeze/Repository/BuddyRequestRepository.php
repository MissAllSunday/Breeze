<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\BuddyRequestEntity;
use Breeze\Enums\BuddyStatus;

class BuddyRequestRepository implements BuddyRequestRepositoryInterface
{
	public function __construct(
		protected readonly ClientInterface $dbClient
	) {}

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

	public function delete(int $senderId, int $receiverId): void
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

	public function getPendingByReceiver(int $receiverId): array
	{
		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:table}
			WHERE {raw:receiver} = {int:receiverId}
				AND {raw:status} = {int:pending}',
			[
				'table' => BuddyRequestEntity::TABLE,
				'columns' => implode(', ', array_keys(BuddyRequestEntity::getColumns())),
				'receiver' => BuddyRequestEntity::RECEIVER_ID,
				'status' => BuddyRequestEntity::STATUS,
				'receiverId' => $receiverId,
				'pending' => BuddyRequestEntity::PENDING,
			]
		);

		$results = [];
		while ($row = $this->dbClient->fetchAssoc($request)) {
			$results[] = BuddyRequestEntity::from($row);
		}

		$this->dbClient->freeResult($request);

		return $results;
	}

	public function getStatus(int $senderId, int $receiverId): ?int
	{
		$request = $this->dbClient->query(
			'
			SELECT {raw:status}
			FROM {db_prefix}{raw:table}
			WHERE ({raw:sender} = {int:senderId} AND {raw:receiver} = {int:receiverId})
				OR ({raw:sender} = {int:receiverId} AND {raw:receiver} = {int:senderId})',
			[
				'table' => BuddyRequestEntity::TABLE,
				'status' => BuddyRequestEntity::STATUS,
				'sender' => BuddyRequestEntity::SENDER_ID,
				'receiver' => BuddyRequestEntity::RECEIVER_ID,
				'senderId' => $senderId,
				'receiverId' => $receiverId,
			]
		);

		$row = $this->dbClient->fetchAssoc($request);
		$this->dbClient->freeResult($request);

		return is_array($row) ? (int) $row[BuddyRequestEntity::STATUS] : null;
	}

	public function getStatusesForUsers(int $currentUserId, array $userIds): array
	{
		if ($userIds === []) {
			return [];
		}

		$request = $this->dbClient->query(
			'
			SELECT
				{raw:sender},
				{raw:receiver},
				{raw:status}
			FROM {db_prefix}{raw:table}
			WHERE {raw:sender} = {int:currentUserId}
				AND {raw:receiver} IN ({array_int:userIds})
				OR {raw:receiver} = {int:currentUserId}
					AND {raw:sender} IN ({array_int:userIds})',
			[
				'table' => BuddyRequestEntity::TABLE,
				'sender' => BuddyRequestEntity::SENDER_ID,
				'receiver' => BuddyRequestEntity::RECEIVER_ID,
				'status' => BuddyRequestEntity::STATUS,
				'currentUserId' => $currentUserId,
				'userIds' => array_map('intval', $userIds),
			]
		);

		$results = [];
		while ($row = $this->dbClient->fetchAssoc($request)) {
			$senderId = (int) $row[BuddyRequestEntity::SENDER_ID];
			$receiverId = (int) $row[BuddyRequestEntity::RECEIVER_ID];
			$status = (int) $row[BuddyRequestEntity::STATUS];

			if ($senderId === $currentUserId) {
				$results[$receiverId] = BuddyStatus::fromDbStatus($status);
			} elseif ($receiverId === $currentUserId) {
				$results[$senderId] = BuddyStatus::fromDbStatus($status);
			}
		}

		$this->dbClient->freeResult($request);

		return $results;
	}
}
