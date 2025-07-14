<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Breeze\Breeze;
use Breeze\Controller\API\StatusController;
use Breeze\Entity\AlertEntity;
use Breeze\Event\EventHandlerInterface;
use Breeze\Traits\TextTrait;

class StatusCreatedHandler implements EventHandlerInterface
{
	use TextTrait;

	protected const string TARGET_HREF = '{scriptUrl}?action={action};sa={subAction};id={statusId}';

	public function __construct(
		protected AlertEntity $alertEntity
	) {}

	public function resolve(): array
	{
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
		$this->alertEntity->setTargetHref($this->parserText(self::TARGET_HREF, [
			'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
			'action' => Breeze::ACTION_WALL,
			'subAction' => StatusController::ACTION_SINGLE,
			'statusId' => $this->alertEntity->getContentId(),
		]));
	}
}
