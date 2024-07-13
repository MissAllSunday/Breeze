<?php

declare(strict_types=1);


namespace Breeze\Repository;


use Breeze\Model\NotificationModel;

class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
	public function __construct(
		private readonly NotificationModel $notificationModel
	) {}

	public function save(array $data): int
	{
		$newNotificationId = $this->notificationModel->insert(array_merge($data, [
			StatusEntity::CREATED_AT => time(),
			StatusEntity::LIKES => 0,
		]));

		if ($newNotificationId === 0) {
			throw new InvalidStatusException('error_save_status');
		}

		return $newNotificationId;
	}

	public function getById(int $notificationId): ?ActionLogInterface
	{
		return null;
	}
}
