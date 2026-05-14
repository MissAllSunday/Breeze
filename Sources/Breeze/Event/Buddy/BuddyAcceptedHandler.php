<?php

declare(strict_types=1);

namespace Breeze\Event\Buddy;

use Breeze\Breeze;
use Breeze\Event\EventHandlerInterface;

class BuddyAcceptedHandler extends BaseHandler implements EventHandlerInterface
{
	protected const string TARGET_HREF = '{scriptUrl}?action=profile;u={userId}';

	public function resolve(): array
	{
		$this->extra = $this->alertEntity->getExtra();
		$this->buildAlertText();
		$this->buildTargetHref();
		$this->setPeopleIcon();

		return $this->alertEntity->toArray();
	}

	protected function buildAlertText(): void
	{
		$this->alertEntity->setText($this->parserText($this->getText('alert_buddy_confirmed'), [
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
