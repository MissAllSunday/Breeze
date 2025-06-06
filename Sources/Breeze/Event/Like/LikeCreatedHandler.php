<?php

declare(strict_types=1);

namespace Breeze\Event\Like;

use Breeze\Breeze;
use Breeze\Controller\API\StatusController;
use Breeze\Entity\AlertHandledEntity;
use Breeze\Event\EventHandlerInterface;
use Breeze\Traits\TextTrait;

class LikeCreatedHandler implements EventHandlerInterface
{
	use TextTrait;

	protected const string TARGET_HREF = '{scriptUrl}?action={action};sa={subAction};id={contentId}';

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
		$extra = $this->alertHandledEntity->getExtra();
		$contentType = $extra['content_type'] ?? '';

		// Determine the content type for the alert text
		$type = match ($contentType) {
			Breeze::NAME . '_status' => $this->getText('general.status'),
			Breeze::NAME . '_comment' => $this->getText('general.comment'),
			default => $this->getText('general.content'),
		};

		$this->alertHandledEntity->setText($this->parserText($this->getText('alert_like'), [
			'poster' => $this->alertHandledEntity->getSenderName(),
			'type' => $type,
		]));
	}

	protected function buildTargetHref(): void
	{
		$extra = json_decode($this->alertHandledEntity->getExtra(), true);
		$contentId = $extra['content_id'] ?? 0;
		$contentType = $extra['content_type'] ?? '';

		// Determine the appropriate action and subaction based on content type
		$action = Breeze::ACTION_WALL;
		$subAction = match ($contentType) {
			Breeze::NAME . '_status' => StatusController::ACTION_SINGLE,
			Breeze::NAME . '_comment' => StatusController::ACTION_SINGLE, // Comments link to their parent status
			default => '',
		};

		$this->alertHandledEntity->setTargetHref($this->parserText(self::TARGET_HREF, [
			'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
			'action' => $action,
			'subAction' => $subAction,
			'contentId' => $contentId,
		]));
	}
}
