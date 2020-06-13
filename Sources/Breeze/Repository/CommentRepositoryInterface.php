<?php

declare(strict_types=1);


namespace Breeze\Repository;

interface CommentRepositoryInterface
{
	public function save(array $data): int;

	public function getByProfile(int $profileOwnerId = 0): array;

	public function getByStatus(array $statusIds = []): void;

	public function getById(int $commentId): array;

	public function deleteById(int $commentId): void;

	public function cleanCache(string $cacheName): void;
}
