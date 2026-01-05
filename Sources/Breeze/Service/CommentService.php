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
		protected ?EventServiceProvider $eventServiceProvider = null
	) {
		parent::__construct($commentRepository);
	}

	/**
	 * @throws InvalidCommentException
	 * @throws DataNotFoundException
	 * @return array [CommentEntity]
	 */
	public function save(array $data): array
	{
		$commentEntity = CommentEntity::from($data);
		$commentEntities = $this->commentRepository->insert($commentEntity);

		// Fetch the status entity to get owner and wall information for event
		if (isset($this->eventServiceProvider) && !empty($commentEntities)) {
			try {
				$statusEntity = $this->statusRepository->getById($commentEntity->getStatusId());
				
				// Dispatch the comment created event for each comment
				foreach ($commentEntities as $entity) {
					$this->eventServiceProvider->getDispatcher()->dispatch(
						new CommentCreatedEvent(
							$entity,
							$statusEntity->getUserId(),  // statusOwnerId
							$statusEntity->getWallId()   // wallId
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

	/**
	 * @throws DataNotFoundException
	 */
	public function deleteById(int $commentId): bool
	{
		return $this->commentRepository->deleteById($commentId);
	}
}

