<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\LikeInfoEntity;
use Breeze\Enums\LikesEnum;

interface LikeServiceInterface
{
	public function likeContent(LikesEnum $type, int $contentId, int $userId): ?LikeInfoEntity;

	public function countOrphans(): int;

	public function deleteOrphans(): void;
}
