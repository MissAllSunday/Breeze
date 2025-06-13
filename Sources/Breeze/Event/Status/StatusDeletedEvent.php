<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Psr\EventDispatcher\StoppableEventInterface;

class StatusDeletedEvent implements StoppableEventInterface
{
	private bool $propagationStopped = false;
	
	public function __construct(
		private readonly int $statusId
	) {
	}
	
	public function getStatusId(): int
	{
		return $this->statusId;
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
