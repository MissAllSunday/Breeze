<?php

declare(strict_types=1);

namespace Breeze\Event\Like;

use Breeze\Breeze;
use Breeze\Enums\LikesEnum;
use Breeze\Event\EventHandlerInterface;
use Breeze\Service\ProfileService;

class LikeCreatedHandler extends BaseHandler implements EventHandlerInterface
{
	public const string TARGET_HREF = '{scriptUrl}?action={action};area={area};u={wallOwnerId}{anchor}';

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
		$wallOwnerId = $this->extra['wall_id'] ?? 0;
		$contentType = $this->extra['content_type'] ?? '';
		$contentId = $this->extra['content_id'] ?? 0;

		$anchor = match ($contentType) {
			LikesEnum::Status->value => '#status-' . $contentId,
			LikesEnum::Comments->value => '#comment-' . $contentId,
			default => '',
		};

		$this->alertEntity->setTargetHref($this->parserText(self::TARGET_HREF, [
			'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
			'action' => Breeze::ACTION_PROFILE,
			'area' => ProfileService::AREA,
			'wallOwnerId' => $wallOwnerId,
			'anchor' => $anchor,
		]));
	}
}
