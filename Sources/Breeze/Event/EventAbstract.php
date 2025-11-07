<?php

declare(strict_types=1);

namespace Breeze\Event;

use Breeze\Breeze;
use Psr\EventDispatcher\StoppableEventInterface;

abstract class EventAbstract implements StoppableEventInterface
{
	public const string CONTENT_ACTION_CREATED = Breeze::PATTERN . 'created';
	public const string CONTENT_ACTION_DELETED = Breeze::PATTERN . 'deleted';
	public const string WALL_OWNER = '_profile_owner';
	public const string STATUS_OWNER = '_status_owner';
	public const string COMMENT_OWNER = '_comment_owner';
	public const string OWNER = '_owner';

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
