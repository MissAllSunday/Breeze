<?php

declare(strict_types=1);


namespace Breeze\Service;

interface LikeServiceInterface extends BaseServiceInterface
{
	public function getByContent(string $type, int $contentId): array;

	public function isContentAlreadyLiked(string $type, int $contentId, int $userId): bool;
}
