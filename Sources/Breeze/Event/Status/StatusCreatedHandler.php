<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Breeze\Breeze;
use Breeze\Controller\API\StatusController;
use Breeze\Entity\AlertHandledEntity;
use Breeze\Event\EventHandlerInterface;
use Breeze\Traits\TextTrait;

class StatusCreatedHandler implements EventHandlerInterface
{
	use TextTrait;

	protected const string TARGET_HREF = '{scriptUrl}?action={action};sa={subAction};id={statusId}';

	public function __construct(
		protected AlertHandledEntity $alertHandledEntity
	) {}

	public function resolve(): array
	{
		$this->buildAlertText();
		$this->buildTargetHref();

		return $this->alertHandledEntity->toArray();
	}

	protected function buildAlertText(): void
	{
		$this->alertHandledEntity->setText($this->parserText($this->getText('alert_status_owner'), [
			'poster' => $this->alertHandledEntity->getMemberName(),
		]));
	}

	protected function buildTargetHref(): void
	{
		$this->alertHandledEntity->setTargetHref($this->parserText(self::TARGET_HREF, [
			'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
			'action' => Breeze::ACTION_WALL,
			'subAction' => StatusController::ACTION_SINGLE,
			'statusId' => $this->alertHandledEntity->getContentId(),
		]));
	}
}
