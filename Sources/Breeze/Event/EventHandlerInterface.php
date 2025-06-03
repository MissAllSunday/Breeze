<?php

declare(strict_types=1);

namespace Breeze\Event;

use Breeze\Entity\AlertEntity;

interface EventHandlerInterface
{
	public function resolve(AlertEntity $alert): array;
}
