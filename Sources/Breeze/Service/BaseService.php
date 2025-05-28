<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Traits\TextTrait;

abstract class BaseService
{
	use TextTrait;

	public function __construct(
		protected BaseRepositoryInterface $baseRepository
	) {}

	public function loadUsersInfo(array $userIds = []): array
	{
		return $this->baseRepository->loadUsersInfo($userIds);
	}
}
