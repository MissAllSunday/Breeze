<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\CommentEntity;
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

	public function getStatusByProfile(int $profileOwnerId = 0): array
	{
		// TODO: add cache
		return $this->commentModel->getStatusByProfile($profileOwnerId);
	}

	public function getModel(): CommentModelInterface
	{
		return $this->commentModel;
	}

	public function getCommentsByStatus(array $statusIds = []): void
	{
		// TODO: Implement getCommentsByStatus() method.
	}
}
