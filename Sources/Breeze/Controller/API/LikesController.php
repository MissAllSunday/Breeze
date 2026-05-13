<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\LikeEntity;
use Breeze\Enums\LikesEnum;
use Breeze\Repository\InvalidDataException;
use Breeze\Service\LikeServiceInterface;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class LikesController extends ApiBaseController
{
	public const string ACTION_LIKE = 'like';
	public const string ACTION_INFO = 'info';

	public const array SUB_ACTIONS = [
		self::ACTION_LIKE,
		self::ACTION_INFO,
	];

	public function __construct(
		protected readonly LikeServiceInterface $likeService,
		protected ValidateActionsInterface $validateActions,
		protected Response $response
	) {
		parent::__construct($validateActions, $response);
	}

	public function like(): void
	{
		try {
			$likeInfo = $this->likeService->likeContent(
				LikesEnum::tryFrom($this->data[LikeEntity::TYPE]),
				$this->data[LikeEntity::ID],
				$this->data[LikeEntity::ID_MEMBER]
			);

			$this->response->success(
				'likeSuccess',
				$likeInfo,
				Response::CREATED
			);
		} catch (InvalidDataException $invalidDataException) {
			$this->response->error($invalidDataException->getMessage(), $invalidDataException->getResponseCode());
		}
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMutatingActions(): array
	{
		return [
			self::ACTION_LIKE,
		];
	}
}
