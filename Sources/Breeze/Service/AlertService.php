<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Entity\EntityInterface;
use Breeze\Event\HandlerServiceProvider;
use Breeze\Repository\AlertRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;

class AlertService extends BaseService implements AlertServiceInterface
{
	public function __construct(
		protected AlertRepositoryInterface $alertRepository,
		protected HandlerServiceProvider $handlerServiceProvider
	) {
		$this->setLanguage(Breeze::NAME . 'Alerts');
		parent::__construct($alertRepository);
	}

	public function send(AlertEntity $alertEntity): void
	{
		$this->alertRepository->insert($alertEntity);

		updateMemberData($alertEntity->getIdMember(), ['alerts' => '+']);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function handle(array &$alerts): void
	{
		foreach ($alerts as $id => &$alert) {
			if (!str_contains($alert['content_type'], Breeze::PATTERN)) {
				continue;
			}

			$alertEntity = AlertEntity::from($alert);
			$handler = $this->handlerServiceProvider->getHandler($alertEntity);
			$alert = $handler->resolve();
		}
	}

	public function getById(int $alertId): EntityInterface
	{
		return $this->alertRepository->getById($alertId);
	}

	public function delete(int $alertId): bool
	{
		return $this->alertRepository->delete([$alertId]);
	}
}
