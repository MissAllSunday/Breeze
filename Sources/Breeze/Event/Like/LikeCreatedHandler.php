<?php

declare(strict_types=1);

namespace Breeze\Event\Like;

use Breeze\Breeze;
use Breeze\Event\EventHandlerInterface;
use Breeze\LikesEnum;

class LikeCreatedHandler extends BaseHandler implements EventHandlerInterface
{
	public const string TARGET_HREF = '{scriptUrl}?action={action};id={contentId}';

	public function resolve(): array
	{
		$this->extra = $this->alertEntity->getExtra();
		$this->buildAlertText();
		$this->buildTargetHref();

		return $this->alertEntity->toArray();
	}

	protected function buildAlertText(): void
	{
		$contentType = $this->extra['content_type'] ?? '';

		if (!LikesEnum::isValid($contentType)) {
			return;
		}

		$this->alertEntity->setText($this->parserText($this->getText('alert_like'), [
			'poster' => $this->alertEntity->getSenderName(),
			'type' => $this->getText('alert_' . $contentType),
		]));
	}

	protected function buildTargetHref(): void
	{
		$contentId = $this->extra['content_id'] ?? 0;

		$this->alertEntity->setTargetHref($this->parserText(self::TARGET_HREF, [
			'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
			'action' => Breeze::ACTION_WALL,
			'contentId' => $contentId,
		]));
	}
}
