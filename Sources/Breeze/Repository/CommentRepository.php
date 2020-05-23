<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\CommentModelInterface;

class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
	/**
	 * @var CommentModelInterface
	 */
	private $commentModel;

	public function __construct(CommentModelInterface $commentModel)
	{
		$this->commentModel = $commentModel;
	}

	public function save(array $data): int
	{
		return $this->commentModel->insert($data);
	}

	public function getByProfile(int $profileOwnerId = 0): array
	{
		$commentsByProfile = $this->getCache($this->cacheKey(__METHOD__));

		if (null === $commentsByProfile)
			$commentsByProfile = $this->commentModel->getByProfiles([$profileOwnerId]);

		return $commentsByProfile;
	}

	public function getModel(): CommentModelInterface
	{
		return $this->commentModel;
	}

	public function getByStatus(array $statusIds = []): void
	{
		// TODO: Implement getCommentsByStatus() method.
	}

	public function getById(int $commentId): array
	{
		return $this->commentModel->getByIds([$commentId]);
	}
}
