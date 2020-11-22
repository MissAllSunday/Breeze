<?php

declare(strict_types=1);

namespace Breeze\Model;

interface CommentModelInterface extends BaseModelInterface
{
	public function deleteByStatusId(array $statusIds): bool;

	public function getByProfiles(array $profileOwnerIds): array;

	public function getByStatus(array $statusIds): array;

	public function getByIds(array $commentIds = []): ?array;
}
