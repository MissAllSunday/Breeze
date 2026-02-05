<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\CommentEntity;
use Breeze\Event\Comment\CommentCreatedEvent;
use Breeze\Event\EventServiceProvider;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidCommentException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;

class CommentService extends BaseService implements CommentServiceInterface
{
	public function __construct(
		protected CommentRepositoryInterface $commentRepository,
		protected StatusRepositoryInterface $statusRepository,
		protected EventServiceProvider $eventServiceProvider
	) {
		parent::__construct($commentRepository);
	}

	/**
	 * @throws InvalidCommentException
	 * @return array [CommentEntity]
	 */
	public function save(array $data): array
	{
		$commentEntity = CommentEntity::from($data);
		$commentEntities = $this->commentRepository->insert($commentEntity);

		if (!empty($commentEntities)) {
			try {
				$statusEntity = $this->statusRepository->getBasicInfoById($commentEntity->getStatusId());

				foreach ($commentEntities as $entity) {
					$this->eventServiceProvider->getDispatcher()->dispatch(
						new CommentCreatedEvent(
							$entity,
							$statusEntity
						)
					);
				}
			} catch (DataNotFoundException $e) {
				// If status not found, skip event dispatching
				// The comment was still created successfully
			}
		}

		return $commentEntities;
	}

	public function deleteById(int $commentId): bool
	{
		return $this->commentRepository->deleteById($commentId);
	}
}
