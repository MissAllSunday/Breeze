<?php

declare(strict_types=1);

namespace Breeze\Event;

use Psr\EventDispatcher\StoppableEventInterface;

abstract class EventAbstract implements StoppableEventInterface
{
	private bool $propagationStopped = false;

	public function isPropagationStopped(): bool
	{
		return $this->propagationStopped;
	}

	public function stopPropagation(): void
	{
		$this->propagationStopped = true;
	}
}
