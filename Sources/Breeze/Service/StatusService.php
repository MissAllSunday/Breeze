<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\StatusRepositoryInterface;

class StatusService extends BaseService implements StatusServiceInterface
{
	/**
	 * @var StatusRepositoryInterface
	 */
	private $statusRepository;

	public function __construct(StatusRepositoryInterface $statusRepository)
	{
		$this->statusRepository = $statusRepository;
	}

	public function getByProfile(int $profileOwnerId = 0, int $start = 0): array
	{
		return $this->statusRepository->getStatusByProfile($profileOwnerId, $start);
	}
}
