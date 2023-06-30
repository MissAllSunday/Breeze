<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\StatusEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class StatusController extends ApiBaseController
{
	public const ACTION_PROFILE = 'profile';
	public const ACTION_GENERAL = 'general';
	public const ACTION_DELETE = 'deleteStatus';
	public const ACTION_POST = 'postStatus';
	public const ACTION_TOTAL = 'total';

	public const SUB_ACTIONS = [
		self::ACTION_PROFILE,
		self::ACTION_POST,
		self::ACTION_DELETE,
		self::ACTION_GENERAL,
		self::ACTION_TOTAL,
	];

	public function __construct(
		protected StatusRepositoryInterface $statusRepository,
		protected UserRepositoryInterface $userRepository,
		protected ValidateActionsInterface $validateActions,
		protected Response $response
	) {
		parent::__construct($validateActions, $response);
	}

	public function profile(): void
	{
		$wallUserSettings = $this->userRepository->getById($this->data[StatusEntity::WALL_ID]);
		$wallUserPagination = $wallUserSettings[UserSettingsEntity::PAGINATION_NUM];
		$start = $this->getRequest('start', 0);

		try {
			$statusByProfile = $this->statusRepository->getByProfile(
				[$this->data[StatusEntity::WALL_ID]],
				$start,
				!empty($start) ? ($wallUserPagination * $start) : $wallUserPagination
			);

			$this->response->success('', $statusByProfile);
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function general(): void
	{
		$currentUserInfo = $this->global('user_info');
		$currentUserSettings = $this->userRepository->getById($currentUserInfo['id']);
		$currentUserBuddies = $currentUserSettings[UserSettingsEntity::BUDDIES];

		if (empty($currentUserBuddies)) {
			$this->response->success('', []);
		}

		try {
			$statusByProfile = $this->statusRepository->getByProfile(
				$currentUserBuddies,
				$this->getRequest('start', 0)
			);

			$this->response->success('', $statusByProfile);
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function deleteStatus(): void
	{
		try {
			$this->statusRepository->deleteById($this->data[StatusEntity::ID]);

			$this->response->success('deleted_status');
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function postStatus(): void
	{
		try {
			$statusId = $this->statusRepository->save($this->data);
			$status = $this->statusRepository->getById($statusId);
			$status[$statusId]['isNew'] = true;

			$this->response->success(
				'published_status',
				$status,
				Response::CREATED
			);
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function total(): void
	{
		try {
			$statusByProfile = $this->statusRepository->getByProfile(
				[$this->data[StatusEntity::WALL_ID]],
				$this->getRequest('start', 0)
			);

			$this->response->success('', $statusByProfile);
		} catch (InvalidStatusException $invalidStatusException) {
			$this->response->error($invalidStatusException->getMessage());
		}
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
