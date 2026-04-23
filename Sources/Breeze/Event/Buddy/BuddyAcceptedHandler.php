<?php

declare(strict_types=1);

namespace Breeze\Event\Buddy;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Event\EventHandlerInterface;
use Breeze\Repository\AlertRepository;
use Breeze\Traits\TextTrait;

class BuddyAcceptedHandler implements EventHandlerInterface
{
	use TextTrait;

	protected const string TARGET_HREF = '{scriptUrl}?action=profile;u={userId}';

	public function __construct(
		protected AlertEntity $alertEntity,
		protected AlertRepository $alertRepository
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
		$this->alertEntity->setText($this->parserText($this->getText('alert_buddy_accepted'), [
			'poster' => $this->alertEntity->getSenderName(),
		]));
	}

	protected function buildTargetHref(): void
	{
		$this->alertEntity->setTargetHref($this->parserText(self::TARGET_HREF, [
			'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
			'userId' => $this->alertEntity->getSenderId(),
		]));
	}
}
