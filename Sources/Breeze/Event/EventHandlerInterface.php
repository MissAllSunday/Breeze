<?php

declare(strict_types=1);

namespace Breeze\Event;

/**
 * @codeCoverageIgnore
 */
interface EventHandlerInterface
{
	public function resolve(): array;
}
