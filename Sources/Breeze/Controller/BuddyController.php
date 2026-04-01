<?php

declare(strict_types=1);


namespace Breeze\Controller;

use Breeze\Service\BuddyServiceInterface;
use Breeze\Util\Error;
use Breeze\Util\Response;

class BuddyController extends BaseController implements ControllerInterface
{
	protected const string ACTION_HANDLE = 'handle';
	protected const array SUB_ACTIONS = [
		self::ACTION_HANDLE,
	];

	protected int $userReceivingId;

	public function __construct(
		protected Response $response,
		protected BuddyServiceInterface $buddyService
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

		$currentUserInfo = $this->global('user_info');

		if (in_array($this->userReceivingId, $currentUserInfo['buddies'])) {
			$this->buddyService->removeBuddy($this->userReceivingId, $currentUserInfo);
		} else {
			$this->buddyService->addBuddy($this->userReceivingId, $currentUserInfo);
		}
	}

	public function confirm(): void
	{
		$this->check();

		$currentUserInfo = $this->global('user_info');
		$this->buddyService->confirmBuddy($this->userReceivingId, $currentUserInfo);
	}

	protected function check(): void
	{
		checkSession('get');
		validateToken('buddy', 'get');

		$this->isAllowedTo('profile_extra_own');

		if ($this->userReceivingId === 0) {
			Error::show('no_access');
		}
	}
}
