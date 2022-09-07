<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Exceptions\InvalidCommentException;
use Breeze\Exceptions\InvalidStatusException;
use Breeze\Model\StatusModelInterface;

class StatusRepository extends BaseRepository implements StatusRepositoryInterface
{
	private StatusModelInterface $statusModel;

	private CommentRepositoryInterface $commentRepository;

	public function __construct(
		StatusModelInterface $statusModel,
		CommentRepositoryInterface $commentRepository
	) {
		$this->statusModel = $statusModel;
		$this->commentRepository = $commentRepository;
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function save(array $data): int
	{
		$newStatusId = $this->statusModel->insert($data);

		if (0 === $newStatusId) {
			throw new InvalidStatusException('error_save_status');
		}

		return $newStatusId;
	}

	public function getByProfile(int $profileOwnerId = 0, int $start = 0): array
	{
		$statusByProfile = $this->getCache(__METHOD__ . $profileOwnerId);

		if (empty($statusByProfile)) {
			$statusByProfile = $this->statusModel->getStatusByProfile([
				'start' => $start,
				'maxIndex' => $this->statusModel->getCount(),
				'ids' => [$profileOwnerId],
			]);

			if (!empty(array_filter($statusByProfile))) {
				$this->setCache(__METHOD__ . $profileOwnerId, $statusByProfile);
			}
		}

		return $statusByProfile;
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function getById(int $statusId = 0): array
	{
		$status = $this->statusModel->getById($statusId);

		if (!$status) {
			throw new InvalidStatusException('error_no_status');
		}

		return $status;
	}

	/**
	 * @throws InvalidStatusException
	 * @throws InvalidCommentException
	 */
	public function deleteById(int $statusId): bool
	{
		$this->commentRepository->deleteByStatusId($statusId);

		if (!$this->statusModel->delete([$statusId])) {
			throw new InvalidStatusException('error_no_comment');
		}

		$this->setCache(self::class . '::getById' . $statusId, null);

		return true;
	}
}
