<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Entity\StatusHandledEntity;
use Breeze\Event\EventAbstract;

class StatusCreatedEvent extends EventAbstract
{
	private bool $propagationStopped = false;

	public function __construct(
		private readonly StatusHandledEntity $status
	) {
	}

	public function getStatus(): StatusHandledEntity
	{
		return $this->status;
	}

	public function getStatusId(): int
	{
		return  $this->status->getId();
	}

	public function getWallId(): int
	{
		return $this->status->getWallId();
	}

	public function getUserId(): int
	{
		return $this->status->getUserId();
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
