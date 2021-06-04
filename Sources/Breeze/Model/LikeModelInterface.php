<?php

declare(strict_types=1);

namespace Breeze\Model;

interface LikeModelInterface extends BaseModelInterface
{
	public function getByContent(string $type, int $contentId): array;

	public function userLikes(string $type, int $userId = 0): array;

	public function checkLike(string $type, int $contentId, int $userId): int;
}
