<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Breeze;
use Breeze\Controller\API\StatusController;
use Breeze\Event\EventAbstract;
use Breeze\Event\EventHandlerInterface;

class CommentDeletedHandler extends BaseHandler implements EventHandlerInterface
{
	protected const string TARGET_HREF = '{scriptUrl}?action={action};sa={subAction};id={statusId}{anchor}';

	public function resolve(): array
	{
		$this->extra = $this->alertEntity->getExtra();
		$this->buildAlertText();
		$this->buildTargetHref();

		return $this->alertEntity->toArray();
	}

	protected function buildAlertText(): void
	{
		$contentAction = $this->alertEntity->getContentAction();

		match ($contentAction) {
			EventAbstract::CONTENT_ACTION_DELETED . EventAbstract::WALL_OWNER =>
				$this->buildProfileOwnerText('alert_comment_deleted_different_owner'),
			EventAbstract::CONTENT_ACTION_DELETED . EventAbstract::STATUS_OWNER =>
				$this->buildStatusOwnerText('alert_comment_deleted_status_owner'),
			default => '',
		};
	}

	protected function buildTargetHref(): void
	{
		$statusId = $this->extra['status_id'] ?? 0;

		// For a deleted comment, the target might still be the status where it was deleted from.
		// If the status itself is deleted, this link might lead to a DataNotFoundException,
		// which is handled by AlertService::handle().
		$this->alertEntity->setTargetHref($this->parserText(self::TARGET_HREF, [
			'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
			'action' => Breeze::ACTION_WALL,
			'subAction' => StatusController::ACTION_SINGLE,
			'statusId' => $statusId,
			'anchor' => '',
		]));
	}
}
