<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\MoodEntity;
use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class MoodController extends ApiBaseController
{
	public const ACTION_POST = 'postMood';
	public const ACTION_DELETE = 'deleteMood';
	public const ACTION_ACTIVE = 'getActiveMoods';
	public const ACTION_USER_SET = 'setUserMood';
	public const ACTION_ALL = 'getAllMoods';

	public const SUB_ACTIONS = [
		self::ACTION_POST,
		self::ACTION_DELETE,
		self::ACTION_ACTIVE,
		self::ACTION_ALL,
		self::ACTION_USER_SET,
	];

	public function __construct(
		protected UserRepositoryInterface $userRepository,
		protected MoodRepositoryInterface $moodRepository,
		protected ValidateActionsInterface $validateActions,
		protected Response $response
	) {
		parent::__construct($validateActions, $response);
	}

	public function postMood(): void
	{
		$mood = $this->moodRepository->insertMood($this->data);

		$this->response->success($this->validator->successKeyString(), $mood);
	}

	public function deleteMood(): void
	{

		$this->moodRepository->deleteByIds([$this->data[MoodEntity::ID]]);

		$this->response->success($this->validator->successKeyString(), [], Response::NO_CONTENT);
	}

	public function getAllMoods(): void
	{
		$this->response->success($this->validator->successKeyString(), $this->moodRepository->getAllMoods());
	}

	public function getActiveMoods(): void
	{
		$this->response->success($this->validator->successKeyString(), $this->moodRepository->getActiveMoods());
	}

	public function setUserMood(): void
	{
		$userId = array_pop($this->data);

		$this->userRepository->save($this->data, $userId);

		$this->response->success($this->validator->successKeyString(), [], Response::ACCEPTED);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
