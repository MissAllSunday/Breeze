<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\StatusModelInterface;

class StatusRepository extends BaseRepository implements StatusRepositoryInterface
{
	/**
	 * @var StatusModelInterface
	 */
	private $statusModel;

	public function __construct(StatusModelInterface $statusModel)
	{
		$this->statusModel = $statusModel;
	}

	public function getByProfile(int $profileOwnerId = 0, int $start = 0): array
	 {
		$statusByProfile = $this->getCache($this->cacheKey(__METHOD__));

		if (null === $statusByProfile)
		{
			$statusByProfile = $this->statusModel->getStatusByProfile([
				'start' => $start,
				'maxIndex' => $this->statusModel->getCount(),
				'ids' => [$profileOwnerId]
			]);

			$this->setCache($this->cacheKey(__METHOD__), $statusByProfile);
		}

		return $statusByProfile;
	 }

	public function getModel(): StatusModelInterface
	{
		return$this->statusModel;
	}
}
