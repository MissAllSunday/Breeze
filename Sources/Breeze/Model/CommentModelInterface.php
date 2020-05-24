<?php

declare(strict_types=1);

namespace Breeze\Model;

interface CommentModelInterface extends BaseModelInterface
{
	public function deleteByStatusID(array $ids): bool;

	public function getByProfiles(array $profileOwnerIds): array;

	public function getByIds(array $commentIds = []): ?array;
}
