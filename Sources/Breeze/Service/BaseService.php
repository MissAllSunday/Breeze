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

	/**
	 * Id of the user owning the current session. Authorization and content
	 * attribution must always derive from here, never from a request payload.
	 */
	protected function sessionUserId(): int
	{
		$userInfo = $this->global('user_info');

		return (int) ($userInfo['id'] ?? 0);
	}
}
