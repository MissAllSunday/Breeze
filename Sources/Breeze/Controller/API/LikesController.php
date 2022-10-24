<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\LikeEntity;
use Breeze\Repository\InvalidDataException;
use Breeze\Repository\LikeRepositoryInterface;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class LikesController extends ApiBaseController
{
	public const ACTION_LIKE = 'like';
	public const ACTION_UNLIKE = 'unlike';

	public const SUB_ACTIONS = [
		self::ACTION_LIKE,
		self::ACTION_UNLIKE,
	];

	public function __construct(
		private LikeRepositoryInterface    $likeRepository,
		protected ValidateDataInterface $validator,
		protected Response $response
	) {
		parent::__construct($validator, $response);
	}

	public function like(): void
	{
		try {
			$this->response->success(
				$this->validator->successKeyString(),
				$this->likeRepository->likeContent(
					$this->data[LikeEntity::TYPE],
					$this->data[LikeEntity::ID],
					$this->data[LikeEntity::ID_MEMBER]
				)
			);
		} catch (InvalidDataException $invalidDataException) {
			$this->response->error($invalidDataException->getMessage(), $invalidDataException->getResponseCode());
		}
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
