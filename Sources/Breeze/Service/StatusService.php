<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;

class StatusService extends BaseService implements StatusServiceInterface
{
	/**
	 * @var StatusRepositoryInterface
	 */
	private $statusRepository;

	/**
	 * @var CommentRepositoryInterface
	 */
	private $commentRepository;

	public function __construct(
		StatusRepositoryInterface $statusRepository,
		CommentRepositoryInterface $commentRepository
	)
	{
		$this->statusRepository = $statusRepository;
		$this->commentRepository = $commentRepository;
	}

	public function getByProfile(int $profileOwnerId = 0, int $start = 0): array
	{
		$statusData = $this->statusRepository->getStatusByProfile($profileOwnerId, $start);
	}
}
