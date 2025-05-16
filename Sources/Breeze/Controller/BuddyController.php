<?php

declare(strict_types=1);


namespace Breeze\Controller;

use Breeze\Service\ProfileServiceInterface;
use Breeze\Util\Error;
use Breeze\Util\Response;

class BuddyController extends BaseController implements ControllerInterface
{
	protected const ACTION_HANDLE = 'handle';
	protected const SUB_ACTIONS = [
		self::ACTION_HANDLE,
	];

	protected int $userReceivingId;

	public function __construct(
		protected Response $response,
		protected ProfileServiceInterface $profileService
	)
	{
		$this->userReceivingId = $this->getRequest('u', 0);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMainAction(): string
	{
		return self::ACTION_HANDLE;
	}

	public function getActionName(): string
	{
		return self::ACTION_HANDLE;
	}

	public function handle(): void
	{
		$this->check();

		$action = 'add';
		$currentUserInfo = $this->global('user_info');

		if (in_array($this->userReceivingId, $currentUserInfo['buddies'])) {
			$action = 'remove';
		}

		$this->{$action}($currentUserInfo);
	}

	// Receiver will get an alert from sender to either accept or decline the invite
	protected function add(array $currentUserInfo): void
	{

	}

	protected function remove(array $currentUserInfo): void
	{
		$newBuddiesList = array_diff($currentUserInfo['buddies'], [$this->userReceivingId]);

		$this->profileService->updateMemberData($currentUserInfo['id'], [
			'buddies' => implode(',', $newBuddiesList),
		]);
	}

	// After receiver accepted the invite, sender will get an alert confirming the buddy request
	public function confirm(): void
	{
		$this->check();
	}

	protected function check(): void
	{
		checkSession('get');

		$this->isAllowedTo('profile_extra_own');

		if (empty($this->userReceivingId)) {
			Error::show('no_access');
		}
	}
}
