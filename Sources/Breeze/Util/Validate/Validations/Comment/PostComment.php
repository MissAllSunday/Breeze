<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Entity\CommentEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Service\PermissionsServiceInterface;
use Breeze\Service\WallVisibilityServiceInterface;
use Breeze\Traits\SettingsTrait;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\InvalidDataException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

class PostComment extends BaseActions implements ValidateDataInterface
{
	use SettingsTrait;

	protected const array PARAMS = [
		CommentEntity::STATUS_ID => 0,
		CommentEntity::USER_ID => 0,
		CommentEntity::BODY => '',
	];

	protected const string SUCCESS_KEY = 'published_comment';

	public function __construct(
		Data $validateData,
		User $validateUser,
		Allow $validateAllow,
		BaseRepositoryInterface $repository,
		protected StatusRepositoryInterface $statusRepository,
		protected PermissionsServiceInterface $permissionsService,
		protected WallVisibilityServiceInterface $wallVisibilityService
	) {
		parent::__construct($validateData, $validateUser, $validateAllow, $repository);
	}

	/**
	 * @throws InvalidDataException
	 * @throws DataNotFoundException
	 */
	public function checkData(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);
		$this->validateData->isInt([CommentEntity::STATUS_ID, CommentEntity::USER_ID], $this->data);
		$this->validateData->isString([CommentEntity::BODY], $this->data);
	}

	/**
	 * The wall-enabled / block-list gate runs before the permission check: a
	 * member whose wall is disabled, or who blocked the viewer, must not be
	 * writable through the JSON API.
	 *
	 * @throws NotAllowedException
	 * @throws DataNotFoundException
	 */
	public function checkAllow(): void
	{
		$wallOwnerId = $this->statusRepository
			->getBasicInfoById($this->data[CommentEntity::STATUS_ID])
			->getWallId();

		if (!$this->wallVisibilityService->canAccessWall($wallOwnerId, $this->viewerId())) {
			throw new NotAllowedException(PermissionsEnum::POST_COMMENTS);
		}

		if (!$this->permissionsService->canPost(PermissionsEnum::TYPE_COMMENTS, $wallOwnerId)) {
			throw new NotAllowedException(PermissionsEnum::POST_COMMENTS);
		}

		$this->validateAllow->floodControl($this->data[CommentEntity::USER_ID]);
	}

	protected function viewerId(): int
	{
		$userInfo = $this->global('user_info');

		return (int) ($userInfo['id'] ?? 0);
	}

	/**
	 * The poster must be the session user.
	 *
	 * Without this a member could submit any `user_id` and have the comment
	 * attributed to someone else.
	 *
	 * @throws DataNotFoundException
	 */
	public function checkUser(): void
	{
		$this->validateUser->isSameUser((int) $this->data[CommentEntity::USER_ID]);
		$this->validateUser->areValidUsers([$this->data[CommentEntity::USER_ID]]);
	}

	/**
	 * @throws NotAllowedException
	 * @throws DataNotFoundException
	 * @throws InvalidDataException
	 */
	public function isValid(): void
	{
		$this->checkData();
		$this->checkAllow();
		$this->checkUser();
	}
}
