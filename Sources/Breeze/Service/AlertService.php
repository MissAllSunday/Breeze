<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Entity\EntityInterface;
use Breeze\Event\HandlerServiceProvider;
use Breeze\Repository\AlertRepositoryInterface;
use Breeze\Repository\User\SettingsRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;

class AlertService extends BaseService implements AlertServiceInterface
{
	public function __construct(
		protected AlertRepositoryInterface $alertRepository,
		protected HandlerServiceProvider $handlerServiceProvider,
		protected SettingsRepositoryInterface $userSettingsRepository,
	) {
		$this->setLanguage(Breeze::NAME . 'Alerts');
		parent::__construct($alertRepository);
	}

	public function send(AlertEntity $alertEntity): void
	{
		if ($this->isBlockedRelationship($alertEntity->getIdMemberStarted(), $alertEntity->getIdMember())) {
			return;
		}

		if ($this->checkAlert($alertEntity)) {
			return;
		}

		$this->alertRepository->insert($alertEntity);

		updateMemberData($alertEntity->getIdMember(), ['alerts' => '+']);
	}

	private function isBlockedRelationship(int $senderId, int $recipientId): bool
	{
		if ($senderId === 0 || $recipientId === 0) {
			return false;
		}

		$settings = $this->userSettingsRepository->getByIds([$senderId, $recipientId]);

		$senderBlockList = ($settings[$senderId] ?? null)?->getBlockList() ?? [];
		$recipientBlockList = ($settings[$recipientId] ?? null)?->getBlockList() ?? [];

		return in_array($recipientId, $senderBlockList, true)
			|| in_array($senderId, $recipientBlockList, true);
	}

	public function handle(array &$alerts): void
	{
		$this->setLanguage(Breeze::NAME . 'Alerts');

		foreach ($alerts as &$alert) {
			if (!str_contains($alert['content_type'], Breeze::PATTERN)) {
				continue;
			}

			try {
				$alertEntity = AlertEntity::from($alert);
				$handler = $this->handlerServiceProvider->getHandler($alertEntity);
				$alert = $handler->resolve();
			} catch (DataNotFoundException) {
				continue;
			}
		}
	}

	public function checkAlert(AlertEntity $alertEntity): bool
	{
		return $this->alertRepository->checkAlert($alertEntity);
	}

	public function getById(int $alertId): EntityInterface
	{
		return $this->alertRepository->getById($alertId);
	}

	public function delete(int $alertId): bool
	{
		return $this->alertRepository->delete([$alertId]);
	}

	public function getPendingBuddyAlerts(int $userId): array
	{
		return $this->alertRepository->getPendingBuddyAlerts($userId);
	}
}
