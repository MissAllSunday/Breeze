<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\StatusEntity;
use Breeze\Model\NotificationModelInterface;

class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
	public function __construct(
		private readonly NotificationModelInterface $notificationModel
	) {}

	/**
	 * @throws InvalidStatusException
	 */
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

	public function getById(int $id): array
	{
		return $this->notificationModel->getById($id);
	}
}
