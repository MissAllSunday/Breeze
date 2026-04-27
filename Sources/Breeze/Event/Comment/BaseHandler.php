<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Entity\AlertEntity;
use Breeze\Repository\AlertRepository;
use Breeze\Traits\TextTrait;

class BaseHandler
{
	use TextTrait;

	protected array $extra = [];

	protected array $usersInfo = [];

	public function __construct(
		protected AlertEntity $alertEntity,
		protected AlertRepository $alertRepository
	) {}

	protected function buildProfileOwnerText(string $txtKey): void
	{
		$statusOwnerId = $this->extra['status_owner_id'] ?? 0;
		$wallOwnerId = $this->extra['wall_id'] ?? 0;

		if (empty($this->usersInfo)) {
			$this->usersInfo = $this->alertRepository->loadUsersInfo([$statusOwnerId, $wallOwnerId]);
		}

		$statusOwnerName = $this->usersInfo[$statusOwnerId]['name'] ?? 'Unknown User';
		$wallOwnerName = $this->usersInfo[$wallOwnerId]['name'] ?? 'Unknown User';

		$this->alertEntity->setText($this->parserText($this->getText($txtKey), [
			'poster' => $this->alertEntity->getSenderName(),
			'status_poster' => $statusOwnerName,
			'wall_owner' => $wallOwnerName,
		]));
	}

	protected function buildStatusOwnerText(string $txtKey): void
	{
		$wallOwnerId = $this->extra['wall_id'] ?? 0;

		if (empty($this->usersInfo)) {
			$this->usersInfo = $this->alertRepository->loadUsersInfo([$wallOwnerId]);
		}

		$wallOwnerName = $this->usersInfo[$wallOwnerId]['name'] ?? 'Unknown User';

		$this->alertEntity->setText($this->parserText($this->getText($txtKey), [
			'poster' => $this->alertEntity->getSenderName(),
			'wall_owner' => $wallOwnerName,
		]));
	}
}
