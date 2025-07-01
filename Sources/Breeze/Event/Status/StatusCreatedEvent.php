<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Breeze\Event\EventAbstract;

class StatusCreatedEvent extends EventAbstract
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
		return  $this->status[0]->getId();
	}

	public function getWallId(): int
	{
		return $this->status[0]->getWallId();
	}

	public function getUserId(): int
	{
		return (int) $this->status[0]->getUserId();
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
