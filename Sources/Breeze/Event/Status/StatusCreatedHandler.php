<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Breeze\Breeze;
use Breeze\Event\EventHandlerInterface;
use Breeze\Service\ProfileService;

class StatusCreatedHandler extends BaseHandler implements EventHandlerInterface
{
	protected const string TARGET_HREF = '{scriptUrl}?action={action};area={area};u={wallOwnerId}#status-{statusId}';

	public function resolve(): array
	{
		$this->extra = $this->alertEntity->getExtra();
		$this->buildAlertText();
		$this->buildTargetHref();
		$this->alertEntity->setIcon('<span class="alert_icon main_icons people"></span>');

		return $this->alertEntity->toArray();
	}

	protected function buildAlertText(): void
	{
		$this->alertEntity->setText($this->parserText($this->getText('alert_status_owner'), [
			'poster' => $this->alertEntity->getSenderName(),
		]));
	}

	protected function buildTargetHref(): void
	{
		$wallOwnerId = $this->extra['wall_id'] ?? 0;
		$statusId = $this->extra['status_id'] ?? $this->alertEntity->getContentId();

		$this->alertEntity->setTargetHref($this->parserText(self::TARGET_HREF, [
			'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
			'action' => Breeze::ACTION_PROFILE,
			'area' => ProfileService::AREA,
			'wallOwnerId' => $wallOwnerId,
			'statusId' => $statusId,
		]));
	}
}
