<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Breeze;
use Breeze\Controller\API\StatusController;
use Breeze\Entity\AlertEntity;
use Breeze\Event\EventAbstract;
use Breeze\Event\EventHandlerInterface;
use Breeze\Traits\TextTrait;

class CommentCreatedHandler implements EventHandlerInterface
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

		return $this->alertEntity->toArray();
	}

	protected function buildAlertText(): void
	{
		$contentAction = $this->alertEntity->getContentAction();

		match ($contentAction) {
			EventAbstract::CONTENT_ACTION_CREATED . EventAbstract::WALL_OWNER => $this->buildProfileOwnerText(),
			EventAbstract::CONTENT_ACTION_CREATED . EventAbstract::STATUS_OWNER => $this->buildStatusOwnerText(),
			default => '',
		};
	}

	protected function buildTargetHref(): void
	{
		$extra = $this->alertEntity->getExtra();
		$statusId = $extra['status_id'] ?? 0;

		$this->alertEntity->setTargetHref($this->parserText(self::TARGET_HREF, [
			'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
			'action' => Breeze::ACTION_WALL,
			'subAction' => StatusController::ACTION_SINGLE,
			'statusId' => $statusId,
		]));
	}

	protected function buildProfileOwnerText(): void
	{
		$this->alertEntity->setText($this->parserText($this->getText('alert_comment_different_owner'), [
			'poster' => $this->alertEntity->getSenderName(),
			'status_poster' => 'status_poster_name', // @todo change this to the actual status poster name
			'wall_owner' => 'wall_owner_name',
		]));
	}

	protected function buildStatusOwnerText(): void
	{
		$this->alertEntity->setText($this->parserText($this->getText('alert_comment_status_owner'), [
			'poster' => $this->alertEntity->getSenderName(),
			'wall_owner' => 'wall_owner_name',
		]));
	}
}
