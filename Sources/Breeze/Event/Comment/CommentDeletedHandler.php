<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Breeze;
use Breeze\Event\EventAbstract;
use Breeze\Event\EventHandlerInterface;
use Breeze\Service\ProfileService;

class CommentDeletedHandler extends BaseHandler implements EventHandlerInterface
{
	protected const string TARGET_HREF = '{scriptUrl}?action={action};area={area};u={wallOwnerId}';

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
		$wallOwnerId = $this->extra['wall_id'] ?? 0;

		$this->alertEntity->setTargetHref($this->parserText(self::TARGET_HREF, [
			'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
			'action' => Breeze::ACTION_PROFILE,
			'area' => ProfileService::AREA,
			'wallOwnerId' => $wallOwnerId,
		]));
	}
}
