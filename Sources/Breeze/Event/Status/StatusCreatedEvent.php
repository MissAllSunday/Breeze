<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Breeze\Entity\StatusEntity;
use Psr\EventDispatcher\StoppableEventInterface;

class StatusCreatedEvent implements StoppableEventInterface
{
	private bool $propagationStopped = false;

	public function __construct(
		private readonly array $status
	) {
	}

	public function getStatus(): array
	{
		return $this->status;
	}

	public function getStatusId(): int
	{
		$statusId = array_key_first($this->status);

		return (int) $statusId;
	}

	public function getWallId(): int
	{
		$statusId = array_key_first($this->status);

		return (int) $this->status[$statusId][StatusEntity::WALL_ID];
	}

	public function getUserId(): int
	{
		$statusId = array_key_first($this->status);

		return (int) $this->status[$statusId][StatusEntity::USER_ID];
	}

	public function isPropagationStopped(): bool
	{
		return $this->propagationStopped;
	}

	public function stopPropagation(): void
	{
		$this->propagationStopped = true;
	}
}
