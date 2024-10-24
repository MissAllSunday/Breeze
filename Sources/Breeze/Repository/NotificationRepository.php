<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\NotificationEntity;
use Breeze\Model\NotificationModelInterface;
use Breeze\Util\Json;

class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
	public function __construct(
		private readonly NotificationModelInterface $notificationModel
	) {}

	/**
	 * @throws InvalidNotificationException
	 */
	public function save(array $data): int
	{
		$newNotificationId = 0;

		if ($data === []) {
			return $newNotificationId;
		}

		$newNotificationId = $this->notificationModel->insert([
			NotificationEntity::TASK_BACKGROUND_FILE,
			NotificationEntity::TASK_BACKGROUND_CLASS,
			Json::encode($data),
			NotificationEntity::TASK_BACKGROUND_DEFAULT_CLAIMED_TIME,
		]);

		if ($newNotificationId === 0) {
			throw new InvalidNotificationException('error_save_notification');
		}

		return $newNotificationId;
	}

	public function getById(int $id): array
	{
		return $this->notificationModel->getById($id);
	}
}
