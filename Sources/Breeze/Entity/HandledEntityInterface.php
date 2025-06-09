<?php

namespace Breeze\Entity;

interface HandledEntityInterface
{
	public function setLikesInfo(LikeHandledEntity $likesInfo): array;
}
