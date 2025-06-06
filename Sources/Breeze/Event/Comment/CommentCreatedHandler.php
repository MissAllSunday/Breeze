<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Breeze;
use Breeze\Controller\API\StatusController;
use Breeze\Entity\AlertHandledEntity;
use Breeze\Event\EventHandlerInterface;
use Breeze\Traits\TextTrait;

class CommentCreatedHandler implements EventHandlerInterface
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
		$extra = json_decode($this->alertHandledEntity->getExtra(), true);
		$statusId = $extra['status_id'] ?? 0;
		$wallId = $extra['wall_id'] ?? 0;

		// Get the action type to determine which text to use
		$action = $this->alertHandledEntity->getContentAction();
		
		if ($action === Breeze::PATTERN . 'profile_owner') {
			$statusOwnerId = $extra['status_owner_id'] ?? 0;
			
			// If it's the wall owner's alert about a comment on someone else's status
			$this->alertHandledEntity->setText($this->parserText($this->getText('alert_comment_different_owner'), [
				'poster' => $this->alertHandledEntity->getSenderName(),
				'status_poster' => $this->getMemberName($statusOwnerId),
				'wall_owner' => $this->getMemberName($wallId),
			]));
		} else {
			// If it's the status owner's alert about a comment on their status
			$this->alertHandledEntity->setText($this->parserText($this->getText('alert_comment_status_owner'), [
				'poster' => $this->alertHandledEntity->getSenderName(),
				'wall_owner' => $this->getMemberName($wallId),
			]));
		}
	}

	protected function buildTargetHref(): void
	{
		$extra = json_decode($this->alertHandledEntity->getExtra(), true);
		$statusId = $extra['status_id'] ?? 0;

		$this->alertHandledEntity->setTargetHref($this->parserText(self::TARGET_HREF, [
			'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
			'action' => Breeze::ACTION_WALL,
			'subAction' => StatusController::ACTION_SINGLE,
			'statusId' => $statusId,
		]));
	}
}
