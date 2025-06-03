<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Entity\AlertHandledEntity;
use Breeze\Event\EventHandlerInterface;
use Breeze\Repository\AlertRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;

class AlertService extends BaseService implements AlertServiceInterface
{
	protected const HANDLER_SUFFIX = 'Handler';

	public function __construct(
		protected AlertRepositoryInterface $alertRepository
	) {
		$this->setLanguage(Breeze::NAME . 'Alerts');
		parent::__construct($alertRepository);
	}

	public function send(AlertEntity $alertEntity): void
	{
		$this->alertRepository->insert($alertEntity);

		updateMemberData($alertEntity->getIdMember(), ['alerts' => '+']);
	}

	public function handle(array &$alerts): void
	{
		foreach ($alerts as $id => &$alert) {
			if (str_contains($alert['content_type'], Breeze::PATTERN)) {
				$alertEntity = new AlertHandledEntity($alert);
				$handler = $this->getHandler($alertEntity);
				$alert = $handler->resolve($alertEntity);
			}
		}
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function getById(int $alertId): array
	{
		return $this->alertRepository->getById($alertId);
	}

	public function delete(int $alertId): bool
	{
		return $this->alertRepository->delete([$alertId]);
	}

	protected function getHandler(AlertEntity $alertEntity): EventHandlerInterface
	{
		$handlerName = str_replace(Breeze::PATTERN, '', $alertEntity->getContentType() .
			$alertEntity->getContentAction());
		$handler = ucfirst($handlerName) . self::HANDLER_SUFFIX;

		return new $handler($alertEntity);
	}
}
