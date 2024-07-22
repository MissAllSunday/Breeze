<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\LikeEntity;
use Breeze\Repository\InvalidDataException;
use Breeze\Repository\LikeRepositoryInterface;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class LikesController extends ApiBaseController
{
	public const ACTION_LIKE = 'like';
	public const ACTION_INFO = 'info';

	public const SUB_ACTIONS = [
		self::ACTION_LIKE,
		self::ACTION_INFO,
	];

	public function __construct(
		private readonly LikeRepositoryInterface $likeRepository,
		protected ValidateActionsInterface $validateActions,
		protected Response $response
	) {
		parent::__construct($validateActions, $response);
	}

	public function like(): void
	{
		try {
			$this->response->success(
				'likeSuccess',
				$this->likeRepository->likeContent(
					$this->data[LikeEntity::COLUMN_TYPE],
					$this->data[LikeEntity::COLUMN_ID],
					$this->data[LikeEntity::COLUMN_ID_MEMBER]
				)
			);
		} catch (InvalidDataException $invalidDataException) {
			$this->response->error($invalidDataException->getMessage(), $invalidDataException->getResponseCode());
		}
	}

	public function info(): void
	{
		try {
			$this->response->success('', $this->likeRepository->getLikeInfo(
				$this->data[LikeEntity::COLUMN_TYPE],
				$this->data[LikeEntity::COLUMN_ID]
			));
		} catch (InvalidDataException $invalidDataException) {
			$this->response->error($invalidDataException->getMessage(), $invalidDataException->getResponseCode());
		}
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
