<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Breeze;
use Breeze\Controller\API\StatusController;
use Breeze\Entity\AlertEntity;
use Breeze\Event\EventAbstract;
use Breeze\Event\EventHandlerInterface;
use Breeze\Repository\AlertRepository;
use Breeze\Traits\TextTrait;

class CommentDeletedHandler implements EventHandlerInterface
{
	use TextTrait;

	protected const string TARGET_HREF = '{scriptUrl}?action={action};sa={subAction};id={statusId}{anchor}';

	protected array $extra = [];

	protected array $usersInfo = [];

	public function __construct(
		protected AlertEntity $alertEntity,
		protected AlertRepository $alertRepository
	) {}

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
			EventAbstract::CONTENT_ACTION_DELETED . EventAbstract::WALL_OWNER => $this->buildProfileOwnerText(),
			EventAbstract::CONTENT_ACTION_DELETED . EventAbstract::STATUS_OWNER => $this->buildStatusOwnerText(),
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

	protected function buildProfileOwnerText(): void
	{
		$statusOwnerId = $this->extra['status_owner_id'] ?? 0;
		$wallOwnerId = $this->extra['wall_id'] ?? 0;

		if (empty($this->usersInfo)) {
			$this->usersInfo = $this->alertRepository->loadUsersInfo([$statusOwnerId, $wallOwnerId]);
		}

		$statusOwnerName = $this->usersInfo[$statusOwnerId]['name'] ?? 'Unknown User';
		$wallOwnerName = $this->usersInfo[$wallOwnerId]['name'] ?? 'Unknown User';

		$this->alertEntity->setText($this->parserText($this->getText('alert_comment_deleted_different_owner'), [ // @todo: Define this text key
			'poster' => $this->alertEntity->getSenderName(),
			'status_poster' => $statusOwnerName,
			'wall_owner' => $wallOwnerName,
		]));
	}

	protected function buildStatusOwnerText(): void
	{
		$wallOwnerId = $this->extra['wall_id'] ?? 0;

		if (empty($this->usersInfo)) {
			$this->usersInfo = $this->alertRepository->loadUsersInfo([$wallOwnerId]);
		}

		$wallOwnerName = $this->usersInfo[$wallOwnerId]['name'] ?? 'Unknown User';

		$this->alertEntity->setText($this->parserText($this->getText('alert_comment_deleted_status_owner'), [ // @todo: Define this text key
			'poster' => $this->alertEntity->getSenderName(),
			'wall_owner' => $wallOwnerName,
		]));
	}
}
