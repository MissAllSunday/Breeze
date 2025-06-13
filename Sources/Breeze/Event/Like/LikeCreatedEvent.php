<?php

declare(strict_types=1);

namespace Breeze\Event\Like;

use Breeze\Entity\LikeHandledEntity;
use Breeze\Event\EventAbstract;

class LikeCreatedEvent extends EventAbstract
{
	public function __construct(protected LikeHandledEntity $likeHandledEntity) {
	}

	public function getLikeHandledEntity(): LikeHandledEntity
	{
		return $this->likeHandledEntity;
	}
}
