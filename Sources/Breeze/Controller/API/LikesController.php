<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\LikeEntity;
use Breeze\Event\EventServiceProvider;
use Breeze\Event\Like\LikeCreatedEvent;
use Breeze\LikesEnum;
use Breeze\Repository\InvalidDataException;
use Breeze\Repository\LikeRepositoryInterface;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class LikesController extends ApiBaseController
{
	public const string ACTION_LIKE = 'like';
	public const string ACTION_INFO = 'info';

	/** @var string[] */
	public const array SUB_ACTIONS = [
		self::ACTION_LIKE,
		self::ACTION_INFO,
	];

	public function __construct(
		protected readonly LikeRepositoryInterface $likeRepository,
		protected ValidateActionsInterface $validateActions,
		protected Response $response,
		protected EventServiceProvider $eventServiceProvider
	) {
		parent::__construct($validateActions, $response, $eventServiceProvider);
	}

	public function like(): void
	{
		try {
			$handledLike = $this->likeRepository->likeContent(
				LikesEnum::tryFrom($this->data[LikeEntity::COLUMN_TYPE]),
				$this->data[LikeEntity::COLUMN_ID],
				$this->data[LikeEntity::COLUMN_ID_MEMBER]
			);

			$this->response->success(
				'likeSuccess',
				$handledLike->toArray(),
				Response::CREATED
			);

			$this->eventDispatch(LikeCreatedEvent::class, $handledLike);
		} catch (InvalidDataException $invalidDataException) {
			$this->response->error($invalidDataException->getMessage(), $invalidDataException->getResponseCode());
		}
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
