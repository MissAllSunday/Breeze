<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Repository\AlertRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;

class AlertService extends BaseService implements AlertServiceInterface
{
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

	public function handle(array &$alerts, array &$formats): void
	{
		$breezeAlerts = [];
		$refId = 0;

		foreach ($alerts as $id => $alert) {
			if (str_contains($alert['type'], Breeze::PATTERN)) {
				$alert['text'] = $this->buildAlertText($alert);
				$alert['target_href'] = $this->buildTargetHref($alert);
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

	protected function buildAlertText($alert): string
	{
		return $this->parserText($this->getText('alert_status_owner'), [
			'poster' => $alert['member_name'],
		]);
	}

	protected function buildTargetHref($alert): string
	{
		return $this->parserText('{scriptUrl}?action=breeze;sa=status;bid=' . $alert['content_id'], [
			'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
		]);
	}
}
