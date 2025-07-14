<?php

declare(strict_types=1);

namespace Breeze\Event\Like;

use Breeze\Entity\LikeEntity;
use Breeze\Event\EventAbstract;

class LikeCreatedEvent extends EventAbstract
{
	public function __construct(protected LikeEntity $LikeEntity) {
	}

	public function getLikeEntity(): LikeEntity
	{
		return $this->LikeEntity;
	}
}
