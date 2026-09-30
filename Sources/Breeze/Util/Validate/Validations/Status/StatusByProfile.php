<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\StatusRepositoryInterface;
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

class StatusByProfile extends BaseActions implements ValidateDataInterface
{
	use SettingsTrait;

	protected const PARAMS = [StatusEntity::WALL_ID => 0];

	public function __construct(
		Data $validateData,
		User $validateUser,
		Allow $validateAllow,
		StatusRepositoryInterface $repository,
		protected WallVisibilityServiceInterface $wallVisibilityService
	) {
		parent::__construct($validateData, $validateUser, $validateAllow, $repository);
	}

	/**
	 * @throws InvalidDataException
	 */
	public function checkData(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);
	}

	/**
	 * Closes the read path (`profile` / `total` sub-actions): a disabled wall
	 * or a mutual block must not be readable through the JSON API.
	 *
	 * @throws NotAllowedException
	 */
	public function checkAllow(): void
	{
		if (!$this->wallVisibilityService->canAccessWall(
			(int) $this->data[StatusEntity::WALL_ID],
			$this->viewerId()
		)) {
			throw new NotAllowedException('no_access');
		}
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function checkUser(): void
	{
		$this->validateUser->areValidUsers([$this->data[StatusEntity::WALL_ID]]);
	}

	/**
	 * @throws InvalidDataException
	 * @throws DataNotFoundException
	 * @throws NotAllowedException
	 */
	public function isValid(): void
	{
		$this->checkData();
		$this->checkUser();
		$this->checkAllow();
	}

	protected function viewerId(): int
	{
		$userInfo = $this->global('user_info');

		return (int) ($userInfo['id'] ?? 0);
	}
}
