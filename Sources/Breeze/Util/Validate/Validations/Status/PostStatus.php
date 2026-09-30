<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Enums\PermissionsEnum;
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

class PostStatus extends BaseActions implements ValidateDataInterface
{
	use SettingsTrait;

	protected const array PARAMS = [
		StatusEntity::WALL_ID => 0,
		StatusEntity::USER_ID => 0,
		StatusEntity::BODY => '',
	];

	protected const string SUCCESS_KEY = 'published_status';

	public function __construct(
		Data $validateData,
		User $validateUser,
		Allow $validateAllow,
		StatusRepositoryInterface $repository,
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
		$this->validateData->isInt([StatusEntity::WALL_ID, StatusEntity::USER_ID], $this->data);
		$this->validateData->isString([StatusEntity::BODY], $this->data);
	}

	/**
	 * Posting is authorized against the target wall owner, resolved through
	 * PermissionsService so the profile-owner shortcut and the postStatus
	 * permission stay in one place.
	 *
	 * The wall-enabled / block-list gate runs first: a member whose wall is
	 * disabled, or who blocked the viewer, must not be writable through the
	 * JSON API even when the viewer holds postStatus.
	 *
	 * @throws NotAllowedException
	 */
	public function checkAllow(): void
	{
		$wallOwnerId = (int) $this->data[StatusEntity::WALL_ID];

		if (!$this->wallVisibilityService->canAccessWall($wallOwnerId, $this->viewerId())) {
			throw new NotAllowedException(PermissionsEnum::POST_STATUS);
		}

		if (!$this->permissionsService->canPost(
			PermissionsEnum::TYPE_STATUS,
			$wallOwnerId
		)) {
			throw new NotAllowedException(PermissionsEnum::POST_STATUS);
		}

		$this->validateAllow->floodControl($this->data[StatusEntity::WALL_ID]);
	}

	protected function viewerId(): int
	{
		$userInfo = $this->global('user_info');

		return (int) ($userInfo['id'] ?? 0);
	}

	/**
	 * The poster must be the session user.
	 *
	 * Without this a member could submit any `user_id` and have the status
	 * attributed to someone else.
	 *
	 * @throws DataNotFoundException
	 */
	public function checkUser(): void
	{
		$this->validateUser->isSameUser((int) $this->data[StatusEntity::USER_ID]);
		$this->validateUser->areValidUsers([$this->data[StatusEntity::USER_ID], $this->data[StatusEntity::WALL_ID]]);
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
