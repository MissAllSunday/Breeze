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
		protected StatusRepositoryInterface  $statusRepository,
		protected EventServiceProvider       $eventServiceProvider,
		protected ?MentionServiceInterface   $mentionService = null
	) {
		parent::__construct($commentRepository);
	}

	/**
	 * @throws InvalidCommentException
	 * @return array [CommentEntity]
	 */
	public function save(array $data): array
	{
		$processed = null;

		if ($this->mentionService?->isEnabled()) {
			$mentionIds = array_map('intval', (array) ($data['mention_ids'] ?? []));
			$processed  = $this->mentionService->processBody($data[CommentEntity::BODY], $mentionIds);
			$data[CommentEntity::BODY] = $processed['body'];
		}

		$commentEntity = CommentEntity::from($data);
		$commentEntities = $this->commentRepository->insert($commentEntity);

		if ($commentEntities === []) {
			return $commentEntities;
		}

		// Fetch the parent status now — we need wall_id for mention alerts
		// and the entity itself for the CommentCreatedEvent.
		$statusEntity = null;
		$wallId = 0;

		try {
			$statusEntity = $this->statusRepository->getBasicInfoById($commentEntity->getStatusId());
			$wallId = $statusEntity->getWallId();
		} catch (DataNotFoundException) {
			// Status not found; mention alerts will use wallId = 0,
			// event dispatch is skipped below.
		}

		if ($processed !== null && !empty($processed['members'])) {
			foreach ($commentEntities as $entity) {
				$this->mentionService->save(
					MentionServiceInterface::CONTENT_TYPE_COMMENT,
					$entity->getId(),
					$processed['members'],
					$entity->getUserId(),
					$wallId
				);
			}
		}

		if ($statusEntity !== null) {
			foreach ($commentEntities as $entity) {
				$this->eventServiceProvider->getDispatcher()->dispatch(
					new CommentCreatedEvent($entity, $statusEntity)
				);
			}
		}

		return $commentEntities;
	}

	public function deleteById(int $commentId): bool
	{
		return $this->commentRepository->deleteById($commentId);
	}

	public function countOrphans(): int
	{
		return $this->commentRepository->countOrphans();
	}

	public function deleteOrphans(): void
	{
		$this->commentRepository->deleteOrphans();
	}

	public function recountLikes(): void
	{
		$this->commentRepository->recountLikes();
	}
}
