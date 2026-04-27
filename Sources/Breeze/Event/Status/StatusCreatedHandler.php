<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Breeze\Breeze;
use Breeze\Controller\API\StatusController;
use Breeze\Event\EventHandlerInterface;

class StatusCreatedHandler extends BaseHandler implements EventHandlerInterface
{
	protected const string TARGET_HREF = '{scriptUrl}?action={action};sa={subAction};id={statusId}{anchor}';

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
		$statusId = $this->alertEntity->getContentId();

		$this->alertEntity->setTargetHref($this->parserText(self::TARGET_HREF, [
			'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
			'action' => Breeze::ACTION_WALL,
			'subAction' => StatusController::ACTION_SINGLE,
			'statusId' => $statusId,
			'anchor' => '#status-' . $statusId,
		]));
	}
}
