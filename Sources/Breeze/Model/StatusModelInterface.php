<?php

declare(strict_types=1);

namespace Breeze\Model;

interface StatusModelInterface extends BaseModelInterface
{
	public function getColumnPosterId(): string;

	public function getStatusByProfile(array $params): array;

	public function getById(int $statusId): array;
}
