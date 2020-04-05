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

	public function getCommentsByProfile(int $profileOwnerId = 0): void
	{
		$status = $this->commentModel->getChunk(
			0,
			0,
			[
				'columnName' => CommentEntity::COLUMN_PROFILE_ID,
				'ids' => [$profileOwnerId]]
		);
	}

	public function getModel(): CommentModelInterface
	{
		return $this->commentModel;
	}
}
