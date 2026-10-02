<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\CommentEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Event\Comment\CommentCreatedEvent;
use Breeze\Event\EventServiceProvider;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidCommentException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;

class CommentService extends BaseService implements CommentServiceInterface
{
	public function __construct(
		protected CommentRepositoryInterface $commentRepository,
		protected StatusRepositoryInterface  $statusRepository,
		protected EventServiceProvider       $eventServiceProvider,
		protected PermissionsServiceInterface $permissionsService,
		protected WallVisibilityServiceInterface $wallVisibilityService,
		protected ?MentionServiceInterface   $mentionService = null
	) {
		parent::__construct($commentRepository);
	}

	/**
	 * @throws InvalidCommentException
	 * @throws DataNotFoundException when the parent status does not exist
	 * @throws NotAllowedException when the session user may not comment on the target wall
	 * @return array [CommentEntity]
	 */
	public function save(array $data): array
	{
		// Attribution comes from the session, never from the payload.
		$data[CommentEntity::USER_ID] = $this->sessionUserId();

		$statusEntity = $this->statusRepository->getBasicInfoById((int) ($data[CommentEntity::STATUS_ID] ?? 0));
		$wallId = $statusEntity->getWallId();

		if (!$this->wallVisibilityService->canAccessWall($wallId, $data[CommentEntity::USER_ID])
			|| !$this->permissionsService->canPost(PermissionsEnum::TYPE_COMMENTS, $wallId)) {
			throw new NotAllowedException(PermissionsEnum::POST_COMMENTS);
		}

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

		// Per-item delete flag, resolved from the persisted author id and the
		// parent status wall_id so the freshly posted comment renders its
		// delete button without a page reload.
		foreach ($commentEntities as $entity) {
			$entity->setCanDelete($this->permissionsService->canDelete(
				PermissionsEnum::TYPE_COMMENTS,
				$entity->getUserId(),
				$wallId
			));
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

		foreach ($commentEntities as $entity) {
			$this->eventServiceProvider->getDispatcher()->dispatch(
				new CommentCreatedEvent($entity, $statusEntity)
			);
		}

		return $commentEntities;
	}

	/**
	 * Authorizes from the persisted rows: the comment author and the parent
	 * status wall owner are read back from the repositories so a caller
	 * cannot supply its own ids.
	 *
	 * @throws DataNotFoundException when the comment or its status is missing
	 * @throws NotAllowedException when the session user may not delete it
	 */
	public function deleteById(int $commentId): bool
	{
		$comment = $this->commentRepository->getById($commentId);
		$status = $this->statusRepository->getBasicInfoById($comment->getStatusId());

		if (!$this->permissionsService->canDelete(
			PermissionsEnum::TYPE_COMMENTS,
			$comment->getUserId(),
			$status->getWallId()
		)) {
			throw new NotAllowedException(PermissionsEnum::DELETE_COMMENTS);
		}

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
