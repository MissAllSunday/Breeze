<?php

declare(strict_types=1);

namespace Breeze\Event;

interface EventHandlerInterface
{
	public function resolve(): array;
}
